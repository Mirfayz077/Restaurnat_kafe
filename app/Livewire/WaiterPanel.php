<?php

namespace App\Livewire;

use App\Events\OperationsUpdated;
use App\Models\Branch;
use App\Models\Category;
use App\Models\DiningTable;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Attributes\On;
use Livewire\Component;

class WaiterPanel extends Component
{
    public string $search = '';

    public string $categoryId = 'all';

    public ?int $selectedTableId = null;

    public string $notes = '';

    /** @var array<int, int> */
    public array $cart = [];

    public int $branchId;

    public function mount(): void
    {
        $this->branchId = auth()->user()->branch_id
            ?? Branch::where('is_active', true)->value('id')
            ?? 0;

        $this->selectedTableId = $this->availableTables()->first()?->id;
    }

    public function selectTable(int $tableId): void
    {
        $this->selectedTableId = $tableId;
        $this->resetErrorBag('selectedTableId');
    }

    public function setCategory(string $categoryId): void
    {
        $this->categoryId = $categoryId;
    }

    public function addProduct(int $productId): void
    {
        $this->cart[$productId] = ($this->cart[$productId] ?? 0) + 1;
    }

    public function incrementQuantity(int $productId): void
    {
        $this->addProduct($productId);
    }

    public function decrementQuantity(int $productId): void
    {
        if (! isset($this->cart[$productId])) {
            return;
        }

        $this->cart[$productId]--;

        if ($this->cart[$productId] <= 0) {
            unset($this->cart[$productId]);
        }
    }

    public function removeProduct(int $productId): void
    {
        unset($this->cart[$productId]);
    }

    public function sendToPreparation(): void
    {
        if ($this->cartItems()->isEmpty()) {
            $this->addError('cart', 'Kamida bitta mahsulot tanlang.');

            return;
        }

        $validated = $this->validate([
            'selectedTableId' => ['required', 'integer', 'exists:dining_tables,id'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $table = $this->availableTables()->firstWhere('id', $validated['selectedTableId']);

        if (! $table) {
            $this->addError('selectedTableId', 'Tanlangan stol sizning filialiingizga tegishli emas.');

            return;
        }

        $cartItems = $this->cartItems();
        $subtotal = $cartItems->sum('line_total');
        $now = now();

        $result = DB::transaction(function () use ($table, $cartItems, $subtotal, $validated, $now) {
            $order = $this->occupiedOrdersQuery()
                ->where('dining_table_id', $table->id)
                ->lockForUpdate()
                ->first();

            if ($order?->status === 'paid') {
                return ['state' => 'awaiting_close'];
            }

            if (! $order) {
                $order = Order::create([
                    'order_number' => $this->generateOrderNumber(),
                    'branch_id' => $this->branchId,
                    'dining_table_id' => $table->id,
                    'user_id' => null,
                    'waiter_user_id' => auth()->id(),
                    'order_type' => 'dine_in',
                    'status' => 'open',
                    'notes' => $validated['notes'] ?: null,
                    'subtotal' => $subtotal,
                    'total' => $subtotal,
                    'placed_at' => $now,
                    'paid_at' => null,
                ]);
            } else {
                $order->update([
                    'waiter_user_id' => $order->waiter_user_id ?: auth()->id(),
                    'notes' => $this->mergeOrderNotes($order->notes, $validated['notes'] ?? null),
                    'subtotal' => (float) $order->subtotal + $subtotal,
                    'total' => (float) $order->total + $subtotal,
                ]);
            }

            foreach ($cartItems as $item) {
                $order->items()->create([
                    'product_id' => $item['id'],
                    'product_name' => $item['name'],
                    'station' => $item['station'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['price'],
                    'line_total' => $item['line_total'],
                    'preparation_status' => 'queued',
                    'sent_to_station_at' => $now,
                ]);
            }

            $order->refreshPreparationStatus();

            return [
                'state' => 'sent',
                'order_id' => $order->id,
            ];
        });

        if ($result['state'] === 'awaiting_close') {
            $this->addError('selectedTableId', "Bu stol to'langan, lekin hali close qilinmagan.");

            return;
        }

        OperationsUpdated::dispatch(
            type: 'waiter.order.sent',
            branchId: $this->branchId,
            orderId: $result['order_id'] ?? null,
            meta: ['table_id' => $table->id],
        );

        $this->resetComposer();
        session()->flash('status', 'Zakaz oshxona va bar navbatiga yuborildi.');
    }

    public function serveReadyItems(int $orderId): void
    {
        $order = $this->activeOrdersQuery()
            ->whereKey($orderId)
            ->with('items')
            ->firstOrFail();

        $readyItems = $order->items->where('preparation_status', 'ready');

        if ($readyItems->isEmpty()) {
            session()->flash('error', 'Bu zakazda topshirishga tayyor item yo‘q.');

            return;
        }

        $servedAt = now();

        DB::transaction(function () use ($order, $readyItems, $servedAt) {
            $order->items()
                ->whereIn('id', $readyItems->pluck('id'))
                ->update([
                    'preparation_status' => 'served',
                    'served_at' => $servedAt,
                ]);

            $order->refreshPreparationStatus();
        });

        OperationsUpdated::dispatch(
            type: 'waiter.order.served',
            branchId: $order->branch_id,
            orderId: $order->id,
            meta: ['ready_count' => $readyItems->count()],
        );

        session()->flash('status', 'Tayyor itemlar mijozga topshirildi.');
    }

    #[On('operations-updated')]
    public function syncFromRealtime(): void
    {
        // Re-render the waiter terminal when operational state changes.
    }

    protected function availableTables(): Collection
    {
        if (! $this->branchId) {
            return collect();
        }

        return DiningTable::query()
            ->where('branch_id', $this->branchId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }

    protected function cartItems(): Collection
    {
        $products = Product::query()
            ->with('category')
            ->where('is_active', true)
            ->whereIn('id', array_keys($this->cart))
            ->get()
            ->keyBy('id');

        return collect($this->cart)
            ->map(function (int $quantity, int|string $productId) use ($products) {
                $product = $products->get((int) $productId);

                if (! $product) {
                    return null;
                }

                $lineTotal = $quantity * (float) $product->price;

                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'category' => $product->category?->name,
                    'station' => $product->station,
                    'station_label' => $product->stationLabel(),
                    'price' => (float) $product->price,
                    'quantity' => $quantity,
                    'line_total' => $lineTotal,
                ];
            })
            ->filter()
            ->values();
    }

    protected function activeOrdersQuery(): Builder
    {
        return Order::query()
            ->where('branch_id', $this->branchId)
            ->whereIn('status', Order::activeStatuses());
    }

    protected function occupiedOrdersQuery(): Builder
    {
        return Order::query()
            ->where('branch_id', $this->branchId)
            ->whereIn('status', Order::settlementStatuses());
    }

    protected function generateOrderNumber(): string
    {
        do {
            $orderNumber = 'SRV-'.now()->format('Ymd-His').'-'.str_pad((string) random_int(1, 999), 3, '0', STR_PAD_LEFT);
        } while (Order::where('order_number', $orderNumber)->exists());

        return $orderNumber;
    }

    protected function mergeOrderNotes(?string $currentNotes, ?string $newNotes): ?string
    {
        $notes = collect([$currentNotes, $newNotes])
            ->map(fn (?string $note) => trim((string) $note))
            ->filter()
            ->unique()
            ->implode(PHP_EOL);

        return $notes !== '' ? $notes : null;
    }

    protected function resetComposer(): void
    {
        $this->notes = '';
        $this->cart = [];
        $this->resetErrorBag();
    }

    public function render()
    {
        $tables = $this->availableTables();

        if ($tables->isNotEmpty() && ! $tables->contains('id', $this->selectedTableId)) {
            $this->selectedTableId = $tables->first()->id;
        }

        $search = trim($this->search);
        $cartItems = $this->cartItems();

        $products = Product::query()
            ->with('category')
            ->where('is_active', true)
            ->when($this->categoryId !== 'all', fn (Builder $query) => $query->where('category_id', (int) $this->categoryId))
            ->when($search !== '', function (Builder $query) use ($search) {
                $query->where(function (Builder $innerQuery) use ($search) {
                    $innerQuery
                        ->where('name', 'like', '%'.$search.'%')
                        ->orWhere('description', 'like', '%'.$search.'%')
                        ->orWhere('sku', 'like', '%'.$search.'%');
                });
            })
            ->orderBy('name')
            ->get();

        $activeOrders = $this->occupiedOrdersQuery()
            ->with(['items', 'diningTable'])
            ->whereIn('dining_table_id', $tables->pluck('id'))
            ->get()
            ->keyBy('dining_table_id');

        $tableCards = $tables->map(function (DiningTable $table) use ($activeOrders) {
            /** @var Order|null $order */
            $order = $activeOrders->get($table->id);
            $items = $order?->items ?? collect();

            $queuedQty = $items->where('preparation_status', 'queued')->sum('quantity');
            $preparingQty = $items->where('preparation_status', 'preparing')->sum('quantity');
            $readyQty = $items->where('preparation_status', 'ready')->sum('quantity');
            $servedQty = $items->where('preparation_status', 'served')->sum('quantity');

            $status = match (true) {
                ! $order => 'available',
                $order->status === 'paid' => 'paid',
                $readyQty > 0 => 'ready',
                ($queuedQty + $preparingQty) > 0 => 'preparing',
                default => 'served',
            };

            return [
                'id' => $table->id,
                'name' => $table->name,
                'seats' => $table->seats,
                'status' => $status,
                'order' => $order,
                'total' => $order ? (float) $order->total : 0,
                'queued_qty' => $queuedQty,
                'preparing_qty' => $preparingQty,
                'ready_qty' => $readyQty,
                'served_qty' => $servedQty,
            ];
        });

        /** @var Order|null $selectedOrder */
        $selectedOrder = $activeOrders->get($this->selectedTableId);
        $selectedTable = $tables->firstWhere('id', $this->selectedTableId);

        return view('livewire.waiter-panel', [
            'branch' => Branch::find($this->branchId),
            'categories' => Category::where('is_active', true)->orderBy('sort_order')->orderBy('name')->get(),
            'tables' => $tableCards,
            'selectedTable' => $selectedTable,
            'selectedOrder' => $selectedOrder,
            'products' => $products,
            'cartItems' => $cartItems,
            'subtotal' => $cartItems->sum('line_total'),
            'activeTablesCount' => $tableCards->where('order')->count(),
            'readyTablesCount' => $tableCards->where('status', 'ready')->count(),
        ]);
    }
}
