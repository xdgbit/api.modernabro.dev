<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sessions', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->string('id', 255)->charset('utf8mb4')->collation('utf8mb4_unicode_ci');
            $table->bigInteger('user_id')->unsigned()->nullable()->default(null);
            $table->string('ip_address', 45)->nullable()->default(null)->charset('utf8mb4')->collation('utf8mb4_unicode_ci');
            $table->text('user_agent')->nullable()->default(null)->charset('utf8mb4')->collation('utf8mb4_unicode_ci');
            $table->longText('payload')->charset('utf8mb4')->collation('utf8mb4_unicode_ci');
            $table->integer('last_activity');

            $table->primary(['id']);

            $table->index(['last_activity'], 'sessions_last_activity_index');

            $table->index(['user_id'], 'sessions_user_id_index');
        });
    }

    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('sessions');
        Schema::enableForeignKeyConstraints();
    }
};
