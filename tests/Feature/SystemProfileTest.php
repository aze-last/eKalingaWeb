<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Livewire\Settings\SystemProfile;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class SystemProfileTest extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->superAdmin = User::factory()->create([
            'name' => 'Municipal Super Admin',
            'username' => 'superadmin',
            'email' => 'superadmin@sulop.gov.ph',
            'password' => Hash::make('Password123!'),
            'role' => UserRole::SuperAdmin,
            'is_active' => true,
        ]);

        $this->admin = User::factory()->create([
            'name' => 'Regular Admin Officer',
            'username' => 'regularadmin',
            'email' => 'admin@sulop.gov.ph',
            'password' => Hash::make('AdminPassword123!'),
            'role' => UserRole::Admin,
            'is_active' => true,
        ]);
    }

    public function test_superadmin_can_access_system_profile(): void
    {
        $response = $this->actingAs($this->superAdmin)->get(route('settings.profile'));
        $response->assertOk();
        $response->assertSeeLivewire(SystemProfile::class);
        $response->assertSee('System Profile');
    }

    public function test_regular_admin_is_forbidden_from_system_profile(): void
    {
        $response = $this->actingAs($this->admin)->get(route('settings.profile'));
        $response->assertForbidden();
    }

    public function test_superadmin_can_update_text_branding_settings(): void
    {
        Livewire::actingAs($this->superAdmin)
            ->test(SystemProfile::class)
            ->set('system_name', 'eKalinga Pro+')
            ->set('system_subtitle', 'Comprehensive Ayuda Suite')
            ->set('municipality_name', 'Municipality of Sulop Highlands')
            ->set('province_name', 'Province of Davao del Sur')
            ->set('country_name', 'Republic of the Philippines')
            ->set('municipal_address', 'Poblacion, Sulop, Davao del Sur 8009')
            ->set('tagline', 'Progress Through Compassion')
            ->call('saveSettings')
            ->assertDispatched('toast', function ($event, $params) {
                return ($params['type'] ?? '') === 'success';
            });

        $this->assertEquals('eKalinga Pro+', Setting::get('system_name'));
        $this->assertEquals('Comprehensive Ayuda Suite', Setting::get('system_subtitle'));
        $this->assertEquals('Municipality of Sulop Highlands', Setting::get('municipality_name'));
        $this->assertEquals('Progress Through Compassion', Setting::get('tagline'));
    }

    public function test_superadmin_can_upload_seal_favicon_and_login_bg(): void
    {
        Storage::fake('public');

        $seal = UploadedFile::fake()->image('official_seal.png', 300, 300);
        $fav = UploadedFile::fake()->image('custom_fav.png', 64, 64);
        $bg = UploadedFile::fake()->image('municipal_hall.jpg', 1920, 1080);

        Livewire::actingAs($this->superAdmin)
            ->test(SystemProfile::class)
            ->set('sealFile', $seal)
            ->set('faviconFile', $fav)
            ->set('loginBgFile', $bg)
            ->call('saveSettings')
            ->assertDispatched('toast', function ($event, $params) {
                return ($params['type'] ?? '') === 'success';
            });

        $sealUrl = Setting::get('municipal_seal_url');
        $favUrl = Setting::get('favicon_url');
        $bgUrl = Setting::get('login_bg_url');

        $this->assertNotNull($sealUrl);
        $this->assertNotNull($favUrl);
        $this->assertNotNull($bgUrl);

        $this->assertStringStartsWith('/storage/branding/', $sealUrl);
        $this->assertStringStartsWith('/storage/branding/', $favUrl);
        $this->assertStringStartsWith('/storage/branding/', $bgUrl);
    }

    public function test_superadmin_can_remove_custom_assets(): void
    {
        Storage::fake('public');

        Setting::set('municipal_seal_url', '/storage/branding/seal_custom.png');
        Setting::set('favicon_url', '/storage/branding/fav_custom.png');
        Setting::set('login_bg_url', '/storage/branding/bg_custom.jpg');

        Livewire::actingAs($this->superAdmin)
            ->test(SystemProfile::class)
            ->call('removeSeal')
            ->call('removeFavicon')
            ->call('removeLoginBg')
            ->assertDispatched('toast');

        $this->assertEquals('/images/Site_logo.png', Setting::get('municipal_seal_url'));
        $this->assertEquals('/images/Site_logo.png', Setting::get('favicon_url'));
        $this->assertEmpty(Setting::get('login_bg_url'));
    }

    public function test_login_page_renders_dynamic_branding_elements(): void
    {
        Setting::set('system_name', 'eKalinga Elite');
        Setting::set('system_subtitle', 'Digital Ayuda Platform');
        Setting::set('municipality_name', 'Municipality of Sulop');
        Setting::set('tagline', 'Service Beyond Boundaries');

        $response = $this->get(route('login'));
        $response->assertOk();
        $response->assertSee('eKalinga Elite');
        $response->assertSee('Digital Ayuda Platform');
        $response->assertSee('Municipality of Sulop');
        $response->assertSee('Service Beyond Boundaries');
    }
}
