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
                'name' => '10x Classes',
                'type' => 'quota',
                'quota_classes' => 10,
                'price' => 200000,
            ],
            [
                'name' => '20x Classes',
                'type' => 'quota',
                'quota_classes' => 20,
                'price' => 380000,
            ],
            [
                'name' => '30x Classes',
                'type' => 'quota',
                'quota_classes' => 30,
                'price' => 540000,
            ],
            [
                'name' => 'Package Monthly',
                'type' => 'monthly',
                'quota_classes' => null,
                'price' => 300000,
            ],
            [
                'name' => 'Package Group',
                'type' => 'group',
                'quota_classes' => null,
                'price' => 300000,
            ]
        ];

        foreach ($packages as $data) {
            Package::create($data);
        }
    }

}
