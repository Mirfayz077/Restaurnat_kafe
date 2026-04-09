<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    public function test_guest_can_open_public_home_page(): void
    {
        $response = $this->get('/');

        $response
            ->assertOk()
            ->assertSee('Restoran uchun bitta asosiy sahifa');
    }
}
