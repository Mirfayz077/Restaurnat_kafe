<?php

namespace App\Http\Controllers;

use App\Models\DiningTable;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class CabinetController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user()->loadMissing(['role.permissions', 'branch']);
        $scopeBranchId = in_array($user->role?->name, ['admin', 'manager'], true) ? null : $user->branch_id;
        $today = now()->toDateString();
        $roleName = $user->role?->name ?? 'staff';
        $station = match ($roleName) {
            'chef' => 'kitchen',
            'bartender' => 'bar',
            default => null,
        };

        return view('cabinet.index', [
            'user' => $user,
            'roleName' => $roleName,
            'hero' => $this->heroContent($roleName),
            'quickLinks' => $this->quickLinks($user),
            'summaryCards' => $this->summaryCards($user, $scopeBranchId, $today),
            'stationSnapshots' => collect(['kitchen', 'bar'])
                ->map(fn (string $stationName) => $this->stationSnapshot($stationName, $scopeBranchId)),
            'stationItems' => $station ? $this->stationItems($station, $user->branch_id) : collect(),
            'stationProducts' => $station ? $this->stationProducts($station) : collect(),
            'waiterOrders' => $roleName === 'waiter' ? $this->waiterOrders($user) : collect(),
            'cashierOrders' => $roleName === 'cashier' ? $this->cashierOrders($user->branch_id) : collect(),
            'managerOrders' => in_array($roleName, ['admin', 'manager'], true) ? $this->managerOrders() : collect(),
            'recentProducts' => in_array($roleName, ['admin', 'manager', 'cashier'], true)
                ? Product::query()->with('category')->where('is_active', true)->latest()->limit(6)->get()
                : collect(),
            'staffBreakdown' => in_array($roleName, ['admin', 'manager'], true)
                ? Role::query()->withCount('users')->orderBy('label')->get()
                : collect(),
            'webhookNote' => $station
                ? "Ichki waiter -> {$this->stationLabel($station)} oqimi uchun webhook shart emas. Webhook faqat sayt, Telegram bot yoki tashqi delivery servisdan zakaz qabul qilinsa kerak bo'ladi."
                : null,
        ]);
    }

    protected function heroContent(string $roleName): array
    {
        return match ($roleName) {
            'admin', 'manager' => [
                'eyebrow' => 'Control room',
                'title' => 'Manager va admin nazorat markazi',
                'description' => "Zakazlar, itemlar, bo'limlar va jamoa harakati bitta kabinetdan ko'rinadi.",
            ],
            'cashier' => [
                'eyebrow' => 'Cashier cabinet',
                'title' => 'Checkout va settlement markazi',
                'description' => "To'lovlar, receiptlar va stolni yopish jarayonlarini tez boshqarish uchun markaziy ekran.",
            ],
            'waiter' => [
                'eyebrow' => 'Waiter cabinet',
                'title' => 'Servis va stol boshqaruv kabineti',
                'description' => "Aktiv stollar, ready itemlar va yangi ticket yuborish oqimini kuzatish uchun panel.",
            ],
            'chef' => [
                'eyebrow' => 'Kitchen cabinet',
                'title' => 'Oshpaz operatsiya kabineti',
                'description' => "Kitchen queue, kelgan zakazlar va yangi menu itemlarni bir qarashda ko'rsatadi.",
            ],
            'bartender' => [
                'eyebrow' => 'Bar cabinet',
                'title' => 'Barmen operatsiya kabineti',
                'description' => "Bar queue, ichimliklar itemlari va yangi menu qo'shilgan pozitsiyalarni nazorat qilish uchun kabinet.",
            ],
            default => [
                'eyebrow' => 'Staff cabinet',
                'title' => 'Role-based ish kabineti',
                'description' => "Sizning rolingizga mos actionlar va operatsion holat shu sahifada jamlangan.",
            ],
        };
    }

    protected function quickLinks(User $user): Collection
    {
        return collect([
            [
                'label' => 'Main Cabinet',
                'description' => "Role bo'yicha overview va asosiy actionlar.",
                'route' => 'cabinet',
                'permission' => null,
            ],
            [
                'label' => 'Manager Dashboard',
                'description' => 'KPI, savdo va buyurtma overview.',
                'route' => 'dashboard',
                'permission' => 'dashboard.view',
            ],
            [
                'label' => 'POS Terminal',
                'description' => 'Checkout, split bill va receipt oqimi.',
                'route' => 'pos.index',
                'permission' => 'orders.create',
            ],
            [
                'label' => 'Waiter Panel',
                'description' => 'Stollar va service ticket oqimi.',
                'route' => 'waiter.index',
                'permission' => 'waiter.panel',
            ],
            [
                'label' => 'Kitchen Queue',
                'description' => 'Oshxona navbati va item processing.',
                'route' => 'kitchen.index',
                'permission' => 'kitchen.view',
            ],
            [
                'label' => 'Bar Queue',
                'description' => 'Bar navbati va ichimlik itemlari.',
                'route' => 'bar.index',
                'permission' => 'bar.view',
            ],
            [
                'label' => 'Products',
                'description' => 'Menu itemlar va stansiya taqsimoti.',
                'route' => 'products.index',
                'permission' => 'products.manage',
            ],
            [
                'label' => 'Staff',
                'description' => 'Xodimlar va rollarni boshqarish.',
                'route' => 'staff.index',
                'permission' => 'staff.manage',
            ],
            [
                'label' => 'Reports',
                'description' => 'Savdo va waiter performance hisobotlari.',
                'route' => 'reports.index',
                'permission' => 'reports.view',
            ],
        ])->filter(function (array $link) use ($user) {
            return $link['permission'] === null || $user->hasPermission($link['permission']);
        })->values();
    }

    protected function summaryCards(User $user, ?int $scopeBranchId, string $today): array
    {
        $roleName = $user->role?->name;

        return match ($roleName) {
            'admin', 'manager' => [
                [
                    'label' => 'Today orders',
                    'value' => Order::query()->whereDate('placed_at', $today)->count(),
                    'hint' => "Barcha filiallar bo'yicha",
                ],
                [
                    'label' => 'Today revenue',
                    'value' => number_format((float) Order::query()->whereIn('status', Order::financialStatuses())->whereDate('paid_at', $today)->sum('total'))." so'm",
                    'hint' => 'Paid va closed orderlar',
                ],
                [
                    'label' => 'Menu items',
                    'value' => Product::where('is_active', true)->count(),
                    'hint' => 'Aktiv katalog',
                ],
                [
                    'label' => 'Open service',
                    'value' => Order::query()->whereIn('status', Order::activeStatuses())->count(),
                    'hint' => 'Hozir xizmatda',
                ],
            ],
            'cashier' => [
                [
                    'label' => 'Served waiting payment',
                    'value' => $this->settlementOrdersQuery($scopeBranchId)->where('status', 'served')->count(),
                    'hint' => "To'lov kutayotgan stollar",
                ],
                [
                    'label' => 'Paid waiting close',
                    'value' => $this->settlementOrdersQuery($scopeBranchId)->where('status', 'paid')->count(),
                    'hint' => 'Close qilinishi kerak',
                ],
                [
                    'label' => 'Receipts today',
                    'value' => Order::query()
                        ->whereIn('status', Order::financialStatuses())
                        ->whereDate('paid_at', $today)
                        ->where('branch_id', $scopeBranchId)
                        ->count(),
                    'hint' => "Filial bo'yicha",
                ],
                [
                    'label' => 'Revenue today',
                    'value' => number_format((float) Order::query()
                        ->whereIn('status', Order::financialStatuses())
                        ->whereDate('paid_at', $today)
                        ->where('branch_id', $scopeBranchId)
                        ->sum('total'))." so'm",
                    'hint' => 'Kassir kabineti',
                ],
            ],
            'waiter' => [
                [
                    'label' => 'My active tables',
                    'value' => $this->waiterOrdersQuery($user)->count(),
                    'hint' => "Sizga biriktirilgan stol orderlari",
                ],
                [
                    'label' => 'Ready to serve',
                    'value' => Order::query()
                        ->where('branch_id', $scopeBranchId)
                        ->where('waiter_user_id', $user->id)
                        ->whereIn('status', ['ready'])
                        ->count(),
                    'hint' => 'Darhol servisga tayyor',
                ],
                [
                    'label' => 'Branch service',
                    'value' => Order::query()
                        ->where('branch_id', $scopeBranchId)
                        ->whereIn('status', Order::activeStatuses())
                        ->count(),
                    'hint' => 'Filialdagi aktiv orderlar',
                ],
                [
                    'label' => 'Tables available',
                    'value' => DiningTable::query()->where('branch_id', $scopeBranchId)->where('is_active', true)->count(),
                    'hint' => 'Aktiv stol fondi',
                ],
            ],
            'chef', 'bartender' => [
                [
                    'label' => 'Queued items',
                    'value' => $this->stationMetric($this->stationNameForRole($roleName), $scopeBranchId, 'queued'),
                    'hint' => 'Navbatda kutayotganlar',
                ],
                [
                    'label' => 'Preparing now',
                    'value' => $this->stationMetric($this->stationNameForRole($roleName), $scopeBranchId, 'preparing'),
                    'hint' => 'Ish jarayonidagi itemlar',
                ],
                [
                    'label' => 'Ready items',
                    'value' => $this->stationMetric($this->stationNameForRole($roleName), $scopeBranchId, 'ready'),
                    'hint' => 'Waiter qabul qilishi kerak',
                ],
                [
                    'label' => 'Menu positions',
                    'value' => Product::query()->where('station', $this->stationNameForRole($roleName))->where('is_active', true)->count(),
                    'hint' => "Sizning stansiyangiz menyusi",
                ],
            ],
            default => [
                [
                    'label' => 'Today orders',
                    'value' => Order::query()->whereDate('placed_at', $today)->count(),
                    'hint' => 'Operatsion overview',
                ],
            ],
        };
    }

    protected function stationSnapshot(string $station, ?int $branchId): array
    {
        $query = $this->activeStationItemsQuery($station, $branchId);

        return [
            'name' => $station,
            'label' => $this->stationLabel($station),
            'queued' => (clone $query)->where('preparation_status', 'queued')->sum('quantity'),
            'preparing' => (clone $query)->where('preparation_status', 'preparing')->sum('quantity'),
            'ready' => (clone $query)->where('preparation_status', 'ready')->sum('quantity'),
            'orders' => (clone $query)->distinct('order_id')->count('order_id'),
        ];
    }

    protected function stationItems(string $station, int $branchId): Collection
    {
        return $this->activeStationItemsQuery($station, $branchId)
            ->with(['order.branch', 'order.diningTable'])
            ->latest('sent_to_station_at')
            ->limit(8)
            ->get();
    }

    protected function stationProducts(string $station): Collection
    {
        return Product::query()
            ->with('category')
            ->where('station', $station)
            ->where('is_active', true)
            ->latest()
            ->limit(6)
            ->get();
    }

    protected function waiterOrders(User $user): Collection
    {
        return $this->waiterOrdersQuery($user)
            ->with(['diningTable', 'items'])
            ->latest('placed_at')
            ->limit(6)
            ->get();
    }

    protected function cashierOrders(int $branchId): Collection
    {
        return $this->settlementOrdersQuery($branchId)
            ->with(['diningTable', 'waiter', 'items', 'payments'])
            ->orderByRaw("CASE status WHEN 'paid' THEN 0 WHEN 'served' THEN 1 WHEN 'ready' THEN 2 WHEN 'in_service' THEN 3 ELSE 4 END")
            ->latest('placed_at')
            ->limit(8)
            ->get();
    }

    protected function managerOrders(): Collection
    {
        return Order::query()
            ->with(['branch', 'diningTable', 'waiter', 'cashier'])
            ->whereIn('status', array_merge(Order::activeStatuses(), ['paid']))
            ->orderByRaw("CASE status WHEN 'ready' THEN 0 WHEN 'in_service' THEN 1 WHEN 'open' THEN 2 WHEN 'served' THEN 3 WHEN 'paid' THEN 4 ELSE 5 END")
            ->latest('placed_at')
            ->limit(8)
            ->get();
    }

    protected function activeStationItemsQuery(string $station, ?int $branchId): Builder
    {
        return OrderItem::query()
            ->where('station', $station)
            ->whereIn('preparation_status', ['queued', 'preparing', 'ready'])
            ->whereHas('order', function (Builder $query) use ($branchId) {
                $query
                    ->whereIn('status', Order::activeStatuses())
                    ->when($branchId, fn (Builder $branchQuery) => $branchQuery->where('branch_id', $branchId));
            });
    }

    protected function settlementOrdersQuery(?int $branchId): Builder
    {
        return Order::query()
            ->where('order_type', 'dine_in')
            ->whereIn('status', Order::settlementStatuses())
            ->when($branchId, fn (Builder $query) => $query->where('branch_id', $branchId));
    }

    protected function waiterOrdersQuery(User $user): Builder
    {
        return Order::query()
            ->where('branch_id', $user->branch_id)
            ->where('waiter_user_id', $user->id)
            ->whereIn('status', Order::activeStatuses());
    }

    protected function stationMetric(string $station, ?int $branchId, string $status): int
    {
        return (int) $this->activeStationItemsQuery($station, $branchId)
            ->where('preparation_status', $status)
            ->sum('quantity');
    }

    protected function stationNameForRole(?string $roleName): string
    {
        return $roleName === 'bartender' ? 'bar' : 'kitchen';
    }

    protected function stationLabel(string $station): string
    {
        return config("pos.product_stations.{$station}", ucfirst($station));
    }
}
