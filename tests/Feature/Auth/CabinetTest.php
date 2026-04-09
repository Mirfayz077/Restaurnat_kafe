<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Database\Seeders\RestaurantPosSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CabinetTest extends TestCase
{
    use RefreshDatabase;

    public function test_waiter_sees_waiter_cabinet(): void
    {
        $this->seed(RestaurantPosSeeder::class);

        $waiter = User::where('login', 'waiter')->firstOrFail();

        $this->actingAs($waiter)
            ->get(route('cabinet'))
            ->assertOk()
            ->assertSee('Servis va stol boshqaruv kabineti')
            ->assertSee('Open waiter panel');
    }

    public function test_bartender_sees_bar_cabinet(): void
    {
        $this->seed(RestaurantPosSeeder::class);

        $bartender = User::where('login', 'bartender')->firstOrFail();

        $this->actingAs($bartender)
            ->get(route('cabinet'))
            ->assertOk()
            ->assertSee('Barmen operatsiya kabineti')
            ->assertSee('Open queue');
    }
}
