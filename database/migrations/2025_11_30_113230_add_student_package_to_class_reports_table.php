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
        Schema::table('class_reports', function (Blueprint $table) {
            $table->foreignId('student_package_id')
                ->nullable()
                ->constrained()
                ->after('class_schedule_id'); // posisi setelah schedule
        });
    }

    public function down(): void
    {
        Schema::table('class_reports', function (Blueprint $table) {
            $table->dropForeign(['student_package_id']);
            $table->dropColumn('student_package_id');
        });
    }

};
