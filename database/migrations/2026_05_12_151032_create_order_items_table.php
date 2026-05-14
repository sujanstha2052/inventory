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
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignId('product_variant_id')->constrained('product_variants')->restrictOnDelete();
            $table->foreignId('warehouse_id')->nullable()->constrained('warehouses')->nullOnDelete();

            $table->decimal('quantity', 12, 2)->unsigned();
            $table->decimal('unit_price', 14, 2)->default(0.00);
            $table->decimal('discount_amount', 14, 2)->default(0.00);
            $table->decimal('total_price', 14, 2)->default(0.00);
            $table->decimal('tax_rate', 5, 2)->default(0.00);
            $table->decimal('tax_amount', 14, 2)->default(0.00);

            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index(['order_id', 'product_variant_id']);

            $table->comment('Line items for customer orders with price snapshot');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
