<?php

namespace Database\Seeders;

use App\Models\Race;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;

class RaceSeeder extends Seeder
{
    public function run(): void
    {
        // Three placeholder upcoming races so the UI has something to show.
        // Predictions close 1 hour before lights out.
        $now = CarbonImmutable::now();

        $races = [
            [
                'season'  => 2026,
                'round'   => 1,
                'name'    => 'Miami Grand Prix',
                'circuit' => 'Miami International Autodrome',
                'country' => 'United States',
                'race_date' => $now->addDays(7)->setTime(20, 0),
            ],
            [
                'season'  => 2026,
                'round'   => 2,
                'name'    => 'Emilia-Romagna Grand Prix',
                'circuit' => 'Autodromo Enzo e Dino Ferrari',
                'country' => 'Italy',
                'race_date' => $now->addDays(21)->setTime(15, 0),
            ],
            [
                'season'  => 2026,
                'round'   => 3,
                'name'    => 'Monaco Grand Prix',
                'circuit' => 'Circuit de Monaco',
                'country' => 'Monaco',
                'race_date' => $now->addDays(28)->setTime(15, 0),
            ],
        ];

        foreach ($races as $r) {
            Race::updateOrCreate(
                ['season' => $r['season'], 'round' => $r['round']],
                array_merge($r, [
                    'predictions_close_at' => $r['race_date']->subHour(),
                    'status' => Race::STATUS_UPCOMING,
                ]),
            );
        }
    }
}
