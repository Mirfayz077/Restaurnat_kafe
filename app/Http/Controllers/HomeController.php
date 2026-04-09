<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        $today = now()->toDateString();
        $hasOperationalTables = collect([
            'branches',
            'products',
            'roles',
            'users',
            'orders',
            'order_items',
        ])->every(fn (string $table) => Schema::hasTable($table));

        $activeItemsQuery = $hasOperationalTables
            ? OrderItem::query()->whereHas('order', fn ($query) => $query->whereIn('status', Order::activeStatuses()))
            : null;

        return view('home', [
            'stats' => [
                'branches' => $hasOperationalTables ? Branch::where('is_active', true)->count() : 0,
                'menuItems' => $hasOperationalTables ? Product::where('is_active', true)->count() : 0,
                'roles' => $hasOperationalTables ? Role::count() : 0,
                'staff' => $hasOperationalTables ? User::count() : 0,
                'ordersToday' => $hasOperationalTables ? Order::whereDate('placed_at', $today)->count() : 0,
                'readyItems' => $activeItemsQuery ? (clone $activeItemsQuery)->where('preparation_status', 'ready')->sum('quantity') : 0,
            ],
            'roleHighlights' => [
                [
                    'label' => 'Manager / Admin',
                    'summary' => "Zakazlar, itemlar, filiallar va jamoa ustidan to'liq nazorat.",
                    'points' => ['Live monitoring', 'Catalog control', 'Reports and analytics'],
                ],
                [
                    'label' => 'Cashier',
                    'summary' => "Checkout, settlement, split bill va stol close jarayonlari bitta joyda.",
                    'points' => ['POS terminal', 'Receipt flow', 'Table settlement'],
                ],
                [
                    'label' => 'Waiter',
                    'summary' => "Stollar holati, yangi ticket yuborish va ready itemlarni serve qilish uchun qulay panel.",
                    'points' => ['Table status', 'Kitchen / bar send', 'Ready-to-serve tracking'],
                ],
                [
                    'label' => 'Chef / Bartender',
                    'summary' => "Kelgan zakazlarni navbat asosida qabul qilish va yangi menu itemlarni kuzatish.",
                    'points' => ['Station queue', 'Queued / preparing / ready', 'Latest menu items'],
                ],
            ],
            'serviceFlow' => [
                [
                    'step' => '01',
                    'title' => 'Waiter zakaz yuboradi',
                    'description' => "Stolga bog'langan itemlar avtomatik kitchen yoki barga jo'natiladi.",
                ],
                [
                    'step' => '02',
                    'title' => 'Chef / barmen qabul qiladi',
                    'description' => "Kelgan itemlar queued, preparing va ready bosqichlarida yuradi.",
                ],
                [
                    'step' => '03',
                    'title' => 'Cashier yakunlaydi',
                    'description' => "Full payment, split bill va stolni close qilish oqimi tayyor.",
                ],
                [
                    'step' => '04',
                    'title' => 'Manager monitoring qiladi',
                    'description' => "Mahsulotlar, xodimlar, zakazlar va hisobotlar role-based kabinetlardan boshqariladi.",
                ],
            ],
        ]);
    }
}
