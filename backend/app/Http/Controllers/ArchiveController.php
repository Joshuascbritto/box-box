<?php

namespace App\Http\Controllers;

use App\Models\Race;
use Illuminate\Http\JsonResponse;

class ArchiveController extends Controller
{
    public function index(): JsonResponse
    {
        // Last three completed seasons, ordered most-recent first.
        $seasons = Race::query()
            ->where('status', Race::STATUS_FINISHED)
            ->select('season')
            ->groupBy('season')
            ->orderByDesc('season')
            ->limit(3)
            ->pluck('season')
            ->all();

        $races = Race::query()
            ->whereIn('season', $seasons)
            ->where('status', Race::STATUS_FINISHED)
            ->with('result')
            ->orderBy('season', 'desc')
            ->orderBy('round', 'asc')
            ->get();

        $grouped = $races->groupBy('season')->map(function ($group, $season) {
            return [
                'season' => (int) $season,
                'race_count' => $group->count(),
                'races' => $group->map(fn (Race $r) => [
                    'id' => $r->id,
                    'round' => $r->round,
                    'name' => $r->name,
                    'circuit' => $r->circuit,
                    'country' => $r->country,
                    'race_date' => $r->race_date?->toIso8601String(),
                    'p1_driver_id' => $r->result?->p1_driver_id,
                    'p2_driver_id' => $r->result?->p2_driver_id,
                    'p3_driver_id' => $r->result?->p3_driver_id,
                    'dnf_count' => $r->result?->dnf_count,
                ])->values(),
            ];
        })->values();

        return response()->json(['data' => $grouped]);
    }
}
