<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jobs', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->bigInteger('id')->unsigned()->autoIncrement();
            $table->string('queue', 255)->charset('utf8mb4')->collation('utf8mb4_unicode_ci');
            $table->longText('payload')->charset('utf8mb4')->collation('utf8mb4_unicode_ci');
            $table->smallInteger('attempts')->unsigned();
            $table->integer('reserved_at')->unsigned()->nullable()->default(null);
            $table->integer('available_at')->unsigned();
            $table->integer('created_at')->unsigned();

            $table->primary(['id']);

            $table->index(['queue'], 'jobs_queue_index');
        });
    }

    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('jobs');
        Schema::enableForeignKeyConstraints();
    }
};
