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
        Schema::create('supplies', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('category_supplies_id')
                ->constrained('category_supplies')
                ->onDelete('cascade');
            $table->string('stock');
            $table->enum('unit', ['pcs', 'pack', 'box', 'liter', 'kg', 'gram', 'ml']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('supplies');
    }
};
