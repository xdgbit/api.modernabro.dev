<?php

namespace Database\Seeders;

use App\Models\Brand;
use Illuminate\Database\Seeder;

class BrandSeeder extends Seeder
{
    public function run(): void
    {
        $brands = [
            ['title' => 'آریا تک',      'slug' => 'aria-tak'],
            ['title' => 'نوین گستر',    'slug' => 'novin-gostar'],
            ['title' => 'پارس الکترونیک', 'slug' => 'pars-electronic'],
            ['title' => 'سپهر مدرن',    'slug' => 'sepehr-modern'],
            ['title' => 'البرز پلاس',   'slug' => 'alborz-plus'],
            ['title' => 'دنا کالا',     'slug' => 'dana-kala'],
            ['title' => 'رایان صنعت',   'slug' => 'rayan-sanat'],
            ['title' => 'ماهور',        'slug' => 'mahvar'],
            ['title' => 'توسعه پویا',   'slug' => 'tose-pooya'],
            ['title' => 'فن‌آوران شرق', 'slug' => 'fanavaran-shargh'],
        ];

        foreach ($brands as $brand)
        {
            Brand::updateOrCreate(
                ['slug' => $brand['slug']],
                [
                    'title'       => $brand['title'],
                    'description' => 'توضیحات برند ' . $brand['title'],
                    'logo'        => null,
                ]
            );
        }
    }
}