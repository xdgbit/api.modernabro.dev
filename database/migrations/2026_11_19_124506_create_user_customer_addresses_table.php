<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('user_customer_addresses', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_customer_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('title'); // عنوان آدرس
            $table->string('receiver_name'); // نام گیرنده
            $table->string('receiver_mobile', 15); // موبایل گیرنده
            $table->string('postal_code', 20); // کد پستی
            $table->string('province'); // استان
            $table->string('city'); // شهر
            $table->text('address'); // نشانی کامل

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_customer_addresses');
    }
};
