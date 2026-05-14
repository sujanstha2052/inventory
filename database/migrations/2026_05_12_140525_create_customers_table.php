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
        Schema::create('customers', function (Blueprint $table) {
            $table->id();

            $table->foreignId('customer_group_id')->nullable()->constrained('customer_groups')->nullOnDelete();
            $table->foreignIdFor(User::class, 'created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignIdFor(User::class, 'updated_by')->nullable()->constrained('users')->nullOnDelete();

            $table->string('code')->unique();

            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('company_name')->nullable();
            $table->string('tax_number')->nullable();
            $table->text('notes')->nullable();

            $table->boolean('is_active')->default(true);

            $table->decimal('outstanding_balance', 12, 2)->default(0.00);
            $table->decimal('total_sales', 14, 2)->default(0.00);

            $table->timestamps();
            $table->softDeletes();

            $table->index('email');
            $table->index('phone');

            $table->comment('Customers with contact info, pricing group, and cached financials');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
