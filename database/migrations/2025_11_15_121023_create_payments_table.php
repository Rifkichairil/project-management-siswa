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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_package_id')->constrained();
            $table->integer('amount');
            $table->enum('status', ['pending', 'paid', 'failed'])->default('pending');
            $table->enum('method', ['transfer', 'cash'])->nullable();
            $table->string('proof')->nullable(); // bukti pembayaran (foto)
            $table->timestamps();
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
