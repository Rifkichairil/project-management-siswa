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
        Schema::create('student_packages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained();
            $table->foreignId('package_id')->constrained();
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->integer('total_quota')->nullable();
            $table->integer('used_quota')->nullable();
            $table->integer('remaining_quota')->nullable();
            $table->enum('status', ['active', 'inactive', 'expired'])
                  ->default('active');
            $table->integer('old_package_to_deactivate')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_packages');
    }
};
