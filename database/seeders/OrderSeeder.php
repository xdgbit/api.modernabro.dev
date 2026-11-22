<?php

namespace Database\Seeders;

use App\Models\_Order\Order;
use App\Models\_Order\OrderItem;
use App\Models\Product\Product;
use App\Models\User\UserCustomer;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class OrderSeeder extends Seeder
{
    private const ORDER_COUNT = 50;
    private const ITEM_COUNT = 100;

    public function run(): void
    {
        $customerIds = UserCustomer::query()->pluck('id')->all();
        $products = Product::query()
            ->where('status', 'active')
            ->get(['id', 'name', 'sku', 'price', 'sale_price']);

        if (empty($customerIds) || $products->isEmpty())
        {
            $this->command?->warn('مشتریان یا محصولات فعال خالی هستند. ابتدا UserCustomerSeeder و ProductSeeder را اجرا کنید.');

            return;
        }

        $orders = [];

        for ($i = 1; $i <= self::ORDER_COUNT; $i++)
        {
            $orders[] = Order::create([
                'code'              => sprintf('ORD-%06d', $i),
                'user_customer_id'  => fake()->randomElement($customerIds),
                'status'            => fake()->randomElement(['pending', 'paid', 'processing', 'shipped', 'delivered', 'cancelled', 'returned']),
                'payment_status'    => fake()->randomElement(['unpaid', 'paid', 'partially_paid', 'refunded']),
                'subtotal'          => 0,
                'discount_amount'   => 0,
                'shipping_cost'     => fake()->randomElement([0, 30000, 50000, 80000]),
                'total_amount'      => 0,
                'receiver_name'     => fake()->name(),
                'receiver_mobile'   => '09' . fake()->numerify('#########'),
                'shipping_address'  => fake()->address(),
                'postal_code'       => fake()->numerify('##########'),
                'tracking_code'     => null,
                'description'       => fake()->boolean(30) ? fake()->sentence() : null,
                'admin_note'        => null,
                'created_at'        => fake()->dateTimeBetween('-6 months', 'now'),
            ]);
        }

        // ۱۰۰ قلم: هر سفارش حداقل یک قلم، ۵۰ قلم باقی‌مانده تصادفی
        $distribution = array_fill(0, self::ORDER_COUNT, 1);

        for ($extra = 0; $extra < self::ITEM_COUNT - self::ORDER_COUNT; $extra++)
        {
            $distribution[fake()->numberBetween(0, self::ORDER_COUNT - 1)]++;
        }

        foreach ($orders as $index => $order)
        {
            $subtotal = 0;

            for ($n = 0; $n < $distribution[$index]; $n++)
            {
                $product = $products->random();

                $unitPrice = (int) $product->price;
                $salePrice = $product->sale_price !== null ? (int) $product->sale_price : null;
                $effectivePrice = $salePrice ?? $unitPrice;
                $quantity = fake()->numberBetween(1, 3);
                $totalPrice = $effectivePrice * $quantity;

                OrderItem::create([
                    'order_id'     => $order->id,
                    'product_id'   => $product->id,
                    'product_name' => $product->name,
                    'product_sku'  => $product->sku,
                    'unit_price'   => $unitPrice,
                    'sale_price'   => $salePrice,
                    'quantity'     => $quantity,
                    'total_price'  => $totalPrice,
                ]);

                $subtotal += $totalPrice;
            }

            $discount = fake()->boolean(20) ? (int) round($subtotal * 0.1 / 1000) * 1000 : 0;

            $order->update([
                'subtotal'        => $subtotal,
                'discount_amount' => $discount,
                'total_amount'    => $subtotal - $discount + $order->shipping_cost,
            ]);
        }
    }
}