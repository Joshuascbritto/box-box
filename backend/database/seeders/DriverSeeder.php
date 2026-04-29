<?php

namespace Database\Seeders;

use App\Models\Driver;
use Illuminate\Database\Seeder;

class DriverSeeder extends Seeder
{
    public function run(): void
    {
        // Current grid (active=true) plus drivers from the last three seasons
        // who are no longer racing (active=false). The team field reflects
        // their CURRENT placement; the Archive page shows historical results
        // by code/name only and never displays a team for historical context.
        $drivers = [
            // Red Bull Racing
            ['name' => 'Max Verstappen',    'code' => 'VER', 'team' => 'Red Bull Racing',     'number' => 1],
            ['name' => 'Yuki Tsunoda',      'code' => 'TSU', 'team' => 'Red Bull Racing',     'number' => 22],

            // McLaren
            ['name' => 'Lando Norris',      'code' => 'NOR', 'team' => 'McLaren',             'number' => 4],
            ['name' => 'Oscar Piastri',     'code' => 'PIA', 'team' => 'McLaren',             'number' => 81],

            // Ferrari
            ['name' => 'Charles Leclerc',   'code' => 'LEC', 'team' => 'Ferrari',             'number' => 16],
            ['name' => 'Lewis Hamilton',    'code' => 'HAM', 'team' => 'Ferrari',             'number' => 44],

            // Mercedes
            ['name' => 'George Russell',          'code' => 'RUS', 'team' => 'Mercedes',      'number' => 63],
            ['name' => 'Andrea Kimi Antonelli',   'code' => 'ANT', 'team' => 'Mercedes',      'number' => 12],

            // Aston Martin
            ['name' => 'Fernando Alonso',   'code' => 'ALO', 'team' => 'Aston Martin',        'number' => 14],
            ['name' => 'Lance Stroll',      'code' => 'STR', 'team' => 'Aston Martin',        'number' => 18],

            // Alpine
            ['name' => 'Pierre Gasly',      'code' => 'GAS', 'team' => 'Alpine',              'number' => 10],
            ['name' => 'Franco Colapinto',  'code' => 'COL', 'team' => 'Alpine',              'number' => 43],

            // Williams
            ['name' => 'Alexander Albon',   'code' => 'ALB', 'team' => 'Williams',            'number' => 23],
            ['name' => 'Carlos Sainz',      'code' => 'SAI', 'team' => 'Williams',            'number' => 55],

            // Racing Bulls (RB / VCARB)
            ['name' => 'Liam Lawson',       'code' => 'LAW', 'team' => 'Racing Bulls',        'number' => 30],
            ['name' => 'Isack Hadjar',      'code' => 'HAD', 'team' => 'Racing Bulls',        'number' => 6],

            // Kick Sauber
            ['name' => 'Nico Hulkenberg',   'code' => 'HUL', 'team' => 'Kick Sauber',         'number' => 27],
            ['name' => 'Gabriel Bortoleto', 'code' => 'BOR', 'team' => 'Kick Sauber',         'number' => 5],

            // Haas
            ['name' => 'Esteban Ocon',      'code' => 'OCO', 'team' => 'Haas',                'number' => 31],
            ['name' => 'Oliver Bearman',    'code' => 'BEA', 'team' => 'Haas',                'number' => 87],
        ];

        foreach ($drivers as $d) {
            Driver::updateOrCreate(
                ['code' => $d['code']],
                array_merge($d, ['active' => true]),
            );
        }

        // Drivers who appear in the historical archive (2023–2025) but
        // are no longer on the current grid. Team reflects their last seat.
        $historical = [
            ['name' => 'Sergio Perez',        'code' => 'PER', 'team' => 'Red Bull Racing', 'number' => 11],
            ['name' => 'Daniel Ricciardo',    'code' => 'RIC', 'team' => 'Racing Bulls',    'number' => 3],
            ['name' => 'Logan Sargeant',      'code' => 'SAR', 'team' => 'Williams',         'number' => 2],
            ['name' => 'Kevin Magnussen',     'code' => 'MAG', 'team' => 'Haas',             'number' => 20],
            ['name' => 'Valtteri Bottas',     'code' => 'BOT', 'team' => 'Kick Sauber',      'number' => 77],
            ['name' => 'Zhou Guanyu',         'code' => 'ZHO', 'team' => 'Kick Sauber',      'number' => 24],
        ];

        foreach ($historical as $d) {
            Driver::updateOrCreate(
                ['code' => $d['code']],
                array_merge($d, ['active' => false]),
            );
        }
    }
}
