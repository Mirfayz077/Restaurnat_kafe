<?php

namespace App\Livewire;

use App\Events\OperationsUpdated;
use App\Models\Branch;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;
use Livewire\Component;

class StationQueue extends Component
{
    public string $station = 'kitchen';

    public int $branchId;

    public function mount(string $station): void
    {
        abort_unless(array_key_exists($station, config('pos.product_stations', [])), 404);

        $this->station = $station;
        $this->branchId = auth()->user()->branch_id
            ?? Branch::where('is_active', true)->value('id')
            ?? 0;
    }

    public function startItem(int $itemId): void
    {
        $item = $this->findItem($itemId, ['queued']);
        $startedAt = now();

        DB::transaction(function () use ($item, $startedAt) {
            $item->update([
                'preparation_status' => 'preparing',
                'started_preparing_at' => $startedAt,
            ]);

            $item->order->refreshPreparationStatus();
        });

        OperationsUpdated::dispatch(
            type: 'station.item.preparing',
            branchId: $item->order->branch_id,
            orderId: $item->order_id,
            station: $this->station,
            meta: ['item_id' => $item->id],
        );

        session()->flash('status', 'Item tayyorlashga olindi.');
    }

    public function markReady(int $itemId): void
    {
        $item = $this->findItem($itemId, ['queued', 'preparing']);
        $readyAt = now();

        DB::transaction(function () use ($item, $readyAt) {
            $item->update([
                'preparation_status' => 'ready',
                'started_preparing_at' => $item->started_preparing_at ?? $readyAt,
                'ready_at' => $readyAt,
            ]);

            $item->order->refreshPreparationStatus();
        });

        OperationsUpdated::dispatch(
            type: 'station.item.ready',
            branchId: $item->order->branch_id,
            orderId: $item->order_id,
            station: $this->station,
            meta: ['item_id' => $item->id],
        );

        session()->flash('status', 'Item tayyor bo\'ldi.');
    }

    #[On('operations-updated')]
    public function syncFromRealtime(): void
    {
        // Re-render the queue when Reverb broadcasts an operational change.
    }

    protected function findItem(int $itemId, array $allowedStatuses): OrderItem
    {
        return OrderItem::query()
            ->whereKey($itemId)
            ->where('station', $this->station)
            ->whereIn('preparation_status', $allowedStatuses)
            ->whereHas('order', function (Builder $query) {
                $query
                    ->whereIn('status', Order::activeStatuses())
                    ->where('branch_id', $this->branchId);
            })
            ->with(['order.diningTable', 'order.branch'])
            ->firstOrFail();
    }

    public function render()
    {
        $items = OrderItem::query()
            ->with(['order.branch', 'order.diningTable'])
            ->where('station', $this->station)
            ->whereIn('preparation_status', ['queued', 'preparing', 'ready'])
            ->whereHas('order', function (Builder $query) {
                $query
                    ->whereIn('status', Order::activeStatuses())
                    ->where('branch_id', $this->branchId);
            })
            ->orderByRaw("CASE preparation_status WHEN 'ready' THEN 0 WHEN 'preparing' THEN 1 ELSE 2 END")
            ->orderBy('sent_to_station_at')
            ->get();

        $orders = $items
            ->groupBy('order_id')
            ->map(function ($group) {
                $firstItem = $group->first();
                $order = $firstItem?->order;

                return [
                    'order' => $order,
                    'items' => $group,
                    'queued_qty' => $group->where('preparation_status', 'queued')->sum('quantity'),
                    'preparing_qty' => $group->where('preparation_status', 'preparing')->sum('quantity'),
                    'ready_qty' => $group->where('preparation_status', 'ready')->sum('quantity'),
                ];
            })
            ->values();

        return view('livewire.station-queue', [
            'branch' => Branch::find($this->branchId),
            'stationLabel' => config("pos.product_stations.{$this->station}", $this->station),
            'orders' => $orders,
            'queuedCount' => $items->where('preparation_status', 'queued')->sum('quantity'),
            'preparingCount' => $items->where('preparation_status', 'preparing')->sum('quantity'),
            'readyCount' => $items->where('preparation_status', 'ready')->sum('quantity'),
        ]);
    }
}
