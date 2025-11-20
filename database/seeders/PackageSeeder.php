<?php

namespace Database\Seeders;

use App\Models\Package;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PackageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $packages = [
            [
                'name' => '4x Classes',
                'type' => 'quota',
                'quota_classes' => 4,
                'price' => 200000,
            ],
            [
                'name' => '8x Classes',
                'type' => 'quota',
                'quota_classes' => 8,
                'price' => 380000,
            ],
            [
                'name' => '12x Classes',
                'type' => 'quota',
                'quota_classes' => 12,
                'price' => 540000,
            ],
            [
                'name' => 'Package Monthly',
                'type' => 'monthly',
                'quota_classes' => null,
                'price' => 300000,
            ]
        ];

        foreach ($packages as $data) {
            Package::create($data);
        }
    }

}
