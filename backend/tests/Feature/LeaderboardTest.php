<?php

namespace Tests\Feature;

use App\Models\Driver;
use App\Models\Prediction;
use App\Models\Race;
use App\Models\RaceResult;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LeaderboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_leaderboard_orders_by_total_points_desc_and_only_counts_finished_races(): void
    {
        $alice = User::factory()->create(['name' => 'Alice']);
        $bob   = User::factory()->create(['name' => 'Bob']);

        $d1 = Driver::create(['name' => 'A', 'code' => 'AAA', 'team' => 'X', 'number' => 1, 'active' => true]);
        $d2 = Driver::create(['name' => 'B', 'code' => 'BBB', 'team' => 'Y', 'number' => 2, 'active' => true]);
        $d3 = Driver::create(['name' => 'C', 'code' => 'CCC', 'team' => 'Z', 'number' => 3, 'active' => true]);

        $finished = Race::create([
            'season' => 2025, 'round' => 1, 'name' => 'F', 'circuit' => '-', 'country' => '-',
            'race_date' => now()->subDay(), 'predictions_close_at' => now()->subDay()->subHour(),
            'status' => Race::STATUS_FINISHED,
        ]);
        RaceResult::create([
            'race_id' => $finished->id,
            'p1_driver_id' => $d1->id, 'p2_driver_id' => $d2->id, 'p3_driver_id' => $d3->id,
            'dnf_count' => 2, 'recorded_at' => now()->subDay(),
        ]);

        $upcoming = Race::create([
            'season' => 2025, 'round' => 2, 'name' => 'U', 'circuit' => '-', 'country' => '-',
            'race_date' => now()->addDay(), 'predictions_close_at' => now()->addDay()->subHour(),
            'status' => Race::STATUS_UPCOMING,
        ]);

        Prediction::create(['user_id' => $alice->id, 'race_id' => $finished->id, 'p1_driver_id' => $d1->id, 'p2_driver_id' => $d2->id, 'p3_driver_id' => $d3->id, 'dnf_count' => 2, 'points' => 93, 'submitted_at' => now()]);
        Prediction::create(['user_id' => $bob->id,   'race_id' => $finished->id, 'p1_driver_id' => $d1->id, 'p2_driver_id' => $d2->id, 'p3_driver_id' => $d3->id, 'dnf_count' => 5, 'points' => 58, 'submitted_at' => now()]);

        // Bob also has a prediction on an upcoming race — must not count.
        Prediction::create(['user_id' => $bob->id, 'race_id' => $upcoming->id, 'p1_driver_id' => $d1->id, 'p2_driver_id' => $d2->id, 'p3_driver_id' => $d3->id, 'dnf_count' => 0, 'points' => null, 'submitted_at' => now()]);

        $response = $this->getJson('/api/leaderboard')->assertOk();
        $data = $response->json('data');

        $this->assertSame('Alice', $data[0]['user']['name']);
        $this->assertSame(93,      $data[0]['total_points']);
        $this->assertSame(1,       $data[0]['perfect_podiums']);

        $this->assertSame('Bob', $data[1]['user']['name']);
        $this->assertSame(58,    $data[1]['total_points']);
        $this->assertSame(1,     $data[1]['races_predicted']);
    }
}
