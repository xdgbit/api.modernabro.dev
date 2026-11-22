<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->string('email', 255)->charset('utf8mb4')->collation('utf8mb4_unicode_ci');
            $table->string('token', 255)->charset('utf8mb4')->collation('utf8mb4_unicode_ci');
            $table->timestamp('created_at')->nullable()->default(null);

            $table->primary(['email']);
        });
    }

    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('password_reset_tokens');
        Schema::enableForeignKeyConstraints();
    }
};
