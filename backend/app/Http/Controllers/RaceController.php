<?php

namespace App\Http\Controllers;

use App\Models\Race;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RaceController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Race::query()->orderBy('race_date');

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        // Schedule defaults to the current season (max season on file).
        // Override with ?season=2024 for a specific year, or ?season=all
        // to opt out of the season filter entirely.
        $season = $request->query('season');
        if ($season === null) {
            $current = Race::max('season');
            if ($current !== null) {
                $query->where('season', $current);
            }
        } elseif ($season !== 'all') {
            $query->where('season', (int) $season);
        }

        $races = $query->with('result')->get()
            ->map(fn (Race $r) => $this->present($r));

        return response()->json(['data' => $races]);
    }

    public function show(Race $race): JsonResponse
    {
        $race->load('result');

        return response()->json(['data' => $this->present($race)]);
    }

    private function present(Race $r): array
    {
        return [
            'id' => $r->id,
            'season' => $r->season,
            'round' => $r->round,
            'name' => $r->name,
            'circuit' => $r->circuit,
            'country' => $r->country,
            'race_date' => $r->race_date?->toIso8601String(),
            'predictions_close_at' => $r->predictions_close_at?->toIso8601String(),
            'status' => $r->status,
            'result' => $r->status === Race::STATUS_FINISHED && $r->result ? [
                'p1_driver_id' => $r->result->p1_driver_id,
                'p2_driver_id' => $r->result->p2_driver_id,
                'p3_driver_id' => $r->result->p3_driver_id,
                'dnf_count' => $r->result->dnf_count,
                'recorded_at' => $r->result->recorded_at?->toIso8601String(),
            ] : null,
        ];
    }
}
