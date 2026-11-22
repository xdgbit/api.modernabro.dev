<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('user_customer_verify_mobile_codes', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb3';
            $table->collation = 'utf8mb3_unicode_ci';

            $table->id();
            $table->integer('user_customer_id')->unsigned();
            $table->string('verify_code', 255)->charset('utf8mb3')->collation('utf8mb3_unicode_ci');
            $table->tinyInteger('attempts')->unsigned()->default(0);
            $table->string('sys_log_sent_sms_table_name', 255)->nullable()->default(null)->charset('utf8mb3')->collation('utf8mb3_unicode_ci');
            $table->integer('sys_log_sent_sms_id')->nullable()->default(null);
            $table->timestamps();
            $table->timestamp('expired_at')->nullable()->default(null);
            $table->timestamp('success_attempt_at')->nullable()->default(null);
            $table->timestamp('archived_at')->nullable()->default(null);
            $table->timestamp('deleted_at')->nullable()->default(null);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('user_customer_verify_mobile_codes');
        Schema::enableForeignKeyConstraints();
    }
};
