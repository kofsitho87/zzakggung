<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Model\Trade;
use Auth;

class UserController extends Controller
{
    public function tradesView(Request $request)
    {
        $user = Auth::user();

        $count = 15;
        $page = $request->page;

        //$read_trades = Trade::where('user_id', $user->id)->paginate($count);
        $read_trades = Trade::where('user_id', $user->id)->orderByDesc('id')->paginate($count);

        //수정된 부분
        $all_trades = Trade::where('user_id', $user->id)->get();
        $trades = [];
        foreach($all_trades as $idx => $trade)
        {
            $trade->plus = $trade->is_plus ? $trade->price : 0;
            $trade->minus = $trade->is_plus ? 0 : $trade->price;
            $trade->change = $trade->plus - $trade->minus;

            if( $idx > 0 )
            {
                $change = $all_trades[$idx - 1]->change;
                $trade->change = $change + $trade->change;
            }

            $trades[] = $trade;
        }
        $trades = collect($trades)->reverse()->values();

        // $trades = Trade::where('user_id', $user->id)->get()->map(function($trade, $idx) use($all_trades){
        //     $trade->plus = $trade->is_plus ? $trade->price : 0;
        //     $trade->minus = $trade->is_plus ? 0 : $trade->price;
        //     $trade->change = $trade->plus - $trade->minus;

        //     if( $idx > 0 )
        //     {
        //         //$limit = ($page - 1) * $count;
        //         //$prev_trades = Trade::where('user_id', $user->id)->limit($idx)->get();

        //         $prev_trades = $all_trades->slice(1, $idx)->values();
        //         // $change = $prev_trades->sum(function($trade){
        //         //     return $trade->is_plus ? $trade->price : -$trade->price;
        //         // });
        //         $trade->change = $change + $trade->change;
        //     }
        //     return $trade;
        // });
        //->reverse()->values();

        $total_plus_price     = $user->totalPlusPrice();
        $total_availble_price = $user->tradeAvailblePrice();
        $total_minus_price    = $user->tradeTotalMinusPrice();

        $data = compact('read_trades', 'trades', 'user', 'total_plus_price', 'total_availble_price', 'total_minus_price');
        return view('user_trades', $data);
    }
}
