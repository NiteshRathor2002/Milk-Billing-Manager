<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bills', function (Blueprint $table) {
            $table->id();
            $table->string('bill_number', 50)->unique();
            $table->foreignId('customer_id')->constrained('customers')->onDelete('restrict');
            $table->string('billing_month', 7);
            $table->date('period_start_date');
            $table->date('period_end_date');
            $table->decimal('total_milk_quantity', 8, 2);
            $table->decimal('milk_charges', 10, 2);
            $table->decimal('previous_balance', 10, 2)->default(0.00);
            $table->decimal('payments_credited', 10, 2)->default(0.00);
            $table->decimal('amount_due', 10, 2);
            $table->json('itemized_snapshot');
            $table->enum('status', ['unpaid', 'partially_paid', 'paid'])->default('unpaid');
            $table->timestamp('generated_at')->useCurrent();
            $table->timestamps();

            $table->unique(['customer_id', 'billing_month']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bills');
    }
};
