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
        Schema::create('stock', function (Blueprint $table) {
            $table->id();

            $table->foreignId('product_variant_id')->constrained('product_variants')->restrictOnDelete();
            $table->foreignId('warehouse_id')->constrained('warehouses')->restrictOnDelete();

            $table->string('batch_number')->nullable();
            $table->decimal('quantity', 12, 2)->unsigned()->default(0.00);
            $table->decimal('unit_cost', 14, 2)->default(0.00);

            $table->timestamp('received_at')->nullable();
            $table->date('expiry_date')->nullable();
            $table->text('notes')->nullable();

            $table->foreignIdFor(User::class, 'created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignIdFor(User::class, 'updated_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['product_variant_id', 'warehouse_id']);
            $table->index('received_at');

            $table->comment('Inventory batches per variant per warehouse with FIFO costing');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock');
    }
};
