<?php

use Illuminate\Database\Seeder;

class ShopTypesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $z = new \App\Model\ShopType;
        $z->type = 'Z';
        $z->delivery_price = 0;
        $z->save();

        $a = new \App\Model\ShopType;
        $a->type = 'A';
        $a->delivery_price = 2500;
        $a->save();

        $b = new \App\Model\ShopType;
        $b->type = 'B';
        $b->delivery_price = 3000;
        $b->save();

        $c = new \App\Model\ShopType;
        $c->type = 'C';
        $c->delivery_price = 0;
        $c->save();
    }
}
