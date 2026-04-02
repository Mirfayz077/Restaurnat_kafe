<?php

namespace App\Livewire;

use Livewire\Component;

class PosDashboard extends Component
{
    public string $search = '';

    public string $category = 'all';

    public bool $rushMode = true;

    public int $servedToday = 128;

    public int $ticketsOpen = 14;

    public int $activeSlide = 0;

    public array $menuItems = [
        [
            'id' => 1,
            'name' => 'Charcoal Burger',
            'category' => 'grill',
            'price' => 42000,
            'tag' => 'Top seller',
            'description' => 'Smoked beef, cheddar, house sauce.',
        ],
        [
            'id' => 2,
            'name' => 'Lagman Bowl',
            'category' => 'kitchen',
            'price' => 38000,
            'tag' => 'Fresh',
            'description' => 'Hand-pulled noodles with rich broth.',
        ],
        [
            'id' => 3,
            'name' => 'Cafe Latte',
            'category' => 'bar',
            'price' => 19000,
            'tag' => 'Smooth',
            'description' => 'Double shot espresso with steamed milk.',
        ],
        [
            'id' => 4,
            'name' => 'Caesar Salad',
            'category' => 'cold',
            'price' => 26000,
            'tag' => 'Light',
            'description' => 'Crisp greens, parmesan, classic dressing.',
        ],
        [
            'id' => 5,
            'name' => 'Shashlik Combo',
            'category' => 'grill',
            'price' => 51000,
            'tag' => 'Weekend',
            'description' => 'Three skewers with onion salad.',
        ],
        [
            'id' => 6,
            'name' => 'Berry Cheesecake',
            'category' => 'dessert',
            'price' => 24000,
            'tag' => 'Sweet',
            'description' => 'Creamy cake with berry glaze.',
        ],
    ];

    public array $cart = [
        1 => 2,
        3 => 1,
        5 => 1,
    ];

    public array $slides = [
        [
            'title' => 'Kitchen Rush',
            'value' => '07 orders',
            'copy' => 'Local orders are routed without any CDN dependency.',
        ],
        [
            'title' => 'Cafe Flow',
            'value' => '18 min',
            'copy' => 'Livewire state updates keep the ticket board reactive.',
        ],
        [
            'title' => 'Dessert Tray',
            'value' => '92%',
            'copy' => 'Swiper-ready carousel cards for featured menu items.',
        ],
    ];

    public function setCategory(string $category): void
    {
        $this->category = $category;
    }

    public function toggleRushMode(): void
    {
        $this->rushMode = ! $this->rushMode;
    }

    public function addToCart(int $itemId): void
    {
        $this->cart[$itemId] = ($this->cart[$itemId] ?? 0) + 1;
        $this->servedToday++;
        $this->ticketsOpen++;
    }

    public function removeFromCart(int $itemId): void
    {
        if (! isset($this->cart[$itemId])) {
            return;
        }

        $this->cart[$itemId]--;

        if ($this->cart[$itemId] <= 0) {
            unset($this->cart[$itemId]);
        }

        $this->ticketsOpen = max(1, $this->ticketsOpen - 1);
    }

    public function nextSlide(): void
    {
        $this->activeSlide = ($this->activeSlide + 1) % count($this->slides);
    }

    public function previousSlide(): void
    {
        $this->activeSlide = ($this->activeSlide - 1 + count($this->slides)) % count($this->slides);
    }

    public function getVisibleMenuProperty(): array
    {
        $search = mb_strtolower(trim($this->search));

        return array_values(array_filter($this->menuItems, function (array $item) use ($search) {
            $matchesCategory = $this->category === 'all' || $item['category'] === $this->category;

            if ($search === '') {
                return $matchesCategory;
            }

            $haystack = mb_strtolower($item['name'].' '.$item['description'].' '.$item['tag']);

            return $matchesCategory && str_contains($haystack, $search);
        }));
    }

    public function getCartItemsProperty(): array
    {
        $items = [];

        foreach ($this->cart as $itemId => $quantity) {
            $menuItem = collect($this->menuItems)->firstWhere('id', $itemId);

            if (! $menuItem) {
                continue;
            }

            $items[] = [
                ...$menuItem,
                'quantity' => $quantity,
                'subtotal' => $quantity * $menuItem['price'],
            ];
        }

        return $items;
    }

    public function getCartTotalProperty(): int
    {
        return collect($this->cartItems)->sum('subtotal');
    }

    public function render()
    {
        return view('livewire.pos-dashboard', [
            'categories' => [
                'all' => 'All',
                'grill' => 'Grill',
                'kitchen' => 'Kitchen',
                'bar' => 'Bar',
                'cold' => 'Cold',
                'dessert' => 'Dessert',
            ],
            'visibleMenu' => $this->visibleMenu,
            'cartItems' => $this->cartItems,
            'cartTotal' => $this->cartTotal,
        ]);
    }
}
