<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('daily_deliveries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade');
            $table->date('delivery_date');
            $table->enum('delivery_type', ['normal', 'holiday', 'reduced', 'extra'])->default('normal');
            $table->decimal('default_quantity', 5, 2);
            $table->decimal('actual_quantity', 5, 2);
            $table->decimal('extra_quantity', 5, 2)->default(0.00);
            $table->decimal('rate_applied', 8, 2);
            $table->decimal('daily_charge', 10, 2);
            $table->string('note', 255)->nullable();
            $table->timestamps();

            $table->unique(['customer_id', 'delivery_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_deliveries');
    }
};
