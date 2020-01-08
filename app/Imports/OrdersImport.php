<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\ToModel;

use Auth;

class OrdersImport implements ToModel
{
    /**
     * @param array $row
     *
     * @return Order|null
     */
    public function model(array $row)
    {
        $user = Auth::user();
        return new \App\Model\Order([
            'user_id'          => $user->id,
            'product_id'       => $row[2],
            'qty'              => $qty,
            'receiver'         => $row[4],
            'phone_1'          => $row[5],
            'phone_2'          => $row[6],
            'zipcode'          => $row[7],
            'address'          => $row[8],
            'delivery_message' => $row[9],
            'delivery_price'   => $row[11],
        ]);
    }

    /**
    * @param Collection $collection
    */
    public function collection(Collection $collection)
    {
        // $user = Auth::user();
        // $orders = [];
        // foreach($collection as $key => $row)
        // {
        //     if($key == 0) continue;

        //     $qty = is_int($row[3]) ? $row[3] : 1;

        //     $order = new \App\Model\Order;
            

        //     $order->delivery_status = 0;

        //     $orders[] = $order;
        // }
        // return $orders;
    }
}
