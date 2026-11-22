<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class IranLocationsSeeder extends Seeder
{
    private string $baseUrl =
        'https://raw.githubusercontent.com/sajaddp/list-of-cities-in-Iran/main/dist/json/';

    public function run(): void
    {
        $provinces = $this->fetchJson('provinces.json');
        $cities = $this->fetchJson('cities.json');

        if (count($provinces) !== 31) {
            throw new RuntimeException('Province data is incomplete.');
        }

        DB::transaction(function () use ($provinces, $cities) {

            $now = now();

            // Insert provinces
            foreach (array_chunk($provinces, 500) as $chunk) {
                $rows = [];

                foreach ($chunk as $province) {
                    $rows[] = [
                        'id' => $province['id'],
                        'name' => $province['name'],
                        'slug' => $province['slug'] ?? null,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }

                DB::table('provinces')->upsert(
                    $rows,
                    ['id'],
                    ['name', 'slug', 'updated_at']
                );
            }

            // Insert cities
            foreach (array_chunk($cities, 500) as $chunk) {
                $rows = [];

                foreach ($chunk as $city) {
                    $rows[] = [
                        'id' => $city['id'],
                        'name' => $city['name'],
                        'slug' => $city['slug'] ?? null,
                        'province_id' => $city['province_id'],
                        'county_id' => $city['county_id'] ?? null,
                        'district_id' => $city['district_id'] ?? null,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }

                DB::table('cities')->upsert(
                    $rows,
                    ['id'],
                    [
                        'name',
                        'slug',
                        'province_id',
                        'county_id',
                        'district_id',
                        'updated_at'
                    ]
                );
            }
        });

        $this->command->info('Iran provinces and cities imported successfully.');
        $this->command->info('Provinces: ' . count($provinces));
        $this->command->info('Cities: ' . count($cities));
    }

    private function fetchJson(string $file): array
    {
        $response = Http::timeout(120)
            ->retry(3, 1000)
            ->get($this->baseUrl . $file);

        if (!$response->successful()) {
            throw new RuntimeException(
                "Failed to download {$file}"
            );
        }

        $data = $response->json();

        if (!is_array($data) || !array_is_list($data)) {
            throw new RuntimeException(
                "Invalid JSON structure in {$file}"
            );
        }

        return $data;
    }
}
