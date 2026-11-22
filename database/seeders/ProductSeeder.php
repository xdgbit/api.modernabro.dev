<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Product\Product;
use App\Models\Product\ProductCategory;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $brandIds = Brand::query()->pluck('id')->all();

        // فقط زیرمجموعه‌ها برای اتصال محصول استفاده می‌شوند
        $ProductCategoryIds = ProductCategory::query()
            ->whereNotNull('parent_id')
            ->pluck('id')
            ->all();

        if (empty($brandIds) || empty($ProductCategoryIds))
        {
            $this->command?->warn('برندها یا دسته‌بندی‌ها خالی هستند. ابتدا BrandSeeder و ProductCategorySeeder را اجرا کنید.');

            return;
        }

        $productTypes = [
            'هدفون بی‌سیم', 'ساعت هوشمند', 'گوشی موبایل', 'لپ‌تاپ', 'تبلت',
            'یخچال', 'ماشین لباسشویی', 'جاروبرقی', 'مایکروفر', 'اجاق گاز',
            'ست قابلمه', 'بالش طبی', 'کیف دستی', 'کفش ورزشی', 'عینک آفتابی',
            'چادر کوهنوردی', 'چمدان مسافرتی', 'دوچرخه شهری', 'پیراهن مردانه', 'مانتو زنانه',
        ];

        for ($i = 1; $i <= 200; $i++)
        {
            $price = fake()->numberBetween(5, 500) * 10000;

            $hasSale = fake()->boolean(30);

            $salePrice = $hasSale
                ? (int)(round($price * fake()->randomFloat(2, 0.7, 0.9) / 1000) * 1000)
                : null;

            $threshold = fake()->numberBetween(3, 15);

            // کمی محصولات ناموجود یا کم‌موجودی داشته باشند تا بخش نیازمند تأمین تست شود
            $stock = match (true)
            {
                $i % 12 === 0 => 0,
                $i % 7 === 0 => fake()->numberBetween(0, $threshold),
                default => fake()->numberBetween($threshold + 1, 120),
            };

            $type = fake()->randomElement($productTypes);
            $brandId = fake()->randomElement($brandIds);

            Product::updateOrCreate(
                ['sku' => sprintf('SKU-%05d', $i)],
                [
                    'name'                => $type . ' ' . $i,
                    'description'         => 'توضیحات تستی برای ' . $type,
                    'brand_id'            => $brandId,
                    'category_id'         => fake()->randomElement($ProductCategoryIds),
                    'price'               => $price,
                    'sale_price'          => $salePrice,
                    'stock'               => $stock,
                    'low_stock_threshold' => $threshold,
                    'status'              => fake()->boolean(90) ? 'active' : 'inactive',
                ]
            );
        }
    }
}