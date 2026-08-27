<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_renders_successfully(): void
    {
        $response = $this->get(route('login'));
        $response->assertStatus(200);
        $response->assertSee('Sign In');
        $response->assertSee('Login');
        $response->assertSee('Username or Email');
    }

    public function test_user_can_authenticate_with_email(): void
    {
        $user = User::factory()->create([
            'email' => 'admin@sulop.gov.ph',
            'password' => Hash::make('password'),
            'role' => UserRole::Admin,
        ]);

        $this->post('/login', [
            'login' => 'admin@sulop.gov.ph',
            'password' => 'password',
        ]);

        $this->assertEquals(UserRole::Admin, $user->role);
    }
}
