<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_batches', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->string('id', 255)->charset('utf8mb4')->collation('utf8mb4_unicode_ci');
            $table->string('name', 255)->charset('utf8mb4')->collation('utf8mb4_unicode_ci');
            $table->integer('total_jobs');
            $table->integer('pending_jobs');
            $table->integer('failed_jobs');
            $table->longText('failed_job_ids')->charset('utf8mb4')->collation('utf8mb4_unicode_ci');
            $table->mediumText('options')->nullable()->default(null)->charset('utf8mb4')->collation('utf8mb4_unicode_ci');
            $table->integer('cancelled_at')->nullable()->default(null);
            $table->integer('created_at');
            $table->integer('finished_at')->nullable()->default(null);

            $table->primary(['id']);
        });
    }

    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('job_batches');
        Schema::enableForeignKeyConstraints();
    }
};
