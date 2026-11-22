<?php

namespace Database\Seeders;

use App\Models\Product\ProductCategory;
use Illuminate\Database\Seeder;

class ProductCategorySeeder extends Seeder
{
    public function run(): void
    {
        $tree = [
            'لوازم برقی' => [
                'slug' => 'home-appliances',
                'children' => [
                    ['title' => 'یخچال و فریزر', 'slug' => 'refrigerators'],
                    ['title' => 'ماشین لباسشویی', 'slug' => 'washing-machines'],
                    ['title' => 'جاروبرقی', 'slug' => 'vacuum-cleaners'],
                    ['title' => 'مایکروفر', 'slug' => 'microwaves'],
                    ['title' => 'اجاق گاز', 'slug' => 'stoves'],
                ],
            ],
            'الکترونیک' => [
                'slug' => 'electronics',
                'children' => [
                    ['title' => 'گوشی موبایل', 'slug' => 'mobile-phones'],
                    ['title' => 'لپ‌تاپ', 'slug' => 'laptops'],
                    ['title' => 'تبلت', 'slug' => 'tablets'],
                    ['title' => 'هدفون و هندزفری', 'slug' => 'headphones'],
                    ['title' => 'ساعت هوشمند', 'slug' => 'smartwatches'],
                ],
            ],
            'لوازم خانگی' => [
                'slug' => 'household',
                'children' => [
                    ['title' => 'ظروف آشپزخانه', 'slug' => 'kitchenware'],
                    ['title' => 'لوازم پذیرایی', 'slug' => 'serveware'],
                    ['title' => 'سرویس خواب', 'slug' => 'bedding'],
                    ['title' => 'ابزار و دکوراسیون', 'slug' => 'decor'],
                    ['title' => 'لوازم نظافت', 'slug' => 'cleaning'],
                ],
            ],
            'مد و پوشاک' => [
                'slug' => 'fashion',
                'children' => [
                    ['title' => 'پوشاک مردانه', 'slug' => 'mens-clothing'],
                    ['title' => 'پوشاک زنانه', 'slug' => 'womens-clothing'],
                    ['title' => 'کفش', 'slug' => 'shoes'],
                    ['title' => 'کیف و کوله', 'slug' => 'bags'],
                    ['title' => 'عینک آفتابی', 'slug' => 'sunglasses'],
                ],
            ],
            'ورزش و سفر' => [
                'slug' => 'sports-travel',
                'children' => [
                    ['title' => 'لوازم ورزشی', 'slug' => 'sports-equipment'],
                    ['title' => 'پوشاک ورزشی', 'slug' => 'sportswear'],
                    ['title' => 'چادر و کمپینگ', 'slug' => 'camping'],
                    ['title' => 'چمدان', 'slug' => 'luggage'],
                    ['title' => 'دوچرخه', 'slug' => 'bicycles'],
                ],
            ],
        ];

        foreach ($tree as $parentTitle => $parentData)
        {
            $parent = ProductCategory::updateOrCreate(
                ['slug' => $parentData['slug']],
                [
                    'parent_id'   => null,
                    'title'       => $parentTitle,
                    'description' => 'دسته‌بندی اصلی ' . $parentTitle,
                    'image'       => null,
                    'sort_order'  => 0,
                ]
            );

            foreach ($parentData['children'] as $index => $child)
            {
                ProductCategory::updateOrCreate(
                    ['slug' => $child['slug']],
                    [
                        'parent_id'   => $parent->id,
                        'title'       => $child['title'],
                        'description' => 'زیرمجموعه ' . $parentTitle,
                        'image'       => null,
                        'sort_order'  => $index + 1,
                    ]
                );
            }
        }
    }
}