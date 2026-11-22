<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lfm_files', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb3';
            $table->collation = 'utf8mb3_unicode_ci';

            $table->integer('id')->unsigned()->autoIncrement();
            $table->integer('user_id')->unsigned()->nullable()->default(0);
            $table->enum('user_type', ['user_customer', 'user_expert', 'public'])->nullable()->default(null)->charset('utf8mb3')->collation('utf8mb3_unicode_ci');
            $table->integer('file_mime_type_id')->unsigned();
            $table->double('file_size')->nullable()->default(null);
            $table->string('original_name', 255)->nullable()->default(null)->charset('utf8mb3')->collation('utf8mb3_unicode_ci');
            $table->string('original_extension', 255)->nullable()->default(null)->charset('utf8mb3')->collation('utf8mb3_unicode_ci');
            $table->string('original_mime_type', 255)->nullable()->default(null)->charset('utf8mb3')->collation('utf8mb3_unicode_ci');
            $table->longText('store_path')->nullable()->default(null)->charset('utf8mb3')->collation('utf8mb3_unicode_ci');
            $table->text('store_filename')->nullable()->default(null)->charset('utf8mb3')->collation('utf8mb3_unicode_ci');
            $table->string('md5_hash', 255)->nullable()->default(null)->charset('utf8mb3')->collation('utf8mb3_unicode_ci');
            $table->string('upload_reason', 255)->nullable()->default(null)->charset('utf8mb3')->collation('utf8mb3_unicode_ci');
            $table->integer('created_by')->nullable()->default(null);
            $table->timestamp('created_at')->nullable()->default(null);
            $table->timestamp('updated_at')->nullable()->default(null);
            $table->timestamp('deleted_at')->nullable()->default(null);

            $table->primary(['id']);
        });
    }

    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('lfm_files');
        Schema::enableForeignKeyConstraints();
    }
};
