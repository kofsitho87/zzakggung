<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Validator;
use File;
use Auth;
use DB;
use Carbon\Carbon;

use App\Model\Order;
use App\Model\Product;

use App\Exports\OrdersExport;

class OrderController extends Controller
{
    public $page_counts = [
        15,
        30,
        50,
        100,
        200,
        500
    ];
    public $keyword_options = [
        '전체',
        '받는분',
        '받는분의 전화',
        '운송장번호',
        '주문번호',
        '모델번호',
    ];

    public function productsExample(Request $request)
    {
        $file_path = storage_path('app/example/user_product.xlsx');
        return response()->download($file_path, '주문내역업로드샘플.xlsx');
    }

    public function export(Request $request)
    {
        $user = Auth::user();

        //주문상태: delivery_status
        //기간: sdate, edate 
        //model_id
        //receiver 

        $delivery_status = null; //$request->delivery_status;
        $sdate           = null; //$request->sdate;
        $edate           = null; //$request->edate;
        $keyword_option  = $request->keyword_option;
        $keyword         = trim($request->keyword);
        $order_by        = $request->order_by;

        //조건검색

        if ($request->delivery_status > 0) {
            $delivery_status = $request->delivery_status;
        }

        if ($request->sdate && $request->edate) {
            $sdate = $request->sdate;
            $edate = $request->edate;
        }

        return (new OrdersExport($user, $delivery_status, $sdate, $edate, $keyword_option, $keyword, $order_by))->download('orders.xlsx');
    }

    public function tradeView(Request $request)
    {
        $user = Auth::user();

        //$all_total_price = $user->totalPrice();
        $all_total_price = 0;
        $orders = collect([]);

        //날짜검색
        if (
            isset($request->sdate) && isset($request->edate)
            &&
            !empty($request->sdate) && !empty($request->edate)
        ) {
            $sdate = $request->sdate;
            $edate = $request->edate;
        } else {
            // if( $firstOrder = $user->orders->first() )
            // {
            //     $sdate = $firstOrder->created_at->format('Y-m-d');
            // }
            // else
            // {
            //     $sdate = date('Y-m-d');
            // }

            // if( $lastOrder = $user->orders->last() )
            // {
            //     $edate = $lastOrder->created_at->format('Y-m-d');
            // }
            // else
            // {
            //     $edate = date('Y-m-d');
            // }
            $sdate = date('Y-m-d');
            $edate = date('Y-m-d');
        }

        $_sdate = Carbon::createFromFormat('Y-m-d H:i:s', $sdate . ' 00:00:00');
        $_edate = Carbon::createFromFormat('Y-m-d H:i:s', $edate . ' 23:59:59');
        $diffDays = $_sdate->diffInDays($_edate);

        for ($i = 0; $i <= $diffDays; $i++) {
            $fromDate = Carbon::createFromFormat('Y-m-d H:i:s', $sdate . ' 00:00:00')->addDays($i);
            $a = clone $fromDate;
            $toDate = $a->addHours(23)->addMinutes(59)->addSeconds(59);


            $_order_list = Order::with('status')->where([
                ['user_id', $user->id],
                ['created_at', '>=', $fromDate],
                ['created_at', '<=', $toDate]
            ])->get();

            $total_price = $_order_list->sum(function ($order) use ($user) {
                $price = $order->product->price($user->shop_type_id);
                return ($price * $order->qty) + $order->delivery_price - $order->minus_price;
            });

            $all_total_price += $total_price;

            $_orders = [
                'total_price' => $total_price,
            ];
            $_orders['list'] = $_order_list;

            if ($_orders['list']->count() > 0) {
                $orders[] = $_orders;
            }
            // echo $fromDate . "\n";
            // echo $toDate . "\n";
            // echo $i . "<br>";
        }
        //dd($orders);

        $orders = $orders->reverse();


        $data = compact('sdate', 'edate', 'orders', 'all_total_price');
        return view('trade_list', $data);
    }

    public function uploadView()
    {
        return view('upload');
    }

    public function listView(Request $request)
    {
        //$delivery_status = $request->delivery_status;
        //dd($request->getQueryString());

        $user = Auth::user();
        $query = Order::with('status')->where('user_id', $user->id);
        $keyword = trim($request->keyword);

        //주문상태검색
        if (isset($request->delivery_status) && $request->delivery_status > 0) {
            $status = $request->delivery_status;
            $query->where('delivery_status', $status);
            //$orders = $orders->where('delivery_status', $status);
        }
        //날짜검색
        if (isset($request->sdate) && isset($request->edate) && !empty($request->sdate) && !empty($request->edate)) {
            $_edate = $request->edate . ' 23:59:59';
            $query->where([
                ['created_at', '>=', $request->sdate],
                ['created_at', '<=', $_edate]
            ]);
            // $orders = $orders->where([
            //     ['created_at', '>=', $request->sdate],
            //     ['created_at', '<=', $_edate]
            // ]);
        }
        //조건검색
        if ($keyword && $request->keyword_option) {
            //keyword_option
            //1.receiver
            //2.phone
            //3.delivery_code
            //4.id
            //5.model_id


            switch ($request->keyword_option) {
                case 1:
                    $query->where('receiver', 'LIKE', "%${keyword}%");
                    break;
                case 2:
                    $query->where('phone_1', $keyword);
                    break;
                case 3:
                    $query->where('delivery_code', $keyword);
                    break;
                case 4:
                    $query->where('id', $keyword);
                    break;
                case 5:
                    $query->whereHas('product', function ($q) use ($keyword) {
                        $q->where('model_id', $keyword);
                    });
                    break;
                default:
                    break;
            }
        }

        $page_counts = $this->page_counts;
        $count = $request->count ? $page_counts[$request->count] : $page_counts[0];
        $orders = $query->paginate($count);

        $delivery_status = empty($request->delivery_status) ? 0 : $request->delivery_status;
        $sdate           = $request->sdate;
        $edate           = $request->edate;
        $cnt             = $request->count;
        $keyword         = $request->keyword;
        $keyword_option  = $request->keyword_option;
        $keyword_options = $this->keyword_options;

        $order_by        = $request->order_by;

        $data = compact('orders', 'delivery_status', 'sdate', 'edate', 'cnt', 'page_counts', 'keyword', 'keyword_option', 'keyword_options', 'order_by');
        //dd($data);
        return view('upload_list', $data);
    }

    public function uploadExcel(Request $request)
    {
        $credentials = $request->only('excel');
        $rules = [
            'excel' => 'required|file|mimetypes:application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ];
        $validator = Validator::make($credentials, $rules);
        if ($validator->fails()) {
            $messages = $validator->errors()->messages();
            return redirect()->back()->withErrors($validator->errors());
        }

        $user = Auth::user();

        //엑셀파일 임포트
        //$orders = \Maatwebsite\Excel\Facades\Excel::import(new \App\Imports\OrdersImport,  $request->file('excel'));
        $orders = \Maatwebsite\Excel\Facades\Excel::toArray(new \App\Imports\OrdersImport,  $request->file('excel'));
        $_orders = collect($orders[0]);

        //dd($_orders);

        $orders = collect();
        foreach ($_orders as $idx => $row) {
            #1.데이터 검증
            //0로우 데이터 없음
            if ($idx == 0) continue;

            //0-3공란
            //로우가 없을경우 제거
            // '택배사',
            // '송장번호',
            // '주문번호',
            // '모델번호',
            // '제품명',
            // '옵션',
            // '수량',
            // '거래처명',
            // '수령자',
            // '전화번호1(필수)',
            // '전화번호2(선택)',
            // '우편번호',
            // '주소',
            // '배송메세지'
            if (!isset($row[4]) && !isset($row[5]) && isset($row[6]) && isset($row[7]) && !isset($row[8]) && !isset($row[9]) && !isset($row[10]) && !isset($row[11]))
                continue;

            #2.데이터가공
            $model_id         = isset($row[3]) ? trim($row[3]) : '';
            $product          = Product::where('model_id', $model_id)->first();
            $qty              = isset($row[6]) ? trim($row[6]) : 0;
            $qty              = is_numeric($qty) ? $qty : 0;
            $zipcode          = isset($row[11]) ? trim($row[11]) : '';
            $address          = isset($row[12]) ? trim($row[12]) : '';
            $address_array    = explode(' ', $address);
            $receiver         = isset($row[8]) ? trim($row[8]) : '';
            $phone_1          = isset($row[9]) ? trim($row[9]) : '';
            $phone_2          = isset($row[10]) ? trim($row[10]) : '';
            $product_name     = isset($row[4]) ? trim($row[4]) : '';
            $option           = isset($row[5]) ? trim($row[5]) : '';
            $delivery_message = isset($row[13]) ? $row[13] : '';

            $delivery_price = $user->shop_type->delivery_price; //2500;
            $isJeju         = false;
            $set_item       = false;
            $set_master     = false;
            //$set_count      = 0;

            if (
                count($address_array) > 1
                &&
                (in_array($address_array[0], ['제주시', '제주특별자치도', '서귀포시'])
                    ||
                    in_array($address_array[1], ['제주시', '제주특별자치도', '서귀포시']))
            ) {
                $isJeju = true;
                //제주지역 추가 3000원
                $delivery_price += 3000;
            }

            if (isset($orders[$idx - 2])) {
                $prev_idx = $idx - 2;
                //이전 수령자와 전화번호1, 전화번호2가 전부 일치하면 세트아이템으로 표시
                if ($prev_order = $orders[$prev_idx]) {
                    //수령자
                    $prev_receiver = $prev_order->receiver;

                    //phone1, 2
                    $prev_phone_1 = $prev_order->phone_1;
                    $prev_phone_2 = $prev_order->phone_2;

                    $current_phone_1 = $phone_1;
                    $current_phone_2 = $phone_2;

                    //1. 수령자 체크
                    if (
                        $prev_receiver == $receiver
                        &&
                        $prev_phone_1 == $current_phone_1
                        &&
                        $prev_phone_2 == $current_phone_2
                    ) {
                        $set_item = true;
                        $delivery_price = 0;
                        $prev_order->set_item = true;

                        // $prev_order->set_master = true;
                        // $prev_order->set_count += 1; 
                    }
                }
            }

            $order = new Order([
                'set_item'         => $set_item, //묶음배송
                'user_id'          => $user->id,
                'qty'              => $qty,
                'receiver'         => $receiver,
                'zipcode'          => $zipcode,
                'address'          => $address,
                'phone_1'          => $phone_1,
                'phone_2'          => $phone_2,
                'delivery_message' => $delivery_message,
                'delivery_price'   => $delivery_price,
                'model_id'         => $model_id,
                'product_name'     => $product_name,
                'option'           => $option,

                'price'            => 0,
                'can_upload'       => true,
            ]);

            if ($product) {
                $order->product_id = $product->id;
                $shop_type         = $user->shop_type->id;
                $order->price      = $product->price($shop_type);
            }

            $orders[] = $order;
        }

        if ($orders->count() < 1) {
            return redirect()->back()->withErrors([
                '주문내역이 존재 하지 않거나 잘못된 형식의 엑셀입니다.'
            ]);
        }

        $total_price = 0;
        $can_upload = true;
        foreach ($orders as $order) {
            $total_price += ($order->price * $order->qty);
            $total_price += $order->delivery_price;
            if (empty($order->product_id)) {
                $can_upload = false;
                $order->can_upload = false;
            } else if (empty($order->phone_1)) {
                $can_upload = false;
                $order->can_upload = false;
            } else if (empty($order->address)) {
                $can_upload = false;
                $order->can_upload = false;
            } else if (empty($order->receiver)) {
                $can_upload = false;
                $order->can_upload = false;
            } else if (empty($order->zipcode)) {
                $can_upload = false;
                $order->can_upload = false;
            } else if (empty($order->qty)) {
                $can_upload = false;
                $order->can_upload = false;
            }
            // else if( empty($order->product_name) ) 
            // {
            //     $can_upload = false;
            //     $order->can_upload = false;
            // }
        }

        return redirect()->back()->with([
            'orders'      => $orders,
            'can_upload'  => $can_upload,
            'total_price' => $total_price,
        ]);
        //return redirect()->back()->with('orders', $orders);
    }

    public function uploadOrders(Request $request)
    {
        $credentials = $request->only('order');
        $rules = [
            'order'                      => 'required|array',
            'order.product_id.*'         => 'required|integer',
            //'order.option.*'             => 'required|string',
            'order.qty.*'                => 'required|integer',
            'order.receiver.*'           => 'required|string',
            'order.phone_1.*'            => 'required|string',
            //'order.phone_2.*'          => 'required|string',
            'order.zipcode.*'            => 'required|string',
            'order.address.*'            => 'required|string',
            //'order.delivery_message.*' => 'alpha',
            'order.delivery_price.*'     => 'required|integer',
        ];
        $validator = Validator::make($credentials, $rules);
        if ($validator->fails()) {
            $messages = $validator->errors()->messages();
            return redirect()->back()->withErrors($validator->errors());
        }

        $user = Auth::user();

        DB::beginTransaction();
        foreach ($request->order['product_id'] as $idx => $id) {

            $order = new Order;
            $order->user_id          = $user->id;
            $order->product_id       = $id;
            $order->qty              = $request->order['qty'][$idx];
            $order->option           = $request->order['option'][$idx];
            $order->receiver         = $request->order['receiver'][$idx];
            $order->phone_1          = $request->order['phone_1'][$idx];
            $order->phone_2          = $request->order['phone_2'][$idx];
            $order->zipcode          = $request->order['zipcode'][$idx];
            $order->address          = $request->order['address'][$idx];
            $order->delivery_message = $request->order['delivery_message'][$idx];
            $order->delivery_price   = $request->order['delivery_price'][$idx];
            $order->delivery_status  = $user->shop_type->delivery_status; //$user->shop_type == 'A' ? 2 : 1;

            //$orders[] = $order;

            if (!$order->save()) {
                DB::rollback();
                return redirect()->back()->withErrors('message', 'DB: 주문내역 생성 실패');
            }
        }
        DB::commit();

        //dd($orders);

        return redirect()->back()->with('upload_finish', true);
    }

    public function update(Request $request, Order $order)
    {
        $credentials = $request->only('act', 'message');
        $rules = [
            'act' => 'required|in:back,change',
        ];
        $validator = Validator::make($credentials, $rules);
        if ($validator->fails()) {
            $messages = $validator->errors()->messages();
            return redirect()->back()->withErrors($validator->errors());
        }

        $user = Auth::user();

        if ($order->user->id != $user->id) {
            return redirect()->back()->withErrors('NOT_VALID_USER');
        }

        $order->delivery_status = $request->act == 'back' ? 5 : 6;
        if (!$order->save()) {
            return redirect()->back()->withErrors('NOT_UPDATE_ORDER');
        }

        if ($message = $request->message) {
            $order->message()->create([
                'order_id' => $order->id,
                'content' => $message
            ]);
        }

        return redirect()->back();
    }
}
