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
        Schema::create('dispatches', function (Blueprint $table) {
            $table->id();

            $table->foreignId('order_id')->constrained('orders')->restrictOnDelete();
            $table->foreignId('warehouse_id')->constrained('warehouses')->restrictOnDelete();

            $table->string('dispatch_number')->unique();

            $table->string('status')->default('pending')->comment('pending,in_transit,delivered,cancelled');

            $table->text('notes')->nullable();

            $table->timestamp('dispatched_at')->nullable();
            $table->timestamp('delivered_at')->nullable();

            $table->foreignIdFor(User::class, 'created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignIdFor(User::class, 'updated_by')->nullable()->constrained('users')->nullOnDelete();


            $table->timestamps();
            $table->softDeletes();

            $table->index(['order_id', 'status']);
            $table->index('status');

            $table->comment('Fulfillment dispatches from. warehouses for orders');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dispatches');
    }
};
