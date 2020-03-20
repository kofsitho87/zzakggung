<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;


use Validator;
use DB;
use Artisan;
use Auth;
use Storage;

use App\Model\User;
use App\Model\Order;
use App\Model\OrderStatus;
use App\Model\Product;
use App\Model\ShopType;
use App\Model\DeliveryProvider;
use App\Model\Trade;
use App\Model\Notice;

class StatisticsController extends BaseController
{
    public function topProducts(Request $request)
    {
        $topCount = 10;
        if ($request->topCount) {
            $topCount = $request->topCount;
        }

        Order::$withoutAppends = true;
        $items = Order::with('product')
            ->select('product_id', DB::raw('count(*) as total'))
            ->groupBy('product_id')
            ->orderBy('total', 'desc')
            ->limit($topCount)
            ->get();

        $data = compact('items');
        return $this->sendResponse($data);
    }

    public function topOrderByProvider(Request $request)
    {
        $topCount = 10;
        if ($request->topCount) {
            $topCount = $request->topCount;
        }

        Order::$withoutAppends = true;
        $items = Order::with('user')
            ->select('user_id', DB::raw('count(*) as total'))
            ->groupBy('user_id')
            ->orderBy('total', 'desc')
            ->limit($topCount)
            ->get();

        $data = compact('items');
        return $this->sendResponse($data);
    }

    public function topOrderCity(Request $request)
    {
        $topCount = 10;
        if ($request->topCount) {
            $topCount = $request->topCount;
        }

        Order::$withoutAppends = true;
        $items = Order::select('address')
            //->limit($topCount)
            ->get();

        $items = $items->map(function ($item, $idx) {
            $arr_addrs = explode(" ", $item->address);
            $new_addr = trim($arr_addrs[0]);
            switch ($new_addr) {
                case "서울":
                    $new_addr = "서울특별시";
                    break;
                case "서울시":
                    $new_addr = "서울특별시";
                    break;
                case "경기":
                    $new_addr = "경기도";
                    break;
                case "경남":
                    $new_addr = "경상남도";
                    break;
                case "경북":
                    $new_addr = "경상북도";
                    break;
                case "인천":
                    $new_addr = "인천광역시";
                    break;
                case "부산":
                    $new_addr = "부산광역시";
                    break;
                case "전남":
                    $new_addr = "전라남도";
                    break;
                case "전북":
                    $new_addr = "전라북도";
                    break;
                case "충남":
                    $new_addr = "충청남도";
                    break;
                case "대구":
                    $new_addr = "대구광역시";
                    break;
                case "강원":
                    $new_addr = "강원도";
                    break;
                case "광주":
                    $new_addr = "광주광역시";
                    break;
                case "충북":
                    $new_addr = "충청북도";
                    break;
                case "제주":
                    $new_addr = "제주특별자치도";
                    break;
                case "세종":
                    $new_addr = "세종특별자치시";
                    break;
                case "울산":
                    $new_addr = "울산광역시";
                    break;
                case "인천남구":
                    $new_addr = "인천광역시";
                    break;
                case "제주특별시":
                    $new_addr = "제주특별자치도";
                    break;
            }
            $item->new_addr = $new_addr;
            return $item;
        })
            ->filter(function ($item, $idx) {
                return in_array(
                    $item->new_addr,
                    [
                        '서울특별시', '부산광역시', '대구광역시', '인천광역시', '광주광역시', '대전광역시', '울산광역시',
                        '세종특별자치시', '경기도', '강원도', '충청북도', '충청남도', '전라북도', '전라남도', '경상북도', '	경상남도', '제주특별자치도'
                    ]
                );
            });


        $groupCount = $items->groupBy('new_addr')->map(function ($item, $key) {
            return collect($item)->count();
        });
        $data = compact('groupCount');
        return $this->sendResponse($data);
    }

    public function topOrderByDate(Request $request)
    {

        Order::$withoutAppends = true;
        $items = Order::select(DB::raw('date_format(created_at, \'%Y-%m\') date_ymd, count(*) cnt'))
            ->groupBy('date_ymd')
            ->get();

        $data = compact('items');
        return $this->sendResponse($data);
    }
}
