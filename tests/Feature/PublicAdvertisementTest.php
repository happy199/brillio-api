<?php

namespace Tests\Feature;

use App\Models\Advertisement;
use App\Models\Organization;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicAdvertisementTest extends TestCase
{
    use RefreshDatabase;

    public function test_visitor_can_view_public_advertisements_page()
    {
        $organization = Organization::factory()->create();

        // Create approved ad
        $approvedAd = Advertisement::create([
            'title' => 'Approved Ad Title',
            'image_path' => 'advertisements/approved.webp',
            'link_url' => 'https://example.com/approved',
            'status' => Advertisement::STATUS_APPROVED,
            'organization_id' => $organization->id,
        ]);

        // Create pending ad
        $pendingAd = Advertisement::create([
            'title' => 'Pending Ad Title',
            'image_path' => 'advertisements/pending.webp',
            'link_url' => 'https://example.com/pending',
            'status' => Advertisement::STATUS_PENDING,
            'organization_id' => $organization->id,
        ]);

        // Create rejected ad
        $rejectedAd = Advertisement::create([
            'title' => 'Rejected Ad Title',
            'image_path' => 'advertisements/rejected.webp',
            'link_url' => 'https://example.com/rejected',
            'status' => Advertisement::STATUS_REJECTED,
            'organization_id' => $organization->id,
        ]);

        $response = $this->get(route('public.advertisements'));

        $response->assertStatus(200);
        $response->assertSee('Approved Ad Title');
        $response->assertSee('https://example.com/approved');
        $response->assertDontSee('Pending Ad Title');
        $response->assertDontSee('Rejected Ad Title');
    }
}
