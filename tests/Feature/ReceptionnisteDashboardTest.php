<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

class ReceptionnisteDashboardTest extends TestCase
{
    public function test_receptionniste_can_access_dashboard()
    {
        $user = User::first() ?: User::factory()->create([
            'email' => 'receptionniste@loans.com',
            'role' => 'receptionniste',
        ]);

        $user->assignRole('receptionniste');

        $this->actingAs($user);

        $response = $this->get('/receptionniste/dashboard');

        $response->assertStatus(200);
    }
}

