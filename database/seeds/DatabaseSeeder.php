<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call(ShopTypesSeeder::class);
        $this->call(UserSeeder::class);
        $this->call(OrderStatusSeeder::class);
        //$this->call(ProductSeeder::class);
        $this->call(DeliveryProviderSeeder::class);
    }
}
