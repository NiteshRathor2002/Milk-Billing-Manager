<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('customer_code', 30)->unique();
            $table->string('name', 150);
            $table->string('whatsapp_number', 20);
            $table->decimal('daily_quantity', 5, 2)->default(1.00);
            $table->decimal('rate_per_litre', 8, 2)->default(60.00);
            $table->date('registration_date');
            $table->decimal('previous_balance', 10, 2)->default(0.00);
            $table->text('address')->nullable();
            $table->text('notes')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
