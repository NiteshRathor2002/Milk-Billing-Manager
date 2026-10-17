<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('app_settings', function (Blueprint $table) {
            $table->id();
            $table->string('business_name', 150)->default('Dairy Milk Supply');
            $table->string('business_phone', 30)->default('+91 9876543210');
            $table->string('business_email', 100)->nullable();
            $table->text('business_address')->nullable();
            $table->string('upi_id', 100)->nullable();
            $table->string('bill_title', 100)->default('Milk Delivery Invoice');
            $table->text('bill_footer')->nullable();
            $table->string('currency_symbol', 10)->default('₹');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('app_settings');
    }
};
