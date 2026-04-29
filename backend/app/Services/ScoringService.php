<?php

namespace App\Services;

use App\Models\Prediction;
use App\Models\Race;
use App\Models\RaceResult;
use Illuminate\Support\Facades\DB;

/**
 * Scoring rules:
 *  - P1 exact match:                +25
 *  - P2 exact match:                +18
 *  - P3 exact match:                +15
 *  - Driver on podium, wrong pos:    +5 each
 *  - DNF count exact:               +20
 *  - DNF count off by 1:            +10
 *  - DNF count off by 2:             +3
 *  - Perfect podium bonus (P1+P2+P3 all exact): +15
 *
 * Points for "wrong position" and "exact match" do NOT stack — a driver who
 * lands in their predicted position scores once via the position rule and is
 * not counted again under the consolation rule.
 */
class ScoringService
{
    public const POINTS_P1_EXACT = 25;
    public const POINTS_P2_EXACT = 18;
    public const POINTS_P3_EXACT = 15;
    public const POINTS_PODIUM_WRONG_POSITION = 5;
    public const POINTS_DNF_EXACT = 20;
    public const POINTS_DNF_OFF_BY_ONE = 10;
    public const POINTS_DNF_OFF_BY_TWO = 3;
    public const POINTS_PERFECT_PODIUM_BONUS = 15;

    /**
     * Calculate points for a single prediction against a race result.
     */
    public function score(Prediction $prediction, RaceResult $result): int
    {
        $points = 0;

        $predicted = [
            1 => $prediction->p1_driver_id,
            2 => $prediction->p2_driver_id,
            3 => $prediction->p3_driver_id,
        ];

        $actual = [
            1 => $result->p1_driver_id,
            2 => $result->p2_driver_id,
            3 => $result->p3_driver_id,
        ];

        $exactCount = 0;

        foreach ($predicted as $position => $driverId) {
            if ($driverId === null) {
                continue;
            }

            if ($driverId === $actual[$position]) {
                $points += match ($position) {
                    1 => self::POINTS_P1_EXACT,
                    2 => self::POINTS_P2_EXACT,
                    3 => self::POINTS_P3_EXACT,
                };
                $exactCount++;
                continue;
            }

            if (in_array($driverId, $actual, true)) {
                $points += self::POINTS_PODIUM_WRONG_POSITION;
            }
        }

        if ($exactCount === 3) {
            $points += self::POINTS_PERFECT_PODIUM_BONUS;
        }

        if ($prediction->dnf_count !== null) {
            $delta = abs($prediction->dnf_count - $result->dnf_count);
            $points += match ($delta) {
                0 => self::POINTS_DNF_EXACT,
                1 => self::POINTS_DNF_OFF_BY_ONE,
                2 => self::POINTS_DNF_OFF_BY_TWO,
                default => 0,
            };
        }

        return $points;
    }

    /**
     * Recompute and persist points for every prediction belonging to a race.
     */
    public function recomputeForRace(Race $race): void
    {
        $race->loadMissing('result');

        if (! $race->result) {
            return;
        }

        DB::transaction(function () use ($race) {
            foreach ($race->predictions as $prediction) {
                $points = $this->score($prediction, $race->result);
                $prediction->update(['points' => $points]);
            }
        });
    }

    /**
     * Detailed breakdown — used by the prediction-detail UI in Stage 4.
     *
     * @return array<int, array{label: string, points: int}>
     */
    public function breakdown(Prediction $prediction, RaceResult $result): array
    {
        $rows = [];

        $predicted = [
            1 => ['id' => $prediction->p1_driver_id, 'exact' => self::POINTS_P1_EXACT, 'label' => 'P1'],
            2 => ['id' => $prediction->p2_driver_id, 'exact' => self::POINTS_P2_EXACT, 'label' => 'P2'],
            3 => ['id' => $prediction->p3_driver_id, 'exact' => self::POINTS_P3_EXACT, 'label' => 'P3'],
        ];

        $actual = [
            1 => $result->p1_driver_id,
            2 => $result->p2_driver_id,
            3 => $result->p3_driver_id,
        ];

        $exactCount = 0;

        foreach ($predicted as $position => $row) {
            if ($row['id'] === null) {
                $rows[] = ['label' => "{$row['label']} unselected", 'points' => 0];
                continue;
            }

            if ($row['id'] === $actual[$position]) {
                $rows[] = ['label' => "{$row['label']} exact",      'points' => $row['exact']];
                $exactCount++;
            } elseif (in_array($row['id'], $actual, true)) {
                $rows[] = ['label' => "{$row['label']} on podium",  'points' => self::POINTS_PODIUM_WRONG_POSITION];
            } else {
                $rows[] = ['label' => "{$row['label']} miss",       'points' => 0];
            }
        }

        if ($exactCount === 3) {
            $rows[] = ['label' => 'Perfect podium', 'points' => self::POINTS_PERFECT_PODIUM_BONUS];
        }

        if ($prediction->dnf_count !== null) {
            $delta = abs($prediction->dnf_count - $result->dnf_count);
            $rows[] = match ($delta) {
                0 => ['label' => 'DNF exact',      'points' => self::POINTS_DNF_EXACT],
                1 => ['label' => 'DNF off by 1',   'points' => self::POINTS_DNF_OFF_BY_ONE],
                2 => ['label' => 'DNF off by 2',   'points' => self::POINTS_DNF_OFF_BY_TWO],
                default => ['label' => 'DNF miss', 'points' => 0],
            };
        }

        return $rows;
    }
}
