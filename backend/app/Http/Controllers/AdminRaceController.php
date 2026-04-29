<?php

namespace App\Http\Controllers;

use App\Models\Race;
use App\Models\RaceResult;
use App\Services\ScoringService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AdminRaceController extends Controller
{
    public function __construct(private ScoringService $scoring) {}

    public function recordResult(Request $request, Race $race): JsonResponse
    {
        $data = $request->validate([
            'p1_driver_id' => ['required', 'integer', Rule::exists('drivers', 'id')],
            'p2_driver_id' => ['required', 'integer', Rule::exists('drivers', 'id')],
            'p3_driver_id' => ['required', 'integer', Rule::exists('drivers', 'id')],
            'dnf_count'    => ['required', 'integer', 'min:0', 'max:20'],
        ]);

        $picks = [$data['p1_driver_id'], $data['p2_driver_id'], $data['p3_driver_id']];
        if (count($picks) !== count(array_unique($picks))) {
            throw ValidationException::withMessages([
                'drivers' => 'P1, P2, and P3 must be three different drivers.',
            ]);
        }

        $result = RaceResult::updateOrCreate(
            ['race_id' => $race->id],
            [
                'p1_driver_id' => $data['p1_driver_id'],
                'p2_driver_id' => $data['p2_driver_id'],
                'p3_driver_id' => $data['p3_driver_id'],
                'dnf_count' => $data['dnf_count'],
                'recorded_at' => now(),
            ],
        );

        $race->update(['status' => Race::STATUS_FINISHED]);

        $this->scoring->recomputeForRace($race->fresh('result', 'predictions'));

        return response()->json(['data' => [
            'race_id' => $race->id,
            'p1_driver_id' => $result->p1_driver_id,
            'p2_driver_id' => $result->p2_driver_id,
            'p3_driver_id' => $result->p3_driver_id,
            'dnf_count' => $result->dnf_count,
            'recorded_at' => $result->recorded_at->toIso8601String(),
            'predictions_scored' => $race->predictions()->count(),
        ]]);
    }
}
