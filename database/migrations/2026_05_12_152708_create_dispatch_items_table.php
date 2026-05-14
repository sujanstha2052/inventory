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
        Schema::create('dispatch_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('dispatch_id')->constrained('dispatches')->cascadeOnDelete();
            $table->foreignId('order_item_id')->constrained('order_items')->restrictOnDelete();
            $table->foreignId('stock_id')->nullable()->constrained('stock')->nullOnDelete();

            $table->decimal('quantity', 12, 2)->unsigned();
            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index(['dispatch_id', 'order_item_id']);
            $table->comment('Individual items within a dispatch, linking order items to stock batches');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dispatch_items');
    }
};
