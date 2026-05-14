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
        Schema::create('products', function (Blueprint $table) {
            $table->id();

            $table->foreignId('brand_id')->nullable()->constrained('brands')->nullOnDelete();
            $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->foreignId('unit_id')->constrained('units')->restrictOnDelete();

            $table->string('name');
            $table->string('slug')->unique();
            $table->string('type')->comment("Allowed: 'simple', 'configurable'");
            $table->text('description')->nullable();

            $table->boolean('is_active')->default(true);

            $table->string('sku_prefix')->nullable();

            $table->unsignedInteger('low_stock_threshold')->default(0);

            $table->foreignIdFor(User::class, 'created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignIdFor(User::class, 'updated_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['type', 'is_active']);

            $table->comment('Table to store products. Each product can be simple or configurable. Configurable products can have multiple variants.');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
