<?php

use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \App\Model\Product::create([
            'model_id' => 'AL313',
            'name' => 'ZG-BHR02',
            //'price' => 5000
        ]);

        \App\Model\Product::create([
            'model_id' => 'AL170',
            'name' => 'ZG-UGS',
            //'price' => 3500
        ]);

        \App\Model\Product::create([
            'model_id' => 'AL325',
            'name' => 'FW-LJB',
            //'price' => 6500
        ]);

        \App\Model\Product::create([
            'model_id' => 'AL169',
            'name' => 'K-35',
            //'price' => 8000
        ]);
        \App\Model\Product::create([
            'model_id' => 'AL204',
            'name' => 'ZG-UGS',
            //'price' => 12000
        ]);
        \App\Model\Product::create([
            'model_id' => 'AL030',
            'name' => 'ZG-UGS',
            //'price' => 7500
        ]);
        \App\Model\Product::create([
            'model_id' => 'AL166',
            'name' => 'ZG-UGS',
            //'price' => 2000
        ]);
        \App\Model\Product::create([
            'model_id' => 'AL106',
            'name' => 'ZG-UGS',
            //'price' => 13500
        ]);
        \App\Model\Product::create([
            'model_id' => 'AL188',
            'name' => 'ZG',
            //'price' => 3000
        ]);

        \App\Model\Product::create([
            'model_id' => 'AL316',
            'name' => 'ZG-TT01',
            //'price' => 3000
        ]);

        \App\Model\Product::create([
            'model_id' => 'AL178',
            'name' => 'ZG스노우',
            //'price' => 3000
        ]);

        \App\Model\Product::create([
            'model_id' => 'AL330',
            'name' => 'ZG-TY13 ',
            //'price' => 3000
        ]);

        \App\Model\Product::create([
            'model_id' => 'AL173',
            'name' => 'ZG666',
            //'price' => 3000
        ]);

        \App\Model\Product::create([
            'model_id' => 'AL332',
            'name' => 'ZG-TT03',
            //'price' => 3000
        ]);

        \App\Model\Product::create([
            'model_id' => 'AL183',
            'name' => 'ZG-G8',
            //'price' => 3000
        ]);

        \App\Model\Product::create([
            'model_id' => 'AL182',
            'name' => 'ZG1601',
            //'price' => 3000
        ]);

        \App\Model\Product::create([
            'model_id' => 'AL181',
            'name' => 'K-35',
            //'price' => 3000
        ]);
    }
}
