<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lfm_file_mime_types', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb3';
            $table->collation = 'utf8mb3_unicode_ci';

            $table->integer('id')->unsigned()->autoIncrement();
            $table->integer('user_id')->unsigned();
            $table->string('name', 255)->nullable()->default(null)->charset('utf8mb3')->collation('utf8mb3_unicode_ci');
            $table->string('mime_type', 255)->nullable()->default(null)->charset('utf8mb3')->collation('utf8mb3_unicode_ci');
            $table->string('ext', 50)->nullable()->default(null)->charset('utf8mb3')->collation('utf8mb3_unicode_ci');
            $table->string('icon_class', 250)->nullable()->default(null)->charset('utf8mb3')->collation('utf8mb3_unicode_ci');
            $table->string('description', 255)->nullable()->default(null)->charset('utf8mb3')->collation('utf8mb3_unicode_ci');
            $table->integer('created_by')->unsigned()->nullable()->default(0);
            $table->timestamp('created_at')->nullable()->default(null);
            $table->timestamp('updated_at')->nullable()->default(null);
            $table->timestamp('deleted_at')->nullable()->default(null);

            $table->primary(['id']);
        });
    }

    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('lfm_file_mime_types');
        Schema::enableForeignKeyConstraints();
    }
};
