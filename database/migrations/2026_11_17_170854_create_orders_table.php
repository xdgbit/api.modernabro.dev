<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();

            $table->string('code', 50)->unique();

            $table->foreignId('user_customer_id')
                ->nullable()
                ->constrained('user_customers')
                ->nullOnDelete();

            $table->string('status', 30)->default('pending');
            $table->string('payment_status', 30)->default('unpaid');

           /* $table->foreignId('payment_method_id')
                ->nullable()
                ->constrained('payment_methods')
                ->nullOnDelete();

            $table->foreignId('shipping_method_id')
                ->nullable()
                ->constrained('shipping_methods')
                ->nullOnDelete();*/
/*
            $table->foreignId('discount_code_id')
                ->nullable()
                ->constrained('discount_codes')
                ->nullOnDelete();*/

            // مبالغ به تومان و عدد صحیح
            $table->unsignedBigInteger('subtotal')->default(0);
            $table->unsignedBigInteger('discount_amount')->default(0);
            $table->unsignedBigInteger('shipping_cost')->default(0);
            $table->unsignedBigInteger('total_amount')->default(0);

            $table->string('receiver_name');
            $table->string('receiver_mobile', 20);
            $table->text('shipping_address');
            $table->string('postal_code', 20)->nullable();
            $table->string('tracking_code', 100)->nullable();

            $table->text('description')->nullable();
            $table->text('admin_note')->nullable();

            $table->timestamp('paid_at')->nullable();
            $table->timestamp('shipped_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'payment_status']);
            $table->index('user_customer_id');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};