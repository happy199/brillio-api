<?php

namespace Tests\Feature\Admin;

use App\Models\Career;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CareerDemandLevelTest extends TestCase
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

    public function test_career_demand_level_label_accessor_translates_english_terms(): void
    {
        $careerHigh = new Career(['demand_level' => 'high']);
        $this->assertEquals('Élevée', $careerHigh->demand_level_label);

        $careerMedium = new Career(['demand_level' => 'medium']);
        $this->assertEquals('Moyenne', $careerMedium->demand_level_label);

        $careerLow = new Career(['demand_level' => 'low']);
        $this->assertEquals('Faible', $careerLow->demand_level_label);

        $careerFrench = new Career(['demand_level' => 'Moyenne']);
        $this->assertEquals('Moyenne', $careerFrench->demand_level_label);
    }

    public function test_admin_careers_index_renders_french_demand_levels(): void
    {
        Career::create([
            'title' => 'Développeur IA',
            'description' => 'Développe des modèles de machine learning',
            'demand_level' => 'high',
        ]);

        $response = $this->actingAs($this->admin)
            ->withSession(['admin_2fa_passed' => true])
            ->get(route('admin.careers.index'));

        $response->assertOk();
        $response->assertSee('Élevée');
        $response->assertDontSee('>high</span>', false);
    }
}
