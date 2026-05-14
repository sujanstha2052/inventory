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
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();

            $table->foreignId('stock_id')->constrained('stock')->restrictOnDelete();
            $table->foreignId('product_variant_id')->constrained('product_variants')->restrictOnDelete();
            $table->foreignId('warehouse_id')->constrained('warehouses')->restrictOnDelete();

            $table->string('type')->comment('in or out or adjustment');

            $table->decimal('quantity', 12, 2)->unsigned()->default(0.00);
            $table->decimal('unit_cost', 14, 2)->nullable();

            $table->string('reference_type')->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->text('notes')->nullable();

            $table->foreignIdFor(User::class, 'created_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();

            $table->index(['product_variant_id', 'warehouse_id']);
            $table->index('created_at');

            $table->comment('Audit log of all stock movements (in, out, adjustments)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
