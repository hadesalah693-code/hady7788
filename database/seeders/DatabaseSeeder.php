<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            LocationSeeder::class,
            PropertyTypeSeeder::class,
            AmenitySeeder::class,
            ServiceCategorySeeder::class,
            DemoServiceProviderSeeder::class,
            AdminUserSeeder::class,
            DemoPropertySeeder::class,
        ]);
    }
}
