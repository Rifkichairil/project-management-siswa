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
        Schema::table('student_packages', function (Blueprint $table) {
            $table->integer('old_package_to_deactivate')->nullable()
                  ->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('student_packages', function (Blueprint $table) {
            $table->dropColumn('old_package_to_deactivate');
        });
    }
};
