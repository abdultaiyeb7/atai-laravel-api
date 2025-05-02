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
        Schema::create('admin_data', function (Blueprint $table) {
            $table->integer('admin_id');
            $table->integer('user_id');
            $table->string('admin_username', 50);
            $table->string('contact_no', 10);
            $table->string('website_url')->nullable();
            $table->boolean('is_logged_in')->nullable();
            $table->dateTime('registration_date')->nullable();
            $table->dateTime('expiration_date')->nullable();
            $table->text('refresh_token')->nullable();
            $table->time('logged_in')->nullable();
            $table->string('company_name', 100)->nullable();
            $table->integer('service_duration')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admin_data');
    }
};
