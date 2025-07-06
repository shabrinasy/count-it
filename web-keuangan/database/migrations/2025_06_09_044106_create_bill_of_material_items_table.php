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
        Schema::create('bill_of_material_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bill_of_materials_id')
                ->constrained('bill_of_materials')
                ->onDelete('cascade');
            $table->foreignId('supplies_id')
                ->constrained('supplies')
                ->onDelete('cascade');
            $table->foreignId('category_supplies_id')
                ->constrained('category_supplies')
                ->onDelete('cascade');
            $table->integer('quantity');
            $table->string('unit');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bill_of_material_items');
    }
};
