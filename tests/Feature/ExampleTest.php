<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic test example.
     */
    public function test_root_redirects_unauthenticated_user_to_login(): void
    {
        $response = $this->get('/');
        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_access_dashboard(): void
    {
        $user = User::factory()->create(['role' => 'Admin']);

        $response = $this->actingAs($user)->get('/dashboard');
        $response->assertStatus(200);
    }
}
