<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_customers', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->bigInteger('id')->unsigned()->autoIncrement();
            $table->string('first_name', 255)->nullable()->default(null)->charset('utf8mb4')->collation('utf8mb4_unicode_ci');
            $table->string('last_name', 255)->nullable()->default(null)->charset('utf8mb4')->collation('utf8mb4_unicode_ci');
            $table->string('mobile_number', 20)->nullable()->default(null)->charset('utf8mb4')->collation('utf8mb4_unicode_ci');
            $table->string('email', 255)->nullable()->default(null)->charset('utf8mb4')->collation('utf8mb4_unicode_ci');
            $table->string('password', 255)->nullable()->default(null)->charset('utf8mb4')->collation('utf8mb4_unicode_ci');
            $table->boolean('is_active')->nullable()->default(1);
            $table->string('remember_token', 100)->nullable()->default(null)->charset('utf8mb4')->collation('utf8mb4_unicode_ci');
            $table->timestamp('mobile_verified_at')->nullable()->default(null);
            $table->string('private_pusher_channel', 255)->nullable()->default(null)->charset('utf8mb4')->collation('utf8mb4_unicode_ci');
            $table->timestamp('private_pusher_channel_last_update')->nullable()->default(null);
            $table->timestamp('email_verified_at')->nullable()->default(null);
            $table->timestamp('last_login_at')->nullable()->default(null);
            $table->timestamp('created_at')->nullable()->default(null);
            $table->timestamp('updated_at')->nullable()->default(null);

            $table->primary(['id']);

            $table->unique(['email'], 'user_customers_email_unique');

            $table->unique(['mobile_number'], 'user_customers_mobile_number_unique');
        });
    }

    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('user_customers');
        Schema::enableForeignKeyConstraints();
    }
};
