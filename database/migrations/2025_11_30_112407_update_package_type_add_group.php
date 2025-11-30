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
        // ubah tipe enum agar termasuk 'group'
        DB::statement("ALTER TABLE packages MODIFY type ENUM('quota', 'monthly', 'group')");
    }

    public function down(): void
    {
        // revert jika di-rollback
        DB::statement("ALTER TABLE packages MODIFY type ENUM('quota', 'monthly')");
    }

};
