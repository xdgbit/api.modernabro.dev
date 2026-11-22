<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('personal_access_tokens', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->bigInteger('id')->unsigned()->autoIncrement();
            $table->string('tokenable_type', 255)->charset('utf8mb4')->collation('utf8mb4_unicode_ci');
            $table->bigInteger('tokenable_id')->unsigned();
            $table->text('name')->charset('utf8mb4')->collation('utf8mb4_unicode_ci');
            $table->string('token', 64)->charset('utf8mb4')->collation('utf8mb4_unicode_ci');
            $table->text('abilities')->nullable()->default(null)->charset('utf8mb4')->collation('utf8mb4_unicode_ci');
            $table->timestamp('last_used_at')->nullable()->default(null);
            $table->timestamp('expires_at')->nullable()->default(null);
            $table->timestamp('created_at')->nullable()->default(null);
            $table->timestamp('updated_at')->nullable()->default(null);

            $table->primary(['id']);

            $table->index(['expires_at'], 'personal_access_tokens_expires_at_index');

            $table->index(['tokenable_type', 'tokenable_id'], 'personal_access_tokens_tokenable_type_tokenable_id_index');

            $table->unique(['token'], 'personal_access_tokens_token_unique');
        });
    }

    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('personal_access_tokens');
        Schema::enableForeignKeyConstraints();
    }
};
