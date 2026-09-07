<?php

namespace Tests\Feature;

use App\Models\Distance;
use App\Models\Permission;
use App\Models\Role;
use App\Models\Setting;
use App\Models\User;
use App\Services\AppSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationClosedTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\PermissionSeeder::class);
    }

    public function test_registration_form_is_shown_when_open(): void
    {
        Distance::create([
            'name' => '5 km',
            'kilometers' => 5,
            'price' => 7.50,
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $response = $this->get('http://inschrijven.test/');

        $response->assertOk();
        $response->assertSee('Inschrijven Vierdaagse Kesteren');
        $response->assertDontSee('Inschrijving gesloten');
    }

    public function test_registration_form_shows_closed_page_when_closed(): void
    {
        Setting::set('inschrijving.open', '0');
        Setting::set('inschrijving.closed_message', 'Helaas, vol is vol.');

        $response = $this->get('http://inschrijven.test/');

        $response->assertOk();
        $response->assertSee('Inschrijving gesloten');
        $response->assertSee('Helaas, vol is vol.');
        $response->assertDontSee('Welke afstand ga je lopen?');
    }

    public function test_store_is_blocked_when_registration_is_closed(): void
    {
        Setting::set('inschrijving.open', '0');

        $distance = Distance::create([
            'name' => '5 km',
            'kilometers' => 5,
            'price' => 7.50,
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $response = $this->post('http://inschrijven.test/', [
            'first_name' => 'Jan',
            'last_name' => 'Jansen',
            'postal_code' => '4041AA',
            'house_number' => '1',
            'phone_number' => '0612345678',
            'email' => 'jan@example.com',
            'distance_id' => $distance->id,
            'privacy_consent' => '1',
        ]);

        $response->assertRedirect(route('inschrijven.create'));
        $this->assertDatabaseCount('registrations', 0);
    }

    public function test_admin_can_toggle_registration_closed(): void
    {
        $user = $this->makeUserWithPermissions(['inschrijvingen_edit', 'inschrijvingen_view']);

        $this->assertTrue(AppSettings::registrationsOpen());

        $response = $this->actingAs($user)
            ->post('http://intouch.test/inschrijvingen/toggle-open');

        $response->assertRedirect(route('intouch.registrations.index'));
        $this->assertFalse(AppSettings::registrationsOpen());

        $response = $this->actingAs($user)
            ->post('http://intouch.test/inschrijvingen/toggle-open');

        $response->assertRedirect(route('intouch.registrations.index'));
        $this->assertTrue(AppSettings::registrationsOpen());
    }

    public function test_settings_can_save_registration_closed_state(): void
    {
        $user = $this->makeUserWithPermissions(['instellingen_edit']);

        $response = $this->actingAs($user)
            ->put('http://intouch.test/beheer/instellingen', [
                'sponsors_doelbedrag' => 1850,
                'sponsors_privacy_consent_required' => '1',
                'inschrijving_open' => '0',
                'inschrijving_closed_message' => 'Inschrijving is dicht.',
                'scanner_min_minutes' => 5,
                'app_noodnummers' => '06 00 00 00 00',
            ]);

        $response->assertRedirect(route('intouch.beheer.instellingen.edit'));
        $this->assertFalse(AppSettings::registrationsOpen());
        $this->assertSame('Inschrijving is dicht.', AppSettings::registrationsClosedMessage());
    }

    protected function makeUserWithPermissions(array $slugs): User
    {
        $user = User::factory()->create();
        $role = Role::firstOrCreate(['slug' => 'test-role', 'name' => 'Test'], []);
        $role->permissions()->sync(
            Permission::whereIn('slug', $slugs)->pluck('id')
        );
        $user->roles()->sync([$role->id]);

        return $user;
    }
}
