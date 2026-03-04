<?php

namespace Tests\Feature;

use App\Models\Distance;
use App\Models\Edition;
use App\Models\Evaluation;
use App\Models\Registration;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class EvaluationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\PermissionSeeder::class);
    }

    public function test_evaluation_send_status_returns_json(): void
    {
        $edition = Edition::create([
            'name' => '2026',
            'start_date' => now(),
            'end_date' => now()->addDays(4),
            'is_active' => true,
        ]);
        $user = User::factory()->create();
        $role = Role::firstOrCreate(['slug' => 'admin', 'name' => 'Admin'], []);
        $role->permissions()->sync(
            \App\Models\Permission::whereIn('slug', ['evaluatie_view'])->pluck('id')
        );
        $user->roles()->sync([$role->id]);

        $evaluation = Evaluation::create([
            'edition_id' => $edition->id,
            'name' => 'Test evaluatie',
            'target' => 'all_paid',
            'mail_subject' => 'Test',
            'mail_body' => 'Test',
            'sent_at' => now(),
            'invitations_sent_count' => 5,
            'invitations_total' => 10,
        ]);

        $response = $this->actingAs($user)
            ->withSession(['edition_id' => $edition->id])
            ->getJson(route('intouch.registrations.evaluatie.send-status', $evaluation));

        $response->assertOk()
            ->assertJsonPath('sent', 5)
            ->assertJsonPath('total', 10);
    }

    public function test_evaluation_form_accessible_with_signed_url(): void
    {
        $edition = Edition::create([
            'name' => '2026',
            'start_date' => now(),
            'end_date' => now()->addDays(4),
            'is_active' => true,
        ]);
        $evaluation = Evaluation::create([
            'edition_id' => $edition->id,
            'name' => 'Test',
            'target' => 'all_paid',
            'mail_subject' => 'Test',
            'mail_body' => 'Test',
        ]);
        $evaluation->questions()->create([
            'type' => 'nps',
            'question_text' => 'Hoe tevreden?',
            'sort_order' => 0,
            'is_required' => true,
        ]);
        $distance = Distance::create([
            'name' => '5 km',
            'kilometers' => 5,
            'price' => 10,
            'is_active' => true,
        ]);
        $registration = Registration::create([
            'edition_id' => $edition->id,
            'distance_id' => $distance->id,
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => 'test@example.com',
            'postal_code' => '1234AB',
            'house_number' => '1',
            'phone_number' => '0612345678',
            'mollie_payment_status' => 'paid',
        ]);

        $url = URL::temporarySignedRoute(
            'inschrijven.evaluatie.form',
            now()->addHour(),
            ['evaluation' => $evaluation->id, 'registration' => $registration->id]
        );

        $response = $this->get($url);

        $response->assertOk();
        $response->assertSee('Hoe tevreden?');
    }
}
