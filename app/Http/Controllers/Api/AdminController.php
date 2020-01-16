<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
//use App\Http\Controllers\Api\BaseController as BaseController;

use Validator;
use App\Model\User;
use App\Model\Order;
use App\Model\OrderStatus;
use App\Model\Product;
use App\Model\ShopType;
use App\Model\DeliveryProvider;
use App\Model\Trade;

use App\Exports\AdminOrderExport;

class AdminController extends BaseController
{
    
    public function shopTypes(Request $request)
    {
        $shopTypes = ShopType::all();
        $data = compact('shopTypes');
        return $this->sendResponse($data, '');
    }
    public function deliveryProviders(Request $request)
    {
        $providers = DeliveryProvider::all();
        $delivery_providers = [];

        foreach($providers as $provider)
        {
            $delivery_providers[$provider->id] = $provider->name;
        }

        $data = compact('providers', 'delivery_providers');
        return $this->sendResponse($data, '');
    }

    public function users(Request $request)
    {
        $users = User::where('is_admin', false)->paginate(50);
        $data = compact('users');
        return $this->sendResponse($data, '');
    }

    public function user(Request $request, User $user)
    {
        $data = compact('user');
        return $this->sendResponse($data, '');
    }

    public function updateUser(Request $request, User $user)
    {
        $credentials = $request->only('name', 'user_id', 'shop_type_id');
        $rules = [
            'name' => 'required',
            'user_id' => 'required',
            'shop_type_id' => 'required',
        ];
        $messages = [
            'name.required'  => '필수값입니다.',
            'user_id.required'  => '필수값입니다.',
            'shop_type_id.required'  => '필수값입니다.'
        ];
        $validator = Validator::make($credentials, $rules, $messages);
        if( $validator->fails() )
        {
            $messages = $validator->errors()->messages();
            return $this->sendError('FAILED_UPDATE_USER', $messages);
        }

        $user->name = $request->name;
        $user->user_id = $request->user_id;
        $user->shop_type_id = $request->shop_type_id;

        if( ! $user->save() )
        {
            return $this->sendError('FAILED_UPDATE_USER');
        }
        return $this->sendResponse([]);
    }

    public function userTrades(Request $request, User $user)
    {
        $count = 100;
        $page = $request->page;

        $read_trades = Trade::where('user_id', $user->id)
            ->orderByDesc('id')
            ->paginate($count);
        
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

        $total_plus_price     = $user->totalPlusPrice();
        $total_availble_price = $user->tradeAvailblePrice();
        $total_minus_price    = $user->tradeTotalMinusPrice();

        $data = compact('trades', 'user', 'total_plus_price', 'total_availble_price', 'total_minus_price', 'read_trades');
        return $this->sendResponse($data, '');
    }

    public function addUserTrade(Request $request, User $user)
    {
        $credentials = $request->only('is_plus', 'price', 'content');
        $rules = [
            'is_plus'    => 'required|bool',
            'price'      => 'required|integer',
            'content'    => 'required'
        ];
        $validator = Validator::make($credentials, $rules);
        if( $validator->fails() )
        {
            $messages = $validator->errors()->messages();
            return $this->sendError('FAILED_ADD_USER_TRADE', $messages);
        }

        $trade = new Trade;
        $trade->user_id = $user->id;
        $trade->is_plus = $request->is_plus;
        $trade->price   = (int)$request->price;
        $trade->content = $request->content;

        if( ! $trade->save() )
        {
            return $this->sendError('FAILED_ADD_USER_TRADE');
        }
        
        $data = compact('trade');
        return $this->sendResponse($data, '');
    }

    public function deleteUserTrades(Request $request, User $user)
    {
        $credentials = $request->only('trades');
        $rules = [
            'trades.*'    => 'required',
        ];
        $validator = Validator::make($credentials, $rules);
        if( $validator->fails() )
        {
            $messages = $validator->errors()->messages();
            return $this->sendError('FAILED_DELETE_USER_TRADE', $messages);
        }

        if( !$request->trades || !is_array($request->trades) )
        {
            return $this->sendError('FAILED_DELETE_USER_TRADE', ['삭제할 내역이 없음']);
        }

        foreach($request->trades as $row)
        {
            if( $trade = Trade::find($row) )
            {
                $trade->delete();
            }
        }

        return $this->sendResponse([]);
    }

    public function order(Request $request, Order $order)
    {
        $data = compact('order');
        return $this->sendResponse($data, '');
    }

    public function orders(Request $request)
    {
        $keyword = trim($request->keyword);
        $query = Order::with('status', 'message');

        //날짜검색
        if( isset($request->sdate) && isset($request->edate) && !empty($request->sdate) && !empty($request->edate) )
        {
            $_edate = $request->edate . ' 23:59:59';
            $query->where([
                ['created_at', '>=', $request->sdate],
                ['created_at', '<=', $_edate]
            ]);
        }
        //주문상태검색
        if( isset($request->delivery_status) && $request->delivery_status > 0 )
        {
            $query->where('delivery_status', $request->delivery_status);
        }
        //조건검색
        if($keyword && $request->keyword_option)
        {
            //keyword_option
            //1.receiver
            //2.phone
            //3.delivery_code
            //4.id
            //5.model_id

            switch($request->keyword_option)
            {
                case 1:
                    $search_receivers = explode(",", $keyword);
                    //$query->where('receiver', 'LIKE', "%${keyword}%");
                    $query->where(function($q) use($search_receivers){
                        foreach($search_receivers as $receiver){
                            $receiver = trim($receiver);
                            $q->orWhere('receiver', 'LIKE', "%${receiver}%");
                        }
                    });
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
                    $query->whereHas('product', function($q) use($keyword){
                        $q->where('model_id', $keyword);
                    });
                    break;
                case 6:
                    $query->whereHas('user', function($q) use($keyword){
                        $q->where('name', $keyword);
                    });
                    break;
                default:
                    break;
            }
        }

        $delivery_status = empty($request->delivery_status) ? 0 : $request->delivery_status;
        $sdate           = $request->sdate;
        $edate           = $request->edate;
        $cnt             = $request->count;
        $keyword         = $request->keyword;
        $keyword_option  = $request->keyword_option;
        $keyword_options = $this->keyword_options;
        $order_by        = $request->order_by;
        $page_counts     = $this->page_counts;
        $count           = $request->count;

        $cnt = $request->count ? $page_counts[$request->count] : $page_counts[0];

        $desc = $order_by == 1 ? 'ASC' : 'DESC';
        $orders = $query->orderBy('id', $desc)->paginate($cnt);

        
        $total_price = $orders->sum(function($order) {
            $shop_type_id = $order->user->shop_type_id;
            return ($order->qty * $order->product->price($shop_type_id)) + $order->delivery_price - $order->minus_price;
        });

        $order_status = [];
        foreach(OrderStatus::all() as $status)
        {
            $order_status[$status->id] = $status->name;
        }

        $data = compact('orders', 'delivery_status', 'sdate', 'edate', 'keyword_options', 'keyword_option', 'keyword', 'order_by', 'page_counts', 'count', 'total_price', 'order_status');
        return $this->sendResponse($data, '');
    }

    public function updateOrder(Request $request, Order $order)
    {
        $credentials = $request->only('delivery_status', 'minus_price', 'delivery_provider', 'delivery_code', 'comment');
        $rules = [
            'delivery_status' => 'required'
        ];
        $messages = [
            'delivery_status.required'  => '필수값입니다.'
        ];
        $validator = Validator::make($credentials, $rules, $messages);
        if( $validator->fails() )
        {
            $messages = $validator->errors()->messages();
            return $this->sendError('FAILED_UPDATE_ORDER', $messages);
        }

        $order->delivery_status   = $request->delivery_status;
        $order->minus_price       = $request->minus_price;
        $order->delivery_provider = $request->delivery_provider;
        $order->delivery_code     = $request->delivery_code;
        $order->comment           = $request->comment;

        if( ! $order->save() )
        {
            return $this->sendError('FAILED_UPDATE_ORDER');
        }
        return $this->sendResponse([]);
    }

    public function updateOrders(Request $request)
    {
        $delivery_status = $request->delivery_status;
        $comment         = $request->comment;

        // var_dump($delivery_status);
        // var_dump(!!$is_deleted);
        // die();
        
        if( !$orders = $request->orders )
        {
            return $this->sendError('EMPTY_ORDERS');
        }
        else if(!$delivery_status && !$comment )
        {
            return $this->sendError('NOT_ENOUGHT_PARAM');
        }
        
        foreach($orders as $row)
        {
            // $order = Order::find($row['id']);
            // print_r($order);
            if( $order = Order::find($row['id']) )
            {
                if( $delivery_status )
                {
                    $order->delivery_status = $delivery_status;
                }
                if( $comment )
                {
                    $order->comment = $comment;
                }
                
                //order 업데이트시 상태가 반품완료일때 업체에게 사용가능적립금 추가!(2)
                if( $order->save() && $delivery_status == 7 )
                {
                    $trade = new Trade;
                    $trade->user_id = $order->user->id;
                    $trade->is_plus = true;
                    $trade->price   = $order->product->price($order->user->shop_type_id) * $order->qty;
                    $trade->content = "주문번호: " . $order->id . "번의 반품으로 인한 사용가능적립금 추가";
                    if( ! $trade->save() )
                    {
                        return $this->sendError('FAILED_UPDATE_ORDERS');
                    }
                }
            }
        }
        return $this->sendResponse([]);
    }

    public function deleteOrders(Request $request)
    {
        $credentials = $request->only('orders');
        $rules = [
            'orders' => 'required'
        ];
        $validator = Validator::make($credentials, $rules);
        if( $validator->fails() )
        {
            $messages = $validator->errors()->messages();
            return $this->sendError('EMPTY_ORDERS', $messages);
        }

        $orders = $request->orders;
        foreach($orders as $row)
        {
            $_order = json_decode($row);
            if( $order = Order::find($_order->id) )
            {
                if( ! $order->delete() )
                {
                    return $this->sendError('FAILED_DELETE_ORDERS');
                }
            }
        }

        return $this->sendResponse([]);
    }

    public function updateOrderReceiver(Request $request, Order $order)
    {
        $order->receiver = $request->receiver;
        $order->address = $request->address;
        $order->phone_1 = $request->phone_1;
        $order->phone_2 = $request->phone_2;
        $order->delivery_code = $request->delivery_code;
        if( !$order->save() )
        {
            return $this->sendError('FAILED_UPDATE_ORDER_RECEIVER');
        }

        return $this->sendResponse([]);
    }

    public function orderExport(Request $request)
    {
        //$this->checkAdmin();   

        $delivery_status = null;
        $sdate           = null;
        $edate           = null;
        $keyword_option  = $request->keyword_option;
        $keyword         = trim($request->keyword);
        $order_by        = $request->order_by;
        $count           = $request->count;

        if($request->delivery_status > 0 )
        {
            $delivery_status = $request->delivery_status;
        }

        if($request->sdate && $request->edate)
        {
            $sdate = $request->sdate;
            $edate = $request->edate;
        }

        return (new AdminOrderExport($delivery_status, $sdate, $edate, $keyword_option, $keyword, $order_by, $count))->download('관리자주문내역.xlsx');
    }


    public function products(Request $request)
    {
        $keyword_options = ['전체', '모델번호', '상품이름'];
        $keyword = trim($request->keyword);
        $keyword_option  = trim($request->keyword_option);

        $query = Product::query()->with("prices.shop_type");

        if( $request->keyword /*&& $request->keyword_option*/ )
        {
            // $option = $request->keyword_option == 1 ? 'model_id' : ($request->keyword_option == 2 ? 'name' : '');
            // $query->where($option, $keyword);
            $query->where("model_id", $keyword)->orWhere("name", $keyword);
        }

        $products = $query->paginate();

        $data = compact('products');
        return $this->sendResponse($data);
    }

    public function product(Request $request, Product $product)
    {
        //$product = with("prices.shop_type")
        $data = compact('product');
        return $this->sendResponse($data, '');
    }
}
