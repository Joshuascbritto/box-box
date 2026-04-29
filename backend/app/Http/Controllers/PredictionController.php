<?php

namespace App\Http\Controllers;

use App\Models\Driver;
use App\Models\Prediction;
use App\Models\Race;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class PredictionController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'race_id' => ['required', 'integer', Rule::exists('races', 'id')],
            'p1_driver_id' => ['required', 'integer', Rule::exists('drivers', 'id')->where('active', true)],
            'p2_driver_id' => ['required', 'integer', Rule::exists('drivers', 'id')->where('active', true)],
            'p3_driver_id' => ['required', 'integer', Rule::exists('drivers', 'id')->where('active', true)],
            'dnf_count'    => ['required', 'integer', 'min:0', 'max:20'],
        ]);

        $picks = [$data['p1_driver_id'], $data['p2_driver_id'], $data['p3_driver_id']];
        if (count($picks) !== count(array_unique($picks))) {
            throw ValidationException::withMessages([
                'drivers' => 'P1, P2, and P3 must be three different drivers.',
            ]);
        }

        $race = Race::findOrFail($data['race_id']);

        if ($race->status !== Race::STATUS_UPCOMING) {
            throw ValidationException::withMessages([
                'race_id' => 'Predictions for this race are closed.',
            ]);
        }

        if ($race->predictions_close_at && $race->predictions_close_at->isPast()) {
            throw ValidationException::withMessages([
                'race_id' => 'Predictions for this race are closed.',
            ]);
        }

        $prediction = Prediction::updateOrCreate(
            [
                'user_id' => $request->user()->id,
                'race_id' => $race->id,
            ],
            [
                'p1_driver_id' => $data['p1_driver_id'],
                'p2_driver_id' => $data['p2_driver_id'],
                'p3_driver_id' => $data['p3_driver_id'],
                'dnf_count' => $data['dnf_count'],
                'submitted_at' => now(),
            ],
        );

        return response()->json(['data' => $this->present($prediction)], 201);
    }

    public function mine(Request $request): JsonResponse
    {
        $predictions = Prediction::query()
            ->where('user_id', $request->user()->id)
            ->with(['race.result'])
            ->get()
            ->map(fn (Prediction $p) => $this->present($p, includeRace: true))
            ->sortByDesc(fn ($row) => $row['race']['race_date'] ?? '')
            ->values();

        return response()->json(['data' => $predictions]);
    }

    public function forRace(Request $request, Race $race): JsonResponse
    {
        $userId = $request->user()->id;

        if ($race->status === Race::STATUS_UPCOMING) {
            $own = Prediction::where('user_id', $userId)
                ->where('race_id', $race->id)
                ->first();

            return response()->json([
                'data' => $own ? [$this->present($own, includeUser: true)] : [],
                'visibility' => 'self',
            ]);
        }

        $all = Prediction::query()
            ->where('race_id', $race->id)
            ->with(['user'])
            ->get()
            ->map(fn (Prediction $p) => $this->present($p, includeUser: true));

        return response()->json([
            'data' => $all,
            'visibility' => 'all',
        ]);
    }

    private function present(Prediction $p, bool $includeRace = false, bool $includeUser = false): array
    {
        $row = [
            'id' => $p->id,
            'race_id' => $p->race_id,
            'p1_driver_id' => $p->p1_driver_id,
            'p2_driver_id' => $p->p2_driver_id,
            'p3_driver_id' => $p->p3_driver_id,
            'dnf_count' => $p->dnf_count,
            'points' => $p->points,
            'submitted_at' => $p->submitted_at?->toIso8601String(),
        ];

        if ($includeUser && $p->relationLoaded('user') && $p->user) {
            $row['user'] = [
                'id' => $p->user->id,
                'name' => $p->user->name,
            ];
        }

        if ($includeRace && $p->relationLoaded('race') && $p->race) {
            $row['race'] = [
                'id' => $p->race->id,
                'season' => $p->race->season,
                'round' => $p->race->round,
                'name' => $p->race->name,
                'race_date' => $p->race->race_date?->toIso8601String(),
                'status' => $p->race->status,
                'result' => $p->race->status === Race::STATUS_FINISHED && $p->race->result ? [
                    'p1_driver_id' => $p->race->result->p1_driver_id,
                    'p2_driver_id' => $p->race->result->p2_driver_id,
                    'p3_driver_id' => $p->race->result->p3_driver_id,
                    'dnf_count' => $p->race->result->dnf_count,
                ] : null,
            ];
        }

        return $row;
    }
}
