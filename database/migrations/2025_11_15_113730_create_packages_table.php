<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('packages', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('type', ['quota', 'monthly', 'group']);

            $table->integer('quota_classes')->nullable();
            $table->integer('price');
            $table->timestamps();
        });

           // INSERT DATA DEFAULT
        $packages = [
            [
                'name' => '10x Classes',
                'type' => 'quota',
                'quota_classes' => 10,
                'price' => 200000,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => '20x Classes',
                'type' => 'quota',
                'quota_classes' => 20,
                'price' => 380000,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => '30x Classes',
                'type' => 'quota',
                'quota_classes' => 30,
                'price' => 540000,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Package Monthly',
                'type' => 'monthly',
                'quota_classes' => null,
                'price' => 300000,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Package Group',
                'type' => 'group',
                'quota_classes' => null,
                'price' => 300000,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('packages')->insert($packages);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('packages');
    }
};
