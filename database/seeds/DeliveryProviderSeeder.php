<?php

use Illuminate\Database\Seeder;

class DeliveryProviderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $providers = ['로젠택배', '한진택배'];

        foreach($providers as $provider)
        {
            $model = new \App\Model\DeliveryProvider;
            $model->name = $provider;
            $model->save();
        }
    }
}
