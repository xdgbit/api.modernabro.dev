<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\_Order;
use App\Models\Product\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'name'                => ['required', 'string', 'min:3', 'max:100'],
            'phone'               => ['required', 'regex:/^09\d{9}$/'],
            'city'                => ['required', 'string', 'max:100'],
            'postal_code'         => ['required', 'digits:10'],
            'address'             => ['required', 'string', 'min:10', 'max:500'],
            'note'                => ['nullable', 'string', 'max:500'],
            'items'               => ['required', 'array', 'min:1', 'max:50'],
            'items.*.product_id'  => ['required', 'integer', 'exists:products,id'],
            'items.*.qty'         => ['required', 'integer', 'min:1', 'max:99'],
        ]);

        // ادغام ردیف‌های تکراری یک محصول
        $qtys = [];
        foreach ($data['items'] as $row) {
            $qtys[$row['product_id']] = ($qtys[$row['product_id']] ?? 0) + $row['qty'];
        }

        $order = DB::transaction(function () use ($data, $qtys) {
            $products = Product::whereIn('id', array_keys($qtys))->get();

            $total = 0;
            $lines = [];
            foreach ($products as $p) {
                $qty = min(99, $qtys[$p->id]);
                $total += $p->price * $qty;
                $lines[] = ['product_id' => $p->id, 'name' => $p->name, 'price' => $p->price, 'qty' => $qty];
            }

            $order = Order::create([
                'name'        => $data['name'],
                'phone'       => $data['phone'],
                'city'        => $data['city'],
                'address'     => $data['address'],
                'postal_code' => $data['postal_code'],
                'note'        => $data['note'] ?? null,
                'total'       => $total,
            ]);
            $order->items()->createMany($lines);

            return $order;
        });

        return response()->json(['data' => ['id' => $order->id, 'total' => $order->total]], 201);
    }
}