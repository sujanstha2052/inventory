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
        Schema::create('categories', function (Blueprint $table) {
            $table->id();

            $table->foreignId('parent_id')->nullable()->constrained('categories')->nullOnDelete();

            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();

            $table->boolean('is_active')->default(true);

            $table->unsignedInteger('order_column')->default(0);

            $table->timestamps();
            $table->softDeletes();

            $table->index('parent_id');

            $table->comment('Hierarchical product categories');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
