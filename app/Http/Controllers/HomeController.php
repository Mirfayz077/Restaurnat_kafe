<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        $categoryThemes = [
            'burgers' => [
                'theme' => 'is-espresso',
                'cue' => 'Grill comfort',
                'summary' => "Restoran uchun bitta asosiy sahifa endi burgerlarni tez topish, solishtirish va buyurtma berishga qulay vitringa aylandi.",
            ],
            'hot-dishes' => [
                'theme' => 'is-saffron',
                'cue' => 'Kitchen classics',
                'summary' => "Issiq taomlar bo'limi oilaviy tushlik va kechki ovqat uchun asosiy tanlovlarni ixcham ko'rsatadi.",
            ],
            'drinks' => [
                'theme' => 'is-olive',
                'cue' => 'Coffee and bar',
                'summary' => "Qahva, limonad va ichimliklarni bir joyda ko'rib, taomga mos pairing tanlash oson bo'ladi.",
            ],
            'desserts' => [
                'theme' => 'is-rose',
                'cue' => 'Sweet finish',
                'summary' => "Desertlar yakuniy tanlov sifatida alohida bo'limda turadi va mijozga tez qaror qabul qilishga yordam beradi.",
            ],
        ];

        $fallbackMenuCatalog = collect([
            [
                'name' => 'Burgers',
                'slug' => 'burgers',
                'count' => 2,
                'theme' => 'is-espresso',
                'cue' => 'Grill comfort',
                'summary' => $categoryThemes['burgers']['summary'],
                'items' => [
                    ['id' => 'bg-001', 'name' => 'Classic Burger', 'description' => 'Beef patty, cheese, fries', 'price' => 42000, 'station' => 'Kitchen', 'sku' => 'BG-001'],
                    ['id' => 'bg-002', 'name' => 'Chicken Burger', 'description' => 'Crispy chicken, lettuce, sauce', 'price' => 39000, 'station' => 'Kitchen', 'sku' => 'BG-002'],
                ],
            ],
            [
                'name' => 'Hot Dishes',
                'slug' => 'hot-dishes',
                'count' => 2,
                'theme' => 'is-saffron',
                'cue' => 'Kitchen classics',
                'summary' => $categoryThemes['hot-dishes']['summary'],
                'items' => [
                    ['id' => 'hd-001', 'name' => 'Lagman', 'description' => 'Traditional noodle bowl', 'price' => 36000, 'station' => 'Kitchen', 'sku' => 'HD-001'],
                    ['id' => 'hd-002', 'name' => 'Shashlik Set', 'description' => 'Three skewers and garnish', 'price' => 54000, 'station' => 'Kitchen', 'sku' => 'HD-002'],
                ],
            ],
            [
                'name' => 'Drinks',
                'slug' => 'drinks',
                'count' => 2,
                'theme' => 'is-olive',
                'cue' => 'Coffee and bar',
                'summary' => $categoryThemes['drinks']['summary'],
                'items' => [
                    ['id' => 'dr-001', 'name' => 'Americano', 'description' => 'Freshly brewed coffee', 'price' => 18000, 'station' => 'Bar', 'sku' => 'DR-001'],
                    ['id' => 'dr-002', 'name' => 'Lemonade', 'description' => 'House-made cold lemonade', 'price' => 16000, 'station' => 'Bar', 'sku' => 'DR-002'],
                ],
            ],
            [
                'name' => 'Desserts',
                'slug' => 'desserts',
                'count' => 1,
                'theme' => 'is-rose',
                'cue' => 'Sweet finish',
                'summary' => $categoryThemes['desserts']['summary'],
                'items' => [
                    ['id' => 'ds-001', 'name' => 'Cheesecake', 'description' => 'Berry cheesecake slice', 'price' => 22000, 'station' => 'Kitchen', 'sku' => 'DS-001'],
                ],
            ],
        ]);

        $menuCatalog = $this->hasMenuTables()
            ? $this->buildMenuCatalog($categoryThemes)
            : $fallbackMenuCatalog;

        if ($menuCatalog->isEmpty()) {
            $menuCatalog = $fallbackMenuCatalog;
        }

        return view('home', [
            'menuCatalog' => $menuCatalog,
            'stats' => [
                'branches' => Schema::hasTable('branches') ? Branch::where('is_active', true)->count() : 1,
                'sections' => $menuCatalog->count(),
                'menuItems' => $menuCatalog->sum('count'),
            ],
        ]);
    }

    protected function hasMenuTables(): bool
    {
        return collect(['categories', 'products'])->every(
            fn (string $table) => Schema::hasTable($table)
        );
    }

    /**
     * @param  array<string, array{theme: string, cue: string, summary: string}>  $categoryThemes
     * @return Collection<int, array{name: string, slug: string, count: int, theme: string, cue: string, summary: string, items: array<int, array{id: int|string, name: string, description: ?string, price: float, station: string, sku: string}>}>
     */
    protected function buildMenuCatalog(array $categoryThemes): Collection
    {
        $categories = Category::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->withCount([
                'products' => fn ($query) => $query->where('is_active', true),
            ])
            ->get();

        $products = Product::query()
            ->where('is_active', true)
            ->orderBy('category_id')
            ->orderBy('name')
            ->get()
            ->groupBy('category_id');

        return $categories->map(function (Category $category) use ($categoryThemes, $products) {
            $meta = $categoryThemes[$category->slug] ?? [
                'theme' => 'is-espresso',
                'cue' => 'House picks',
                'summary' => "Mijoz uchun eng muhim itemlar birinchi ko'rinadigan, tez ko'rib chiqiladigan menyu bo'limi.",
            ];

            return [
                'name' => $category->name,
                'slug' => $category->slug,
                'count' => (int) $category->products_count,
                'theme' => $meta['theme'],
                'cue' => $meta['cue'],
                'summary' => $meta['summary'],
                'items' => $products
                    ->get($category->id, collect())
                    ->map(fn (Product $product) => [
                        'id' => $product->id,
                        'name' => $product->name,
                        'description' => $product->description,
                        'price' => (float) $product->price,
                        'station' => $product->stationLabel(),
                        'sku' => $product->sku,
                    ])
                    ->values()
                    ->all(),
            ];
        })->values();
    }
}
