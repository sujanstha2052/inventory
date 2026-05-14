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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('customer_id')->constrained('customers')->restrictOnDelete();
            $table->string('payment_number')->unique();
            $table->decimal('amount', 14, 2)->default(0.00);
            $table->string('payment_method')->comment('Cash, Bank Transfer, Mobile Money, Cheque, etc.');
            $table->string('reference')->nullable();
            $table->timestamp('payment_date');
            $table->text('notes')->nullable();
            $table->foreignIdFor(User::class, 'created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignIdFor(User::class, 'updated_by')->nullable()->constrained('users')->nullOnDelete();


            $table->timestamps();
            $table->softDeletes();

            $table->index('payment_method');
            $table->index('payment_date');

            $table->comment('Customer payments/receipts for invoices');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
