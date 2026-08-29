<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Livewire\Dashboard\Overview;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'name' => 'Officer Test',
            'username' => 'officertest',
            'email' => 'officer@sulop.gov.ph',
            'password' => Hash::make('Password123!'),
            'role' => UserRole::Admin,
            'is_active' => true,
        ]);
    }

    public function test_authenticated_user_can_view_dashboard(): void
    {
        $response = $this->actingAs($this->user)->get(route('dashboard'));
        $response->assertOk();
        $response->assertSeeLivewire(Overview::class);
        $response->assertSee('Command Center');
        $response->assertSee('Total Ayuda Released');
        $response->assertSee('Government Funds (GGMS)');
        $response->assertSee('Barangay Ayuda Leaderboard');
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get(route('dashboard'));
        $response->assertRedirect(route('login'));
    }

    public function test_dashboard_can_switch_tabs_and_timeframes(): void
    {
        Livewire::actingAs($this->user)
            ->test(Overview::class)
            ->call('setActiveTab', 'audit')
            ->assertSet('activeTab', 'audit')
            ->call('setTimeframe', 'month')
            ->assertSet('timeframe', 'month');
    }
}
