<?php

namespace Tests\Feature\Admin;

use App\Models\MentoringSession;
use App\Models\MentorProfile;
use App\Models\Mentorship;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminMentorshipFiltersAndPaginationTest extends TestCase
{
    use RefreshDatabase;

    private const ALICE_NAME = 'Alice Martin Mentor';

    private const BOB_NAME = 'Bob Dupont Mentor';

    private const PAGE_TWO_QUERY = 'page=2';

    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->admin()->create();
    }

    private function assertPaginationPreserved(string $routeName, array $params, array $expectedStrings): void
    {
        $response = $this->actingAs($this->admin)->get(route($routeName, $params));
        $response->assertStatus(200);
        $content = $response->getContent();
        foreach ($expectedStrings as $expected) {
            $this->assertStringContainsString($expected, $content);
        }
        $this->assertStringContainsString(self::PAGE_TWO_QUERY, $content);
    }

    public function test_mentors_index_filters_by_mentor_name_and_preserves_query_string()
    {
        $user1 = User::factory()->create(['name' => self::ALICE_NAME, 'user_type' => 'mentor']);
        MentorProfile::factory()->create(['user_id' => $user1->id, 'is_published' => true]);

        $user2 = User::factory()->create(['name' => self::BOB_NAME, 'user_type' => 'mentor']);
        MentorProfile::factory()->create(['user_id' => $user2->id, 'is_published' => false]);

        // 1. Search by name
        $response = $this->actingAs($this->admin)->get(route('admin.mentors.index', ['search' => 'Alice']));
        $response->assertStatus(200);
        $response->assertSee(self::ALICE_NAME);
        $response->assertDontSee(self::BOB_NAME);

        // 2. Search by mentor_name param
        $response = $this->actingAs($this->admin)->get(route('admin.mentors.index', ['mentor_name' => 'Bob']));
        $response->assertStatus(200);
        $response->assertSee(self::BOB_NAME);
        $response->assertDontSee(self::ALICE_NAME);

        // 3. Create enough mentors to have multiple pages with status filter
        for ($i = 0; $i < 20; $i++) {
            $u = User::factory()->create(['name' => "Mentor Extra {$i}", 'user_type' => 'mentor']);
            MentorProfile::factory()->create(['user_id' => $u->id, 'is_published' => true]);
        }

        $this->assertPaginationPreserved('admin.mentors.index', [
            'status' => 'published',
            'search' => 'Mentor',
        ], ['status=published', 'search=Mentor']);
    }

    public function test_mentorship_requests_filters_by_mentor_name_and_preserves_pagination()
    {
        $mentor1 = User::factory()->create(['name' => 'Mentor Alpha', 'user_type' => 'mentor']);
        $mentor2 = User::factory()->create(['name' => 'Mentor Beta', 'user_type' => 'mentor']);
        $mentee = User::factory()->create(['name' => 'Jeune Student', 'user_type' => 'jeune']);

        Mentorship::create([
            'mentor_id' => $mentor1->id,
            'mentee_id' => $mentee->id,
            'status' => 'pending',
        ]);
        Mentorship::create([
            'mentor_id' => $mentor2->id,
            'mentee_id' => $mentee->id,
            'status' => 'accepted',
        ]);

        // Search by mentor_name
        $response = $this->actingAs($this->admin)->get(route('admin.mentorship.requests', ['mentor_name' => 'Alpha']));
        $response->assertStatus(200);
        $response->assertSee('Mentor Alpha');
        $response->assertDontSee('Mentor Beta');

        // Create 25 requests with pending status to test pagination preservation
        for ($i = 0; $i < 25; $i++) {
            $m = User::factory()->create(['name' => "BatchMentor {$i}", 'user_type' => 'mentor']);
            Mentorship::create([
                'mentor_id' => $m->id,
                'mentee_id' => $mentee->id,
                'status' => 'pending',
            ]);
        }

        $this->assertPaginationPreserved('admin.mentorship.requests', [
            'status' => 'pending',
            'mentor_name' => 'BatchMentor',
        ], ['status=pending', 'mentor_name=BatchMentor']);
    }

    public function test_mentorship_sessions_filters_by_mentor_name_and_preserves_pagination()
    {
        $mentor1 = User::factory()->create(['name' => 'Mentor Carlos', 'user_type' => 'mentor']);
        $mentor2 = User::factory()->create(['name' => 'Mentor Denis', 'user_type' => 'mentor']);
        $mentee = User::factory()->create(['name' => 'Jeune Mentee', 'user_type' => 'jeune']);

        $session1 = MentoringSession::create([
            'mentor_id' => $mentor1->id,
            'title' => 'Session Carlos',
            'status' => 'completed',
            'scheduled_at' => now()->subDay(),
            'duration_minutes' => 60,
        ]);
        $session1->mentees()->attach($mentee->id);

        $session2 = MentoringSession::create([
            'mentor_id' => $mentor2->id,
            'title' => 'Session Denis',
            'status' => 'proposed',
            'scheduled_at' => now()->addDay(),
            'duration_minutes' => 45,
        ]);
        $session2->mentees()->attach($mentee->id);

        // Filter by mentor_name
        $response = $this->actingAs($this->admin)->get(route('admin.mentorship.sessions', ['mentor_name' => 'Carlos']));
        $response->assertStatus(200);
        $response->assertSee('Session Carlos');
        $response->assertDontSee('Session Denis');

        // Test pagination preservation
        for ($i = 0; $i < 25; $i++) {
            $sess = MentoringSession::create([
                'mentor_id' => $mentor1->id,
                'title' => "Extra Session {$i}",
                'status' => 'completed',
                'scheduled_at' => now()->subDays($i + 2),
                'duration_minutes' => 60,
            ]);
            $sess->mentees()->attach($mentee->id);
        }

        $this->assertPaginationPreserved('admin.mentorship.sessions', [
            'status' => 'completed',
            'mentor_name' => 'Carlos',
        ], ['status=completed', 'mentor_name=Carlos']);
    }

    public function test_mentorship_evaluations_filters_by_mentor_name_and_preserves_pagination()
    {
        $mentor = User::factory()->create(['name' => 'Professeur Mentor', 'user_type' => 'mentor']);
        $mentee = User::factory()->create(['name' => 'Eleve Jeune', 'user_type' => 'jeune']);

        for ($i = 0; $i < 20; $i++) {
            $s = MentoringSession::create([
                'mentor_id' => $mentor->id,
                'title' => "First Session {$i}",
                'status' => 'completed',
                'is_first_session' => true,
                'scheduled_at' => now()->subDays($i + 1),
                'duration_minutes' => 60,
            ]);
            $s->mentees()->attach($mentee->id);
        }

        $this->assertPaginationPreserved('admin.mentorship.evaluations', [
            'first_only' => 0,
            'mentor_name' => 'Professeur',
        ], ['mentor_name=Professeur', 'first_only=0']);
    }
}
