<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_categories', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->bigInteger('id')->unsigned()->autoIncrement();
            $table->bigInteger('parent_id')->unsigned()->nullable()->default(null);
            $table->string('title', 255)->charset('utf8mb4')->collation('utf8mb4_unicode_ci');
            $table->string('slug', 255)->charset('utf8mb4')->collation('utf8mb4_unicode_ci');
            $table->text('description')->nullable()->default(null)->charset('utf8mb4')->collation('utf8mb4_unicode_ci');
            $table->string('image', 255)->nullable()->default(null)->charset('utf8mb4')->collation('utf8mb4_unicode_ci');
            $table->integer('sort_order')->unsigned()->default(0);
            $table->timestamp('created_at')->nullable()->default(null);
            $table->timestamp('updated_at')->nullable()->default(null);
            $table->timestamp('deleted_at')->nullable()->default(null);

            $table->primary(['id']);

            $table->index(['parent_id'], 'product_categories_parent_id_index');

            $table->unique(['slug'], 'product_categories_slug_unique');
        });

        Schema::table('product_categories', function (Blueprint $table) {
            $table->foreign(['parent_id'], 'product_categories_parent_id_foreign')
                ->references(['id'])
                ->on('product_categories')
                ->onDelete('set null')
                ->onUpdate('restrict');
        });
    }

    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('product_categories');
        Schema::enableForeignKeyConstraints();
    }
};
