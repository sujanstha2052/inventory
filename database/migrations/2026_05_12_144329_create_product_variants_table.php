<?php

use App\Models\User;
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
        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();

            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();

            $table->string('sku')->unique();

            $table->string('name')->nullable();
            $table->json('attributes')->nullable();
            $table->decimal('purchase_price', 10, 2)->nullable()->default(0.00);
            $table->decimal('selling_price', 10, 2)->default(0.00);

            $table->string('barcode')->nullable()->unique();
            $table->boolean('is_default')->default(false);
            $table->decimal('weight', 6, 2)->nullable();
            $table->decimal('width', 6, 2)->nullable();
            $table->decimal('height', 6, 2)->nullable();
            $table->decimal('depth', 6, 2)->nullable();

            $table->unsignedInteger('low_stock_threshold')->nullable();
            $table->boolean('is_active')->default(true);

            $table->foreignIdFor(User::class, 'created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignIdFor(User::class, 'updated_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();
            $table->index(['product_id', 'is_default']);

            $table->comment('Individual product variants with SKU, prices, and attributes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_variants');
    }
};
