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
        Schema::create('transactions', function (Blueprint $table) {
            $table->uuid('id')->primary();
                $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
                $table->string('methode_payment');
                $table->string('code_transaction')->unique();
                $table->integer('amount_bill');
                $table->integer('amount_credit');
                $table->date('transaction_date');
                $table->time('transaction_time');
                $table->enum('status', ['pending', 'success', 'failed']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
