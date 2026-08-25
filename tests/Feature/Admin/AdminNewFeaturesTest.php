<?php

namespace Tests\Feature\Admin;

use App\Models\Mentorship;
use App\Models\Message;
use App\Models\Organization;
use App\Models\User;
use App\Models\UserFeedback;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminNewFeaturesTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'user_type' => 'admin',
            'is_admin' => true,
        ]);
    }

    public function test_admin_can_clear_all_mentorship_reports_and_flagged_messages(): void
    {
        $mentor = User::factory()->create(['user_type' => 'mentor']);
        $mentee = User::factory()->create(['user_type' => 'jeune']);

        $mentorship = Mentorship::create([
            'mentor_id' => $mentor->id,
            'mentee_id' => $mentee->id,
            'status' => 'accepted',
            'reported_at' => now(),
            'reported_by_id' => $mentee->id,
            'report_reason' => 'Propos inappropriés',
        ]);

        $message = Message::create([
            'mentorship_id' => $mentorship->id,
            'sender_id' => $mentor->id,
            'body' => '[CONTENU BLOQUÉ SURVEILLANCE PII]',
            'original_body' => 'Mon numéro est +22990000000',
            'is_flagged' => true,
            'flag_reason' => 'Numéro de téléphone détecté',
        ]);

        $response = $this->actingAs($this->admin)
            ->withSession(['admin_2fa_passed' => true])
            ->post(route('admin.mentorship-chat.clear-all'));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $mentorship->refresh();
        $this->assertNull($mentorship->reported_at);
        $this->assertNull($mentorship->reported_by_id);
        $this->assertNull($mentorship->report_reason);

        $message->refresh();
        $this->assertFalse((bool) $message->is_flagged);
        $this->assertNull($message->flag_reason);
        $this->assertEquals('Mon numéro est +22990000000', $message->body);
    }

    public function test_admin_can_fetch_paginated_analytics_comments(): void
    {
        $user = User::factory()->create(['name' => 'Élodie Kouassi', 'user_type' => 'jeune']);

        UserFeedback::create([
            'user_id' => $user->id,
            'rating' => 5,
            'comment' => 'Super plateforme de mentorat !',
        ]);

        $response = $this->actingAs($this->admin)
            ->withSession(['admin_2fa_passed' => true])
            ->get(route('admin.analytics.comments'));

        $response->assertOk();
        $response->assertJsonStructure([
            'comments',
            'current_page',
            'last_page',
            'has_more',
        ]);
        $response->assertJsonFragment([
            'comment' => 'Super plateforme de mentorat !',
            'user_type' => 'jeune',
        ]);
    }

    public function test_admin_can_view_organization_details_with_linked_mentors(): void
    {
        $org = Organization::create([
            'name' => 'Org Test',
            'contact_email' => 'contact@orgtest.com',
            'status' => 'active',
            'subscription_plan' => 'establishment',
        ]);

        $mentor = User::factory()->create(['user_type' => 'mentor']);
        $org->mentors()->attach($mentor->id);

        $response = $this->actingAs($this->admin)
            ->withSession(['admin_2fa_passed' => true])
            ->get(route('admin.organizations.show', $org));

        $response->assertOk();
        $response->assertSee('Mentors Liés');
        $response->assertSee($mentor->name);
    }

    public function test_admin_can_set_custom_member_limit_for_establishment_organization(): void
    {
        $org = Organization::create([
            'name' => 'Université Test',
            'contact_email' => 'contact@univ.com',
            'status' => 'active',
            'subscription_plan' => 'establishment',
            'custom_member_limit' => null,
        ]);

        $this->assertNull($org->getMemberLimit()); // Illimité par défaut

        $response = $this->actingAs($this->admin)
            ->withSession(['admin_2fa_passed' => true])
            ->put(route('admin.organizations.update', $org), [
                'name' => 'Université Test',
                'contact_email' => 'contact@univ.com',
                'status' => 'active',
                'subscription_plan' => 'establishment',
                'subscription_expires_at' => now()->addYear()->format('Y-m-d'),
                'custom_member_limit' => 250,
            ]);

        $response->assertRedirect(route('admin.organizations.index'));

        $org->refresh();
        $this->assertEquals(250, $org->custom_member_limit);
        $this->assertEquals(250, $org->getMemberLimit());
    }
}
