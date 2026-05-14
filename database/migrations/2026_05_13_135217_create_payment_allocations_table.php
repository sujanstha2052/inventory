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
        Schema::create('payment_allocations', function (Blueprint $table) {
            $table->id();

            $table->foreignId('payment_id')->constrained('payments')->cascadeOnDelete();
            $table->foreignId('invoice_id')->constrained('invoices')->restrictOnDelete();

            $table->decimal('amount', 14, 2)->unsigned();
            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index(['payment_id', 'invoice_id']);
            $table->comment('How a payment is split across invoices (invoice-wise collection)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_allocations');
    }
};
