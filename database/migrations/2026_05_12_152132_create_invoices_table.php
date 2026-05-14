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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();

            $table->foreignId('order_id')->constrained('orders')->restrictOnDelete();

            $table->string('invoice_number')->unique();

            $table->string('file_path')->nullable();

            $table->decimal('subtotal', 14, 2)->default(0.00);
            $table->decimal('tax_amount', 14, 2)->default(0.00);
            $table->decimal('discount_amount', 14, 2)->default(0.00);
            $table->decimal('grand_total', 14, 2)->default(0.00);

            $table->string('status')->default('unpaid')->comment('unpaid, paid, partially_paid, cancelled');

            $table->timestamp('issued_at')->nullable();
            $table->date('due_date')->nullable();
            $table->text('notes')->nullable();

            $table->foreignIdFor(User::class, 'created_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            $table->comment('Generated invoices with financial snapshot and pdf path');

            $table->index('status');
            $table->index('due_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
