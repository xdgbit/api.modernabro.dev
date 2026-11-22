<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_experts', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->bigInteger('id')->unsigned()->autoIncrement();
            $table->string('username', 255)->nullable()->default(null)->charset('utf8mb4')->collation('utf8mb4_unicode_ci');
            $table->string('first_name', 255)->charset('utf8mb4')->collation('utf8mb4_unicode_ci');
            $table->string('last_name', 255)->charset('utf8mb4')->collation('utf8mb4_unicode_ci');
            $table->string('mobile_number', 20)->charset('utf8mb4')->collation('utf8mb4_unicode_ci');
            $table->string('national_code', 255)->nullable()->default(null)->charset('utf8mb4')->collation('utf8mb4_unicode_ci');
            $table->text('password')->nullable()->default(null)->charset('utf8mb4')->collation('utf8mb4_unicode_ci');
            $table->string('private_pusher_channel', 255)->nullable()->default(null)->charset('utf8mb4')->collation('utf8mb4_unicode_ci');
            $table->string('email', 255)->nullable()->default(null)->charset('utf8mb4')->collation('utf8mb4_unicode_ci');
            $table->boolean('status')->default(1);
            $table->boolean('is_active')->nullable()->default(null);
            $table->boolean('is_super_admin')->nullable()->default(null);
            $table->bigInteger('submitted_by')->nullable()->default(null);
            $table->timestamp('submitted_at')->nullable()->default(null);
            $table->timestamp('created_at')->nullable()->default(null);
            $table->timestamp('updated_at')->nullable()->default(null);
            $table->timestamp('deleted_at')->nullable()->default(null);

            $table->primary(['id']);

            $table->unique(['mobile_number'], 'user_experts_mobile_number_unique');

            $table->unique(['username'], 'user_experts_username_unique');
        });
    }

    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('user_experts');
        Schema::enableForeignKeyConstraints();
    }
};
