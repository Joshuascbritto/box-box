<?php

namespace Tests\Feature;

use App\Models\Driver;
use App\Models\Prediction;
use App\Models\Race;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PredictionTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private array $drivers;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->drivers = [
            Driver::create(['name' => 'Driver A', 'code' => 'AAA', 'team' => 'Team A', 'number' => 1, 'active' => true]),
            Driver::create(['name' => 'Driver B', 'code' => 'BBB', 'team' => 'Team B', 'number' => 2, 'active' => true]),
            Driver::create(['name' => 'Driver C', 'code' => 'CCC', 'team' => 'Team C', 'number' => 3, 'active' => true]),
            Driver::create(['name' => 'Driver D', 'code' => 'DDD', 'team' => 'Team D', 'number' => 4, 'active' => false]),
        ];
    }

    private function upcomingRace(array $overrides = []): Race
    {
        return Race::create(array_merge([
            'season' => 2025,
            'round' => 1,
            'name' => 'Test GP',
            'circuit' => 'Test',
            'country' => 'Testland',
            'race_date' => now()->addDays(5),
            'predictions_close_at' => now()->addDays(5)->subHour(),
            'status' => Race::STATUS_UPCOMING,
        ], $overrides));
    }

    private function authed(): self
    {
        $token = $this->user->createToken('t')->plainTextToken;

        return $this->withHeader('Authorization', "Bearer $token");
    }

    public function test_authenticated_user_can_submit_a_valid_prediction(): void
    {
        $race = $this->upcomingRace();

        $response = $this->authed()->postJson('/api/predictions', [
            'race_id' => $race->id,
            'p1_driver_id' => $this->drivers[0]->id,
            'p2_driver_id' => $this->drivers[1]->id,
            'p3_driver_id' => $this->drivers[2]->id,
            'dnf_count' => 3,
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.race_id', $race->id);

        $this->assertDatabaseHas('predictions', [
            'user_id' => $this->user->id,
            'race_id' => $race->id,
            'dnf_count' => 3,
        ]);
    }

    public function test_unauthenticated_request_is_rejected(): void
    {
        $race = $this->upcomingRace();

        $this->postJson('/api/predictions', [
            'race_id' => $race->id,
            'p1_driver_id' => $this->drivers[0]->id,
            'p2_driver_id' => $this->drivers[1]->id,
            'p3_driver_id' => $this->drivers[2]->id,
            'dnf_count' => 3,
        ])->assertStatus(401);
    }

    public function test_duplicate_drivers_are_rejected(): void
    {
        $race = $this->upcomingRace();

        $this->authed()->postJson('/api/predictions', [
            'race_id' => $race->id,
            'p1_driver_id' => $this->drivers[0]->id,
            'p2_driver_id' => $this->drivers[0]->id,
            'p3_driver_id' => $this->drivers[1]->id,
            'dnf_count' => 0,
        ])->assertStatus(422);
    }

    public function test_inactive_driver_is_rejected(): void
    {
        $race = $this->upcomingRace();

        $this->authed()->postJson('/api/predictions', [
            'race_id' => $race->id,
            'p1_driver_id' => $this->drivers[3]->id, // inactive
            'p2_driver_id' => $this->drivers[1]->id,
            'p3_driver_id' => $this->drivers[2]->id,
            'dnf_count' => 0,
        ])->assertStatus(422);
    }

    public function test_locked_race_rejects_predictions(): void
    {
        $race = $this->upcomingRace(['status' => Race::STATUS_LOCKED]);

        $this->authed()->postJson('/api/predictions', [
            'race_id' => $race->id,
            'p1_driver_id' => $this->drivers[0]->id,
            'p2_driver_id' => $this->drivers[1]->id,
            'p3_driver_id' => $this->drivers[2]->id,
            'dnf_count' => 0,
        ])->assertStatus(422);
    }

    public function test_race_with_past_close_time_rejects_predictions(): void
    {
        $race = $this->upcomingRace([
            'predictions_close_at' => now()->subMinute(),
        ]);

        $this->authed()->postJson('/api/predictions', [
            'race_id' => $race->id,
            'p1_driver_id' => $this->drivers[0]->id,
            'p2_driver_id' => $this->drivers[1]->id,
            'p3_driver_id' => $this->drivers[2]->id,
            'dnf_count' => 0,
        ])->assertStatus(422);
    }

    public function test_dnf_count_must_be_in_range(): void
    {
        $race = $this->upcomingRace();

        $this->authed()->postJson('/api/predictions', [
            'race_id' => $race->id,
            'p1_driver_id' => $this->drivers[0]->id,
            'p2_driver_id' => $this->drivers[1]->id,
            'p3_driver_id' => $this->drivers[2]->id,
            'dnf_count' => 25,
        ])->assertStatus(422);
    }

    public function test_resubmission_updates_existing_prediction(): void
    {
        $race = $this->upcomingRace();

        $this->authed()->postJson('/api/predictions', [
            'race_id' => $race->id,
            'p1_driver_id' => $this->drivers[0]->id,
            'p2_driver_id' => $this->drivers[1]->id,
            'p3_driver_id' => $this->drivers[2]->id,
            'dnf_count' => 1,
        ])->assertCreated();

        $this->authed()->postJson('/api/predictions', [
            'race_id' => $race->id,
            'p1_driver_id' => $this->drivers[2]->id,
            'p2_driver_id' => $this->drivers[1]->id,
            'p3_driver_id' => $this->drivers[0]->id,
            'dnf_count' => 5,
        ])->assertCreated();

        $this->assertSame(1, Prediction::where('user_id', $this->user->id)->count());
        $this->assertSame(5, Prediction::first()->dnf_count);
    }

    public function test_for_race_returns_only_self_when_upcoming(): void
    {
        $race = $this->upcomingRace();
        $other = User::factory()->create();
        Prediction::create([
            'user_id' => $other->id,
            'race_id' => $race->id,
            'p1_driver_id' => $this->drivers[0]->id,
            'p2_driver_id' => $this->drivers[1]->id,
            'p3_driver_id' => $this->drivers[2]->id,
            'dnf_count' => 0,
            'submitted_at' => now(),
        ]);

        $response = $this->authed()->getJson("/api/predictions/race/{$race->id}");
        $response->assertOk()->assertJson(['visibility' => 'self', 'data' => []]);
    }

    public function test_for_race_returns_all_when_locked(): void
    {
        $race = $this->upcomingRace(['status' => Race::STATUS_LOCKED]);
        $other = User::factory()->create();
        Prediction::create([
            'user_id' => $other->id,
            'race_id' => $race->id,
            'p1_driver_id' => $this->drivers[0]->id,
            'p2_driver_id' => $this->drivers[1]->id,
            'p3_driver_id' => $this->drivers[2]->id,
            'dnf_count' => 0,
            'submitted_at' => now(),
        ]);

        $response = $this->authed()->getJson("/api/predictions/race/{$race->id}");
        $response->assertOk()->assertJson(['visibility' => 'all']);
        $this->assertCount(1, $response->json('data'));
    }
}
