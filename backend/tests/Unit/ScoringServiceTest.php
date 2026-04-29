<?php

namespace Tests\Unit;

use App\Models\Prediction;
use App\Models\RaceResult;
use App\Services\ScoringService;
use Tests\TestCase;

class ScoringServiceTest extends TestCase
{
    private ScoringService $scoring;

    protected function setUp(): void
    {
        parent::setUp();
        $this->scoring = new ScoringService();
    }

    private function pred(int $p1, int $p2, int $p3, ?int $dnf): Prediction
    {
        return new Prediction([
            'p1_driver_id' => $p1,
            'p2_driver_id' => $p2,
            'p3_driver_id' => $p3,
            'dnf_count' => $dnf,
        ]);
    }

    private function raceResult(int $p1, int $p2, int $p3, int $dnf): RaceResult
    {
        return new RaceResult([
            'p1_driver_id' => $p1,
            'p2_driver_id' => $p2,
            'p3_driver_id' => $p3,
            'dnf_count' => $dnf,
        ]);
    }

    public function test_perfect_podium_with_exact_dnf(): void
    {
        $points = $this->scoring->score(
            $this->pred(1, 2, 3, 4),
            $this->raceResult(1, 2, 3, 4),
        );

        // 25 + 18 + 15 + 15 (perfect bonus) + 20 (DNF exact) = 93
        $this->assertSame(93, $points);
    }

    public function test_p1_exact_only(): void
    {
        $points = $this->scoring->score(
            $this->pred(1, 99, 98, 0),
            $this->raceResult(1, 2, 3, 5),
        );

        // 25 (P1) + 0 (P2 miss) + 0 (P3 miss) + 0 (DNF off by 5) = 25
        $this->assertSame(25, $points);
    }

    public function test_consolation_for_podium_drivers_in_wrong_position(): void
    {
        $points = $this->scoring->score(
            $this->pred(2, 3, 1, 0),
            $this->raceResult(1, 2, 3, 0),
        );

        // P1 picked driver 2 (wrong position, on podium) = 5
        // P2 picked driver 3 (wrong position, on podium) = 5
        // P3 picked driver 1 (wrong position, on podium) = 5
        // DNF exact = 20
        // Total = 35
        $this->assertSame(35, $points);
    }

    public function test_p2_exact_p3_consolation(): void
    {
        $points = $this->scoring->score(
            $this->pred(99, 2, 1, 1),
            $this->raceResult(1, 2, 3, 2),
        );

        // P1 miss = 0
        // P2 exact = 18
        // P3 picked driver 1 (on podium, wrong pos) = 5
        // DNF off by 1 = 10
        // Total = 33
        $this->assertSame(33, $points);
    }

    public function test_dnf_off_by_two(): void
    {
        $points = $this->scoring->score(
            $this->pred(99, 98, 97, 0),
            $this->raceResult(1, 2, 3, 2),
        );

        // 0 + 0 + 0 + 3 (DNF off by 2) = 3
        $this->assertSame(3, $points);
    }

    public function test_dnf_off_by_more_than_two_scores_zero(): void
    {
        $points = $this->scoring->score(
            $this->pred(99, 98, 97, 0),
            $this->raceResult(1, 2, 3, 5),
        );

        $this->assertSame(0, $points);
    }

    public function test_null_dnf_does_not_score(): void
    {
        $points = $this->scoring->score(
            $this->pred(1, 2, 3, null),
            $this->raceResult(1, 2, 3, 4),
        );

        // 25 + 18 + 15 + 15 (perfect bonus), no DNF score
        $this->assertSame(73, $points);
    }

    public function test_breakdown_lists_each_component(): void
    {
        $rows = $this->scoring->breakdown(
            $this->pred(1, 2, 3, 4),
            $this->raceResult(1, 2, 3, 4),
        );

        $labels = array_column($rows, 'label');
        $this->assertContains('P1 exact', $labels);
        $this->assertContains('P2 exact', $labels);
        $this->assertContains('P3 exact', $labels);
        $this->assertContains('Perfect podium', $labels);
        $this->assertContains('DNF exact', $labels);

        $total = array_sum(array_column($rows, 'points'));
        $this->assertSame(93, $total);
    }
}
