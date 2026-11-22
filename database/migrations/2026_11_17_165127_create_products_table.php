<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->bigInteger('id')->unsigned()->autoIncrement();
            $table->string('name', 255)->charset('utf8mb4')->collation('utf8mb4_unicode_ci');
            $table->string('sku', 100)->charset('utf8mb4')->collation('utf8mb4_unicode_ci');
            $table->text('description')->nullable()->default(null)->charset('utf8mb4')->collation('utf8mb4_unicode_ci');
            $table->bigInteger('brand_id')->unsigned()->nullable()->default(null);
            $table->bigInteger('category_id')->unsigned()->nullable()->default(null);
            $table->bigInteger('price')->unsigned();
            $table->bigInteger('sale_price')->unsigned()->nullable()->default(null);
            $table->integer('stock')->unsigned()->default(0);
            $table->integer('low_stock_threshold')->unsigned()->default(0);
            $table->enum('status', ['active', 'inactive'])->default('active')->charset('utf8mb4')->collation('utf8mb4_unicode_ci');
            $table->string('slug', 255)->nullable()->default(null)->charset('utf8mb4')->collation('utf8mb4_unicode_ci');
            $table->string('meta_title', 255)->nullable()->default(null)->charset('utf8mb4')->collation('utf8mb4_unicode_ci');
            $table->string('meta_description', 255)->nullable()->default(null)->charset('utf8mb4')->collation('utf8mb4_unicode_ci');
            $table->timestamp('created_at')->nullable()->default(null);
            $table->timestamp('updated_at')->nullable()->default(null);
            $table->timestamp('deleted_at')->nullable()->default(null);

            $table->primary(['id']);

            $table->index(['brand_id'], 'products_brand_id_index');

            $table->index(['category_id'], 'products_category_id_index');

            $table->unique(['sku'], 'products_sku_unique');

            $table->index(['status', 'stock'], 'products_status_stock_index');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->foreign(['brand_id'], 'products_brand_id_foreign')
                ->references(['id'])
                ->on('brands')
                ->onDelete('set null')
                ->onUpdate('restrict');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->foreign(['category_id'], 'products_category_id_foreign')
                ->references(['id'])
                ->on('product_categories')
                ->onDelete('set null')
                ->onUpdate('restrict');
        });
    }

    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('products');
        Schema::enableForeignKeyConstraints();
    }
};
