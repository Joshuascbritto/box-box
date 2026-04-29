<?php

namespace App\Http\Controllers;

use App\Services\ScoringService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class LeaderboardController extends Controller
{
    public function index(): JsonResponse
    {
        $rows = DB::table('predictions')
            ->join('users', 'users.id', '=', 'predictions.user_id')
            ->join('races', 'races.id', '=', 'predictions.race_id')
            ->where('races.status', 'finished')
            ->groupBy('users.id', 'users.name')
            ->select([
                'users.id as user_id',
                'users.name as user_name',
                DB::raw('COALESCE(SUM(predictions.points), 0) as total_points'),
                DB::raw('COUNT(predictions.id) as races_predicted'),
                DB::raw('SUM(CASE WHEN predictions.points >= '.(ScoringService::POINTS_P1_EXACT + ScoringService::POINTS_P2_EXACT + ScoringService::POINTS_P3_EXACT + ScoringService::POINTS_PERFECT_PODIUM_BONUS).' THEN 1 ELSE 0 END) as perfect_podiums'),
            ])
            ->orderByDesc('total_points')
            ->limit(50)
            ->get();

        $payload = $rows->values()->map(fn ($r, $i) => [
            'rank' => $i + 1,
            'user' => ['id' => (int) $r->user_id, 'name' => $r->user_name],
            'total_points' => (int) $r->total_points,
            'races_predicted' => (int) $r->races_predicted,
            'perfect_podiums' => (int) $r->perfect_podiums,
        ]);

        return response()->json(['data' => $payload]);
    }
}
