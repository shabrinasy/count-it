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
        Schema::create('accounts', function (Blueprint $table) {
            $table->id();
            $table->string('code_account');
            $table->string('name_account');
            $table->enum('balance', ['debit', 'credit']);
            $table->enum('type', ['header', 'subheader', 'item']);
            $table->enum('account_activity', ['operating', 'investing', 'financing'])->nullable();
            $table->unsignedBigInteger('parent')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('parent')->references('id')->on('accounts')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('accounts');
    }
};
