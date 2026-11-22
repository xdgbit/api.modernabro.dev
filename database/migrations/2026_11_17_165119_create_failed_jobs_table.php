<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('failed_jobs', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->bigInteger('id')->unsigned()->autoIncrement();
            $table->string('uuid', 255)->charset('utf8mb4')->collation('utf8mb4_unicode_ci');
            $table->string('connection', 255)->charset('utf8mb4')->collation('utf8mb4_unicode_ci');
            $table->string('queue', 255)->charset('utf8mb4')->collation('utf8mb4_unicode_ci');
            $table->longText('payload')->charset('utf8mb4')->collation('utf8mb4_unicode_ci');
            $table->longText('exception')->charset('utf8mb4')->collation('utf8mb4_unicode_ci');
            $table->timestamp('failed_at')->useCurrent();
            $table->primary(['id']);

            $table->index(['connection', 'queue', 'failed_at'], 'failed_jobs_connection_queue_failed_at_index');

            $table->unique(['uuid'], 'failed_jobs_uuid_unique');
        });
    }

    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('failed_jobs');
        Schema::enableForeignKeyConstraints();
    }
};
