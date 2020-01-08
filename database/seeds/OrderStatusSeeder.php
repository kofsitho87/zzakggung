<?php

use Illuminate\Database\Seeder;

class OrderStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $status = ['입금대기', '배송준비중', '발송대기', '발송완료', '반품요청', '교환요청', '반품완료', '교환완료'];
        foreach($status as $row)
        {
            App\Model\OrderStatus::create([
                'name' => $row
            ]);
        }
    }
}
