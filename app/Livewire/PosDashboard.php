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

class PosDashboard extends Component
{
    public string $search = '';

    public string $serviceOrderSearch = '';

    public string $categoryId = 'all';

    public string $orderType = 'dine_in';

    public ?int $branchId = null;

    public ?int $tableId = null;

    public ?int $selectedServiceOrderId = null;

    public ?int $selectedSplitId = null;

    public int $splitCount = 2;

    public string $customerName = '';

    public string $customerPhone = '';

    public string $deliveryAddress = '';

    public string $paymentMethod = 'cash';

    public string $servicePaymentMethod = 'cash';

    public string $notes = '';

    /** @var array<int, int> */
    public array $cart = [];

    public function mount(): void
    {
        $this->branchId = auth()->user()->branch_id
            ?? Branch::where('is_active', true)->value('id');

        $this->tableId = $this->availableTables()->first()?->id;
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

    public function selectServiceOrder(int $orderId): void
    {
        $this->selectedServiceOrderId = $this->settlementOrdersQuery()
            ->whereKey($orderId)
            ->exists()
            ? $orderId
            : null;

        $this->selectedSplitId = null;
        $this->resetErrorBag(['selectedServiceOrderId', 'selectedSplitId']);
    }

    public function selectSplit(int $splitId): void
    {
        $order = $this->selectedServiceOrder();

        if (! $order) {
            $this->selectedSplitId = null;

            return;
        }

        $this->selectedSplitId = $order->splits->contains('id', $splitId)
            ? $splitId
            : null;

        $this->resetErrorBag('selectedSplitId');
    }

    public function updatedBranchId(): void
    {
        if ($this->orderType === 'dine_in') {
            $this->tableId = $this->availableTables()->first()?->id;
        }

        $this->selectedServiceOrderId = null;
        $this->selectedSplitId = null;
    }

    public function updatedOrderType(string $value): void
    {
        if ($value !== 'dine_in') {
            $this->tableId = null;
        } else {
            $this->tableId = $this->availableTables()->first()?->id;
        }

        if ($value !== 'delivery') {
            $this->deliveryAddress = '';
        }

        if ($value === 'dine_in') {
            $this->customerName = '';
            $this->customerPhone = '';
        }
    }

    public function checkout()
    {
        if ($this->cartItems()->isEmpty()) {
            $this->addError('cart', "Kamida bitta mahsulot qo'shing.");

            return null;
        }

        $validated = $this->validate([
            'branchId' => ['required', 'exists:branches,id'],
            'orderType' => ['required', Rule::in(array_keys(config('pos.order_types')))],
            'tableId' => [Rule::requiredIf($this->orderType === 'dine_in'), 'nullable', 'exists:dining_tables,id'],
            'customerName' => [Rule::requiredIf(in_array($this->orderType, ['takeaway', 'delivery'], true)), 'nullable', 'string', 'max:255'],
            'customerPhone' => [Rule::requiredIf(in_array($this->orderType, ['takeaway', 'delivery'], true)), 'nullable', 'string', 'max:255'],
            'deliveryAddress' => [Rule::requiredIf($this->orderType === 'delivery'), 'nullable', 'string'],
            'paymentMethod' => ['required', Rule::in(array_keys(config('pos.payment_methods')))],
            'notes' => ['nullable', 'string'],
        ]);

        if ($this->orderType === 'dine_in' && ! $this->availableTables()->contains('id', $this->tableId)) {
            $this->addError('tableId', 'Tanlangan stol ushbu filialga tegishli emas.');

            return null;
        }

        $cartItems = $this->cartItems();
        $subtotal = $cartItems->sum('line_total');

        $order = DB::transaction(function () use ($validated, $cartItems, $subtotal) {
            $now = now();

            $order = Order::create([
                'order_number' => $this->generateOrderNumber(),
                'branch_id' => $validated['branchId'],
                'dining_table_id' => $validated['tableId'],
                'user_id' => auth()->id(),
                'closed_by_user_id' => $validated['orderType'] === 'dine_in' ? auth()->id() : null,
                'order_type' => $validated['orderType'],
                'status' => $validated['orderType'] === 'dine_in' ? 'closed' : 'paid',
                'customer_name' => $validated['customerName'] ?: null,
                'customer_phone' => $validated['customerPhone'] ?: null,
                'delivery_address' => $validated['deliveryAddress'] ?: null,
                'notes' => $validated['notes'] ?: null,
                'subtotal' => $subtotal,
                'total' => $subtotal,
                'placed_at' => $now,
                'paid_at' => $now,
                'closed_at' => $validated['orderType'] === 'dine_in' ? $now : null,
            ]);

            foreach ($cartItems as $item) {
                $order->items()->create([
                    'product_id' => $item['id'],
                    'product_name' => $item['name'],
                    'station' => $item['station'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['price'],
                    'line_total' => $item['line_total'],
                    'preparation_status' => 'served',
                    'sent_to_station_at' => $now,
                    'started_preparing_at' => $now,
                    'ready_at' => $now,
                    'served_at' => $now,
                ]);
            }

            $order->payments()->create([
                'user_id' => auth()->id(),
                'method' => $validated['paymentMethod'],
                'amount' => $subtotal,
                'paid_at' => $now,
            ]);

            return $order;
        });

        $this->resetCheckoutState();
        OperationsUpdated::dispatch(
            type: 'pos.order.checked_out',
            branchId: $order->branch_id,
            orderId: $order->id,
            meta: ['order_type' => $order->order_type],
        );
        session()->flash('status', 'Order muvaffaqiyatli yaratildi.');

        return redirect()->route('orders.receipt', $order);
    }

    public function createEqualSplits(): void
    {
        $validated = $this->validate([
            'selectedServiceOrderId' => ['required', 'integer'],
            'splitCount' => ['required', 'integer', 'min:2', 'max:12'],
        ], [
            'selectedServiceOrderId.required' => 'Avval split qilinadigan orderni tanlang.',
        ]);

        $result = DB::transaction(function () use ($validated) {
            $order = $this->settlementOrdersQuery()
                ->whereKey($validated['selectedServiceOrderId'])
                ->with(['splits', 'payments'])
                ->lockForUpdate()
                ->first();

            if (! $order) {
                return ['state' => 'missing'];
            }

            if ($order->status !== 'served') {
                return ['state' => 'blocked'];
            }

            if ($order->payments->isNotEmpty()) {
                return ['state' => 'payments_exist'];
            }

            $order->splits()->delete();

            foreach ($this->distributeSplitAmounts((float) $order->total, $validated['splitCount']) as $index => $amount) {
                $order->splits()->create([
                    'split_number' => $index + 1,
                    'label' => 'Guest '.($index + 1),
                    'amount' => $amount,
                    'status' => 'draft',
                ]);
            }

            return [
                'state' => 'created',
                'order' => $order->fresh(['splits']),
            ];
        });

        if ($result['state'] === 'missing') {
            $this->addError('selectedServiceOrderId', "Tanlangan order topilmadi.");

            return;
        }

        if ($result['state'] === 'blocked') {
            $this->addError('selectedServiceOrderId', "Split bill faqat to'liq served bo'lgan orderda ochiladi.");

            return;
        }

        if ($result['state'] === 'payments_exist') {
            $this->addError('selectedServiceOrderId', "Orderda allaqachon payment bor. Split billni endi qayta yaratib bo'lmaydi.");

            return;
        }

        $this->selectedServiceOrderId = $result['order']->id;
        $this->selectedSplitId = $result['order']->splits->first()?->id;
        $this->resetErrorBag(['selectedServiceOrderId', 'selectedSplitId']);
        OperationsUpdated::dispatch(
            type: 'pos.split.created',
            branchId: $result['order']->branch_id,
            orderId: $result['order']->id,
            meta: ['split_count' => $validated['splitCount']],
        );
        session()->flash('status', "Equal split yaratildi: {$validated['splitCount']} qism.");
    }

    public function paySelectedSplit()
    {
        $validated = $this->validate([
            'selectedServiceOrderId' => ['required', 'integer'],
            'selectedSplitId' => ['required', 'integer'],
            'servicePaymentMethod' => ['required', Rule::in(array_keys(config('pos.payment_methods')))],
        ], [
            'selectedSplitId.required' => "Avval to'lanadigan splitni tanlang.",
        ]);

        $result = DB::transaction(function () use ($validated) {
            $order = $this->settlementOrdersQuery()
                ->whereKey($validated['selectedServiceOrderId'])
                ->with('splits')
                ->lockForUpdate()
                ->first();

            if (! $order) {
                return ['state' => 'missing'];
            }

            if ($order->status !== 'served') {
                return ['state' => 'blocked'];
            }

            if ($order->splits->isEmpty()) {
                return ['state' => 'no_splits'];
            }

            $split = $order->splits->firstWhere('id', $validated['selectedSplitId']);

            if (! $split) {
                return ['state' => 'split_missing'];
            }

            if ($split->status !== 'draft') {
                return ['state' => 'split_unavailable'];
            }

            $paidAt = now();

            $order->payments()->create([
                'user_id' => auth()->id(),
                'order_split_id' => $split->id,
                'method' => $validated['servicePaymentMethod'],
                'amount' => $split->amount,
                'paid_at' => $paidAt,
            ]);

            $split->update([
                'status' => 'paid',
                'paid_by_user_id' => auth()->id(),
                'paid_at' => $paidAt,
            ]);

            $allPaid = ! $order->splits()
                ->where('status', 'draft')
                ->exists();

            if ($allPaid) {
                $order->update([
                    'user_id' => auth()->id(),
                    'status' => 'paid',
                    'paid_at' => $paidAt,
                ]);
            }

            return [
                'state' => 'paid',
                'all_paid' => $allPaid,
                'order' => $order->fresh(['splits']),
            ];
        });

        if ($result['state'] === 'missing') {
            $this->addError('selectedServiceOrderId', "Tanlangan order topilmadi.");

            return null;
        }

        if ($result['state'] === 'blocked') {
            $this->addError('selectedServiceOrderId', "Split payment faqat served holatdagi order uchun ishlaydi.");

            return null;
        }

        if ($result['state'] === 'no_splits') {
            $this->addError('selectedServiceOrderId', "Avval equal split yarating.");

            return null;
        }

        if (in_array($result['state'], ['split_missing', 'split_unavailable'], true)) {
            $this->addError('selectedSplitId', "Tanlangan split topilmadi yoki allaqachon to'langan.");

            return null;
        }

        $this->selectedServiceOrderId = $result['order']->id;
        $this->selectedSplitId = $result['all_paid']
            ? null
            : $result['order']->splits->firstWhere('status', 'draft')?->id;

        $this->resetErrorBag(['selectedServiceOrderId', 'selectedSplitId']);
        OperationsUpdated::dispatch(
            type: 'pos.split.paid',
            branchId: $result['order']->branch_id,
            orderId: $result['order']->id,
            meta: ['all_paid' => $result['all_paid']],
        );
        session()->flash(
            'status',
            $result['all_paid']
                ? "Barcha splitlar to'landi. Endi stolni close qilishingiz mumkin."
                : "Tanlangan split muvaffaqiyatli to'landi."
        );

        return null;
    }

    public function completeServiceOrderPayment()
    {
        $validated = $this->validate([
            'selectedServiceOrderId' => ['required', 'integer'],
            'servicePaymentMethod' => ['required', Rule::in(array_keys(config('pos.payment_methods')))],
        ], [
            'selectedServiceOrderId.required' => 'Avval waiter order tanlang.',
        ]);

        $result = DB::transaction(function () use ($validated) {
            $order = $this->settlementOrdersQuery()
                ->whereKey($validated['selectedServiceOrderId'])
                ->with('splits')
                ->lockForUpdate()
                ->first();

            if (! $order) {
                return ['state' => 'missing'];
            }

            if ($order->status !== 'served') {
                return ['state' => 'blocked'];
            }

            if ($order->splits->isNotEmpty()) {
                return ['state' => 'split_bill'];
            }

            $paidAt = now();

            $order->payments()->create([
                'user_id' => auth()->id(),
                'method' => $validated['servicePaymentMethod'],
                'amount' => $order->total,
                'paid_at' => $paidAt,
            ]);

            $order->update([
                'user_id' => auth()->id(),
                'status' => 'paid',
                'paid_at' => $paidAt,
            ]);

            return [
                'state' => 'paid',
                'order' => $order,
            ];
        });

        if ($result['state'] === 'missing') {
            $this->addError('selectedServiceOrderId', "Tanlangan order topilmadi yoki allaqachon yopilgan.");

            return null;
        }

        if ($result['state'] === 'blocked') {
            $this->addError('selectedServiceOrderId', "Yakuniy to'lovdan oldin order to'liq served bo'lishi kerak.");

            return null;
        }

        if ($result['state'] === 'split_bill') {
            $this->addError('selectedServiceOrderId', "Bu order split bill rejimida. Endi to'lovlarni splitlar orqali yakunlang.");

            return null;
        }

        $this->resetErrorBag('selectedServiceOrderId');
        $this->selectedServiceOrderId = $result['order']->id;
        OperationsUpdated::dispatch(
            type: 'pos.order.paid',
            branchId: $result['order']->branch_id,
            orderId: $result['order']->id,
        );
        session()->flash('status', "Waiter order to'landi. Endi stolni close qilishingiz mumkin.");

        return null;
    }

    public function closeSelectedServiceOrder(): void
    {
        $validated = $this->validate([
            'selectedServiceOrderId' => ['required', 'integer'],
        ], [
            'selectedServiceOrderId.required' => 'Avval close qilinadigan orderni tanlang.',
        ]);

        $result = DB::transaction(function () use ($validated) {
            $order = $this->settlementOrdersQuery()
                ->whereKey($validated['selectedServiceOrderId'])
                ->lockForUpdate()
                ->first();

            if (! $order) {
                return ['state' => 'missing'];
            }

            if ($order->status !== 'paid') {
                return ['state' => 'blocked'];
            }

            $order->update([
                'status' => 'closed',
                'closed_by_user_id' => auth()->id(),
                'closed_at' => now(),
            ]);

            return [
                'state' => 'closed',
                'branch_id' => $order->branch_id,
                'order_id' => $order->id,
            ];
        });

        if ($result['state'] === 'missing') {
            $this->addError('selectedServiceOrderId', "Tanlangan order topilmadi yoki allaqachon arxivga tushgan.");

            return;
        }

        if ($result['state'] === 'blocked') {
            $this->addError('selectedServiceOrderId', "Stolni close qilishdan oldin order to'langan bo'lishi kerak.");

            return;
        }

        $this->selectedServiceOrderId = null;
        $this->selectedSplitId = null;
        $this->resetErrorBag(['selectedServiceOrderId', 'selectedSplitId']);
        OperationsUpdated::dispatch(
            type: 'pos.order.closed',
            branchId: $result['branch_id'] ?? $this->branchId,
            orderId: $result['order_id'] ?? $validated['selectedServiceOrderId'],
        );
        session()->flash('status', "Stol muvaffaqiyatli yopildi va order arxivga o'tdi.");
    }

    #[On('operations-updated')]
    public function syncFromRealtime(): void
    {
        // Re-render the cashier dashboard when live updates arrive.
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

    protected function settlementOrdersQuery(): Builder
    {
        return Order::query()
            ->where('order_type', 'dine_in')
            ->whereIn('status', Order::settlementStatuses())
            ->when($this->branchId, fn (Builder $query) => $query->where('branch_id', $this->branchId));
    }

    protected function selectedServiceOrder(): ?Order
    {
        if (! $this->selectedServiceOrderId) {
            return null;
        }

        return $this->settlementOrdersQuery()
            ->with(['diningTable', 'items', 'splits', 'payments', 'waiter', 'cashier'])
            ->find($this->selectedServiceOrderId);
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
                    'price' => (float) $product->price,
                    'quantity' => $quantity,
                    'line_total' => $lineTotal,
                ];
            })
            ->filter()
            ->values();
    }

    protected function distributeSplitAmounts(float $total, int $splitCount): array
    {
        $totalCents = (int) round($total * 100);
        $baseAmount = intdiv($totalCents, $splitCount);
        $remainder = $totalCents % $splitCount;

        $amounts = [];

        foreach (range(1, $splitCount) as $index) {
            $amountInCents = $baseAmount + ($index <= $remainder ? 1 : 0);
            $amounts[] = $amountInCents / 100;
        }

        return $amounts;
    }

    protected function generateOrderNumber(): string
    {
        do {
            $orderNumber = 'ORD-'.now()->format('Ymd-His').'-'.str_pad((string) random_int(1, 999), 3, '0', STR_PAD_LEFT);
        } while (Order::where('order_number', $orderNumber)->exists());

        return $orderNumber;
    }

    protected function resetCheckoutState(): void
    {
        $this->search = '';
        $this->categoryId = 'all';
        $this->orderType = 'dine_in';
        $this->tableId = $this->availableTables()->first()?->id;
        $this->customerName = '';
        $this->customerPhone = '';
        $this->deliveryAddress = '';
        $this->paymentMethod = 'cash';
        $this->notes = '';
        $this->cart = [];
        $this->resetErrorBag();
    }

    public function render()
    {
        $search = trim($this->search);
        $serviceOrderSearch = trim($this->serviceOrderSearch);
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

        $serviceOrders = $this->settlementOrdersQuery()
            ->with(['diningTable', 'items', 'splits', 'payments', 'waiter', 'cashier'])
            ->when($serviceOrderSearch !== '', function (Builder $query) use ($serviceOrderSearch) {
                $query->where(function (Builder $innerQuery) use ($serviceOrderSearch) {
                    $innerQuery
                        ->where('order_number', 'like', '%'.$serviceOrderSearch.'%')
                        ->orWhereHas('diningTable', fn (Builder $tableQuery) => $tableQuery->where('name', 'like', '%'.$serviceOrderSearch.'%'));
                });
            })
            ->orderByRaw("CASE status WHEN 'paid' THEN 0 WHEN 'served' THEN 1 WHEN 'ready' THEN 2 WHEN 'in_service' THEN 3 ELSE 4 END")
            ->latest('placed_at')
            ->limit(12)
            ->get();

        if ($this->selectedServiceOrderId && ! $serviceOrders->contains('id', $this->selectedServiceOrderId)) {
            $this->selectedServiceOrderId = null;
            $this->selectedSplitId = null;
        }

        $selectedServiceOrder = $serviceOrders->firstWhere('id', $this->selectedServiceOrderId)
            ?? $serviceOrders->firstWhere('status', 'paid')
            ?? $serviceOrders->firstWhere('status', 'served')
            ?? $serviceOrders->first();

        if (! $this->selectedServiceOrderId && $selectedServiceOrder) {
            $this->selectedServiceOrderId = $selectedServiceOrder->id;
        }

        if ($selectedServiceOrder && $selectedServiceOrder->splits->isNotEmpty()) {
            if ($this->selectedSplitId && ! $selectedServiceOrder->splits->contains('id', $this->selectedSplitId)) {
                $this->selectedSplitId = null;
            }

            if (! $this->selectedSplitId) {
                $this->selectedSplitId = $selectedServiceOrder->splits->firstWhere('status', 'draft')?->id;
            }
        } else {
            $this->selectedSplitId = null;
        }

        $selectedSplit = $selectedServiceOrder?->splits->firstWhere('id', $this->selectedSplitId);

        return view('livewire.pos-dashboard', [
            'branches' => Branch::where('is_active', true)->orderBy('name')->get(),
            'categories' => Category::where('is_active', true)->orderBy('sort_order')->orderBy('name')->get(),
            'availableTables' => $this->availableTables(),
            'products' => $products,
            'serviceOrders' => $serviceOrders,
            'selectedServiceOrder' => $selectedServiceOrder,
            'selectedSplit' => $selectedSplit,
            'cartItems' => $cartItems,
            'subtotal' => $cartItems->sum('line_total'),
            'recentOrders' => Order::query()
                ->with(['cashier', 'branch', 'waiter'])
                ->whereIn('status', Order::financialStatuses())
                ->when($this->branchId, fn (Builder $query) => $query->where('branch_id', $this->branchId))
                ->latest('paid_at')
                ->limit(6)
                ->get(),
        ]);
    }
}
