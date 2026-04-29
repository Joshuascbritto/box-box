<?php

namespace Database\Seeders;

use App\Models\Driver;
use App\Models\Race;
use App\Models\RaceResult;
use Illuminate\Database\Seeder;

/**
 * Seeds the last three completed seasons (2023, 2024, 2025) with full
 * calendars and their podiums. Used by the Archive view.
 *
 *   2023 — 22 rounds, podiums based on actual results.
 *   2024 — 24 rounds, podiums based on actual results.
 *   2025 — 24 rounds, podiums are best-effort placeholders. Sourced from
 *          general training-data knowledge of the season; the project owner
 *          should overwrite with authoritative results when convenient.
 *
 * Format per row: [round, name, circuit, country, YYYY-MM-DD, [P1, P2, P3], dnf]
 */
class HistoricalRaceSeeder extends Seeder
{
    public function run(): void
    {
        $byCode = Driver::pluck('id', 'code')->all();

        $seasons = [
            2023 => $this->season2023(),
            2024 => $this->season2024(),
            2025 => $this->season2025(),
        ];

        foreach ($seasons as $season => $races) {
            foreach ($races as $r) {
                [$round, $name, $circuit, $country, $date, $podium, $dnf] = $r;
                $raceDate = $date.' 14:00:00';

                $race = Race::updateOrCreate(
                    ['season' => $season, 'round' => $round],
                    [
                        'name' => $name,
                        'circuit' => $circuit,
                        'country' => $country,
                        'race_date' => $raceDate,
                        'predictions_close_at' => $date.' 13:00:00',
                        'status' => Race::STATUS_FINISHED,
                    ],
                );

                [$p1, $p2, $p3] = $podium;
                if (! isset($byCode[$p1], $byCode[$p2], $byCode[$p3])) {
                    continue;
                }

                RaceResult::updateOrCreate(
                    ['race_id' => $race->id],
                    [
                        'p1_driver_id' => $byCode[$p1],
                        'p2_driver_id' => $byCode[$p2],
                        'p3_driver_id' => $byCode[$p3],
                        'dnf_count' => $dnf,
                        'recorded_at' => $raceDate,
                    ],
                );
            }
        }
    }

    private function season2023(): array
    {
        return [
            [1,  'Bahrain Grand Prix',         'Bahrain International Circuit',   'Bahrain',                 '2023-03-05', ['VER','PER','ALO'], 4],
            [2,  'Saudi Arabian Grand Prix',   'Jeddah Corniche Circuit',         'Saudi Arabia',            '2023-03-19', ['PER','VER','ALO'], 1],
            [3,  'Australian Grand Prix',      'Albert Park Circuit',             'Australia',               '2023-04-02', ['VER','HAM','ALO'], 6],
            [4,  'Azerbaijan Grand Prix',      'Baku City Circuit',               'Azerbaijan',              '2023-04-30', ['PER','VER','LEC'], 1],
            [5,  'Miami Grand Prix',           'Miami International Autodrome',   'United States',           '2023-05-07', ['VER','PER','ALO'], 1],
            [6,  'Monaco Grand Prix',          'Circuit de Monaco',               'Monaco',                  '2023-05-28', ['VER','ALO','OCO'], 1],
            [7,  'Spanish Grand Prix',         'Circuit de Barcelona-Catalunya',  'Spain',                   '2023-06-04', ['VER','HAM','RUS'], 2],
            [8,  'Canadian Grand Prix',        'Circuit Gilles Villeneuve',       'Canada',                  '2023-06-18', ['VER','ALO','HAM'], 2],
            [9,  'Austrian Grand Prix',        'Red Bull Ring',                   'Austria',                 '2023-07-02', ['VER','LEC','PER'], 1],
            [10, 'British Grand Prix',         'Silverstone Circuit',             'United Kingdom',          '2023-07-09', ['VER','NOR','HAM'], 2],
            [11, 'Hungarian Grand Prix',       'Hungaroring',                     'Hungary',                 '2023-07-23', ['VER','NOR','PER'], 2],
            [12, 'Belgian Grand Prix',         'Circuit de Spa-Francorchamps',    'Belgium',                 '2023-07-30', ['VER','PER','LEC'], 1],
            [13, 'Dutch Grand Prix',           'Circuit Zandvoort',               'Netherlands',             '2023-08-27', ['VER','ALO','GAS'], 5],
            [14, 'Italian Grand Prix',         'Autodromo Nazionale Monza',       'Italy',                   '2023-09-03', ['VER','PER','SAI'], 4],
            [15, 'Singapore Grand Prix',       'Marina Bay Street Circuit',       'Singapore',               '2023-09-17', ['SAI','NOR','HAM'], 4],
            [16, 'Japanese Grand Prix',        'Suzuka Circuit',                  'Japan',                   '2023-09-24', ['VER','NOR','PIA'], 4],
            [17, 'Qatar Grand Prix',           'Lusail International Circuit',    'Qatar',                   '2023-10-08', ['VER','NOR','HAM'], 3],
            [18, 'United States Grand Prix',   'Circuit of the Americas',         'United States',           '2023-10-22', ['VER','NOR','SAI'], 3],
            [19, 'Mexico City Grand Prix',     'Autódromo Hermanos Rodríguez',    'Mexico',                  '2023-10-29', ['VER','HAM','LEC'], 4],
            [20, 'São Paulo Grand Prix',       'Autódromo José Carlos Pace',      'Brazil',                  '2023-11-05', ['VER','NOR','ALO'], 4],
            [21, 'Las Vegas Grand Prix',       'Las Vegas Strip Circuit',         'United States',           '2023-11-18', ['VER','LEC','PER'], 2],
            [22, 'Abu Dhabi Grand Prix',       'Yas Marina Circuit',              'United Arab Emirates',    '2023-11-26', ['VER','LEC','RUS'], 3],
        ];
    }

    private function season2024(): array
    {
        return [
            [1,  'Bahrain Grand Prix',         'Bahrain International Circuit',   'Bahrain',                 '2024-03-02', ['VER','PER','SAI'], 1],
            [2,  'Saudi Arabian Grand Prix',   'Jeddah Corniche Circuit',         'Saudi Arabia',            '2024-03-09', ['VER','PER','LEC'], 2],
            [3,  'Australian Grand Prix',      'Albert Park Circuit',             'Australia',               '2024-03-24', ['SAI','LEC','NOR'], 4],
            [4,  'Japanese Grand Prix',        'Suzuka Circuit',                  'Japan',                   '2024-04-07', ['VER','PER','SAI'], 5],
            [5,  'Chinese Grand Prix',         'Shanghai International Circuit',  'China',                   '2024-04-21', ['VER','NOR','PER'], 1],
            [6,  'Miami Grand Prix',           'Miami International Autodrome',   'United States',           '2024-05-05', ['NOR','VER','LEC'], 0],
            [7,  'Emilia-Romagna Grand Prix',  'Autodromo Enzo e Dino Ferrari',   'Italy',                   '2024-05-19', ['VER','NOR','LEC'], 1],
            [8,  'Monaco Grand Prix',          'Circuit de Monaco',               'Monaco',                  '2024-05-26', ['LEC','PIA','SAI'], 4],
            [9,  'Canadian Grand Prix',        'Circuit Gilles Villeneuve',       'Canada',                  '2024-06-09', ['VER','NOR','RUS'], 5],
            [10, 'Spanish Grand Prix',         'Circuit de Barcelona-Catalunya',  'Spain',                   '2024-06-23', ['VER','NOR','HAM'], 1],
            [11, 'Austrian Grand Prix',        'Red Bull Ring',                   'Austria',                 '2024-06-30', ['RUS','PIA','SAI'], 3],
            [12, 'British Grand Prix',         'Silverstone Circuit',             'United Kingdom',          '2024-07-07', ['HAM','VER','NOR'], 2],
            [13, 'Hungarian Grand Prix',       'Hungaroring',                     'Hungary',                 '2024-07-21', ['PIA','NOR','HAM'], 2],
            [14, 'Belgian Grand Prix',         'Circuit de Spa-Francorchamps',    'Belgium',                 '2024-07-28', ['HAM','PIA','LEC'], 1],
            [15, 'Dutch Grand Prix',           'Circuit Zandvoort',               'Netherlands',             '2024-08-25', ['NOR','VER','LEC'], 2],
            [16, 'Italian Grand Prix',         'Autodromo Nazionale Monza',       'Italy',                   '2024-09-01', ['LEC','PIA','NOR'], 1],
            [17, 'Azerbaijan Grand Prix',      'Baku City Circuit',               'Azerbaijan',              '2024-09-15', ['PIA','LEC','RUS'], 4],
            [18, 'Singapore Grand Prix',       'Marina Bay Street Circuit',       'Singapore',               '2024-09-22', ['NOR','VER','PIA'], 3],
            [19, 'United States Grand Prix',   'Circuit of the Americas',         'United States',           '2024-10-20', ['LEC','SAI','VER'], 3],
            [20, 'Mexico City Grand Prix',     'Autódromo Hermanos Rodríguez',    'Mexico',                  '2024-10-27', ['SAI','NOR','LEC'], 3],
            [21, 'São Paulo Grand Prix',       'Autódromo José Carlos Pace',      'Brazil',                  '2024-11-03', ['VER','OCO','GAS'], 6],
            [22, 'Las Vegas Grand Prix',       'Las Vegas Strip Circuit',         'United States',           '2024-11-23', ['RUS','HAM','SAI'], 2],
            [23, 'Qatar Grand Prix',           'Lusail International Circuit',    'Qatar',                   '2024-12-01', ['VER','LEC','PIA'], 4],
            [24, 'Abu Dhabi Grand Prix',       'Yas Marina Circuit',              'United Arab Emirates',    '2024-12-08', ['NOR','SAI','LEC'], 1],
        ];
    }

    private function season2025(): array
    {
        // Best-effort placeholders — overwrite with authoritative results
        // when convenient. Driver-team pairings for 2025 reflect the post-
        // shuffle grid (Hamilton at Ferrari, Antonelli at Mercedes, etc.)
        // but the data table tracks drivers, not historical team mappings.
        return [
            [1,  'Australian Grand Prix',      'Albert Park Circuit',             'Australia',               '2025-03-16', ['NOR','VER','RUS'], 4],
            [2,  'Chinese Grand Prix',         'Shanghai International Circuit',  'China',                   '2025-03-23', ['PIA','NOR','RUS'], 2],
            [3,  'Japanese Grand Prix',        'Suzuka Circuit',                  'Japan',                   '2025-04-06', ['VER','NOR','PIA'], 1],
            [4,  'Bahrain Grand Prix',         'Bahrain International Circuit',   'Bahrain',                 '2025-04-13', ['PIA','NOR','RUS'], 2],
            [5,  'Saudi Arabian Grand Prix',   'Jeddah Corniche Circuit',         'Saudi Arabia',            '2025-04-20', ['PIA','VER','LEC'], 1],
            [6,  'Miami Grand Prix',           'Miami International Autodrome',   'United States',           '2025-05-04', ['PIA','NOR','RUS'], 1],
            [7,  'Emilia-Romagna Grand Prix',  'Autodromo Enzo e Dino Ferrari',   'Italy',                   '2025-05-18', ['VER','NOR','PIA'], 2],
            [8,  'Monaco Grand Prix',          'Circuit de Monaco',               'Monaco',                  '2025-05-25', ['NOR','LEC','PIA'], 1],
            [9,  'Spanish Grand Prix',         'Circuit de Barcelona-Catalunya',  'Spain',                   '2025-06-01', ['PIA','NOR','LEC'], 2],
            [10, 'Canadian Grand Prix',        'Circuit Gilles Villeneuve',       'Canada',                  '2025-06-15', ['RUS','VER','ANT'], 4],
            [11, 'Austrian Grand Prix',        'Red Bull Ring',                   'Austria',                 '2025-06-29', ['NOR','PIA','LEC'], 2],
            [12, 'British Grand Prix',         'Silverstone Circuit',             'United Kingdom',          '2025-07-06', ['NOR','PIA','HUL'], 4],
            [13, 'Belgian Grand Prix',         'Circuit de Spa-Francorchamps',    'Belgium',                 '2025-07-27', ['PIA','NOR','LEC'], 1],
            [14, 'Hungarian Grand Prix',       'Hungaroring',                     'Hungary',                 '2025-08-03', ['NOR','PIA','RUS'], 3],
            [15, 'Dutch Grand Prix',           'Circuit Zandvoort',               'Netherlands',             '2025-08-31', ['PIA','VER','HAD'], 5],
            [16, 'Italian Grand Prix',         'Autodromo Nazionale Monza',       'Italy',                   '2025-09-07', ['VER','NOR','PIA'], 1],
            [17, 'Azerbaijan Grand Prix',      'Baku City Circuit',               'Azerbaijan',              '2025-09-21', ['VER','RUS','SAI'], 5],
            [18, 'Singapore Grand Prix',       'Marina Bay Street Circuit',       'Singapore',               '2025-10-05', ['RUS','VER','NOR'], 2],
            [19, 'United States Grand Prix',   'Circuit of the Americas',         'United States',           '2025-10-19', ['VER','NOR','LEC'], 3],
            [20, 'Mexico City Grand Prix',     'Autódromo Hermanos Rodríguez',    'Mexico',                  '2025-10-26', ['NOR','LEC','VER'], 3],
            [21, 'São Paulo Grand Prix',       'Autódromo José Carlos Pace',      'Brazil',                  '2025-11-09', ['NOR','PIA','LEC'], 5],
            [22, 'Las Vegas Grand Prix',       'Las Vegas Strip Circuit',         'United States',           '2025-11-22', ['VER','RUS','NOR'], 3],
            [23, 'Qatar Grand Prix',           'Lusail International Circuit',    'Qatar',                   '2025-11-30', ['PIA','VER','NOR'], 2],
            [24, 'Abu Dhabi Grand Prix',       'Yas Marina Circuit',              'United Arab Emirates',    '2025-12-07', ['NOR','VER','LEC'], 2],
        ];
    }
}
