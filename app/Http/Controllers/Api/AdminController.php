<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
//use App\Http\Controllers\Api\BaseController as BaseController;

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

use App\Exports\AdminOrderExport;

class AdminController extends BaseController
{
    public function changePw(Request $request)
    {
        $credentials = $request->only('password', 'new_password');
        $rules = [
            //'password'  => 'required|min:6',
            'new_password'  => 'required|min:6',
        ];
        $messages = [
            //'password.required'  => '필수값입니다.',
            'new_password.required'  => '필수값입니다.',
        ];
        $validator = Validator::make($credentials, $rules, $messages);
        if ($validator->fails()) {
            $messages = $validator->errors()->messages();
            return $this->sendError('FAILED_CHANGE_PASSWORD', $messages);
        }

        $user = Auth::user();
        $user->password = bcrypt($request->new_password);

        if (!$user->save()) {
            return $this->sendError('FAILED_CHANGE_PASSWORD', "");
        }

        return $this->sendResponse([]);
    }

    public function shopTypes(Request $request)
    {
        $shopTypes = ShopType::with("status")->get();
        $data = compact('shopTypes');
        return $this->sendResponse($data, '');
    }
    public function deliveryProviders(Request $request)
    {
        $providers = DeliveryProvider::all();
        $delivery_providers = [];

        foreach ($providers as $provider) {
            $delivery_providers[$provider->id] = $provider->name;
        }

        $data = compact('providers', 'delivery_providers');
        return $this->sendResponse($data, '');
    }
    public function createDeliveryProviders(Request $request)
    {
        $credentials = $request->only('provider');
        $rules = [
            'provider' => "required"
        ];
        $messages = [
            'provider.required'  => '필수값입니다.',
        ];
        $validator = Validator::make($credentials, $rules, $messages);
        if ($validator->fails()) {
            $messages = $validator->errors()->messages();
            return $this->sendError('FAILED_CREATE_USER', $messages);
        }

        $provider = new DeliveryProvider;
        $provider->name = $request->provider;
        if (!$provider->save()) {
            return $this->sendError('FAILED_CREATE_Delivery_Provider');
        }

        $data = compact("provider");
        return $this->sendResponse($data);
    }

    public function createProductByExcel(Request $request)
    {
        $credentials = $request->only('excel');
        $rules = [
            'excel' => 'required|file|mimetypes:application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ];
        $validator = Validator::make($credentials, $rules);
        if ($validator->fails()) {
            $messages = $validator->errors()->messages();
            return $this->sendError('FAILED_UPLOAD_EXCEL', $messages);
        }

        $products = \Maatwebsite\Excel\Facades\Excel::toArray(new \App\Imports\ProductsImport,  $request->file('excel'));
        $_products = collect($products[0]);
        $products = collect();
        $shop_types = ShopType::where('type', '!=', 'Z')->get();

        DB::beginTransaction();

        foreach ($_products as $idx => $row) {
            if ($idx == 0) continue;

            if (!isset($row[0]) && !isset($row[1]))
                continue;

            $model_id = trim($row[0]);
            $name     = trim($row[1]);

            if (Product::where('model_id', $model_id)->first()) {
                continue;
            }

            $product = new Product;
            $product->model_id = $model_id;
            $product->name = $name;

            if (!$product->save()) {
                DB::rollback();
                return redirect()->back()->withErrors('DB ERROR: 상품등록 실패');
            }

            foreach ($shop_types as $idx => $type) {
                $price = 0;
                if (isset($row[$idx + 2])) {
                    $priceVal = $row[$idx + 2];

                    if (is_numeric($priceVal)) {
                        $price = trim($priceVal);
                    }
                }
                $product->prices()->create([
                    'shop_type_id' => $type->id,
                    'price'        => $price
                ]);
            }

            $products[] = $product;
        }

        DB::commit();

        if ($products->count() < 1) {
            return $this->sendError('FAILED_UPLOAD_EXCEL', 'ERROR: 상품등록 갯수가 없습니다.');
        }

        return $this->sendResponse([]);
    }

    public function users(Request $request)
    {
        $users = User::where('is_admin', false)->paginate(100);
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
        $credentials = $request->only('name', 'email', 'shop_type_id');
        $rules = [
            'name' => 'required',
            'email' => 'required',
            'shop_type_id' => 'required',
        ];
        $messages = [
            'name.required'  => '필수값입니다.',
            'email.required'  => '필수값입니다.',
            'shop_type_id.required'  => '필수값입니다.'
        ];
        $validator = Validator::make($credentials, $rules, $messages);
        if ($validator->fails()) {
            $messages = $validator->errors()->messages();
            return $this->sendError('FAILED_UPDATE_USER', $messages);
        }

        $user->name = $request->name;
        $user->email = $request->email;
        $user->shop_type_id = $request->shop_type_id;

        if (!$user->save()) {
            return $this->sendError('FAILED_UPDATE_USER');
        }
        return $this->sendResponse([]);
    }

    public function createUser(Request $request)
    {
        $credentials = $request->only('name', 'email', 'password', 'shop_type_id');
        $rules = [
            'name'      => "required|unique:users",
            'email'     => 'required|unique:users',
            'password'  => 'required|min:6',
            'shop_type_id' => 'required',
        ];
        $messages = [
            'name.required'  => '거래처 이름은 필수값입니다.',
            'name.unique'    => '이미 존재하는 거래처이름입니다.',
            'email.required' => '이메일은 필수값입니다.',
            'email.unique'   => '이미 존재하는 이메일입니다.',
            'password.required' => '비밀번호는 필수값 입니다.',
            'password.min' => '비밀번호는 6글자 이상 입력해주세요!',
            'shop_type_id.required'  => '필수값입니다.'
        ];
        $validator = Validator::make($credentials, $rules, $messages);
        if ($validator->fails()) {
            $messages = $validator->errors()->messages();
            return $this->sendError('FAILED_CREATE_USER', $messages);
        }

        $user = new User;
        $user->user_id      = $request->name;
        $user->name         = $request->name;
        $user->email        = $request->email;
        $user->shop_type_id = $request->shop_type_id;
        $user->password     = bcrypt($request->password);

        if (!$user->save()) {
            return $this->sendError('FAILED_CREATE_USER');
        }
        $data = compact('user');
        return $this->sendResponse($data);
    }

    public function deleteUser(Request $request, User $user)
    {
        if (!$user->delete()) {
            return $this->sendError('FAILED_DELETE_USER', '유저삭제실패');
        }
        return $this->sendResponse([]);
    }

    public function userTrades(Request $request, User $user)
    {
        $count = 50;
        $page = $request->page;

        $read_trades = Trade::where('user_id', $user->id)
            ->orderByDesc('id')
            ->paginate($count);

        //수정된 부분
        $all_trades = Trade::where('user_id', $user->id)->get();
        $trades = [];
        foreach ($all_trades as $idx => $trade) {
            $trade->plus = $trade->is_plus ? $trade->price : 0;
            $trade->minus = $trade->is_plus ? 0 : $trade->price;
            $trade->change = $trade->plus - $trade->minus;

            if ($idx > 0) {
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
        if ($validator->fails()) {
            $messages = $validator->errors()->messages();
            return $this->sendError('FAILED_ADD_USER_TRADE', $messages);
        }

        $trade = new Trade;
        $trade->user_id = $user->id;
        $trade->is_plus = $request->is_plus;
        $trade->price   = (int) $request->price;
        $trade->content = $request->content;

        if (!$trade->save()) {
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
        if ($validator->fails()) {
            $messages = $validator->errors()->messages();
            return $this->sendError('FAILED_DELETE_USER_TRADE', $messages);
        }

        if (!$request->trades || !is_array($request->trades)) {
            return $this->sendError('FAILED_DELETE_USER_TRADE', ['삭제할 내역이 없음']);
        }

        foreach ($request->trades as $row) {
            if ($trade = Trade::find($row)) {
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
        $query = Order::with('status', 'message', 'deliveryProvider');

        //날짜검색
        if (isset($request->sdate) && isset($request->edate) && !empty($request->sdate) && !empty($request->edate)) {
            $_edate = $request->edate . ' 23:59:59';
            $query->where([
                ['created_at', '>=', $request->sdate],
                ['created_at', '<=', $_edate]
            ]);
        }
        //주문상태검색
        if (isset($request->delivery_status) && $request->delivery_status > 0) {
            $query->where('delivery_status', $request->delivery_status);
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
                    $search_receivers = explode(",", $keyword);
                    //$query->where('receiver', 'LIKE', "%${keyword}%");
                    $query->where(function ($q) use ($search_receivers) {
                        foreach ($search_receivers as $receiver) {
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
                    $query->whereHas('product', function ($q) use ($keyword) {
                        $q->where('model_id', $keyword);
                    });
                    break;
                case 6:
                    $query->whereHas('user', function ($q) use ($keyword) {
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
        $orders = $query
            ->orderBy('id', $desc)
            //->append('product_price')
            ->paginate($cnt);


        $total_price = $orders->sum(function ($order) {
            $shop_type_id = $order->user->shop_type_id;
            return ($order->qty * $order->product->price($shop_type_id)) + $order->delivery_price - $order->minus_price;
        });

        $order_status = [];
        foreach (OrderStatus::all() as $status) {
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
        if ($validator->fails()) {
            $messages = $validator->errors()->messages();
            return $this->sendError('FAILED_UPDATE_ORDER', $messages);
        }

        $order->delivery_status   = $request->delivery_status;
        $order->minus_price       = $request->minus_price;
        $order->delivery_provider = $request->delivery_provider;
        $order->delivery_code     = $request->delivery_code;
        $order->comment           = $request->comment;

        if (!$order->save()) {
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

        if (!$orders = $request->orders) {
            return $this->sendError('EMPTY_ORDERS');
        } else if (!$delivery_status && !$comment) {
            return $this->sendError('NOT_ENOUGHT_PARAM');
        }

        foreach ($orders as $row) {
            // $order = Order::find($row['id']);
            // print_r($order);
            if ($order = Order::find($row['id'])) {
                if ($delivery_status) {
                    $order->delivery_status = $delivery_status;
                }
                if ($comment) {
                    $order->comment = $comment;
                }

                //order 업데이트시 상태가 반품완료일때 업체에게 사용가능적립금 추가!(2)
                if ($order->save() && $delivery_status == 7) {
                    $trade = new Trade;
                    $trade->user_id = $order->user->id;
                    $trade->is_plus = true;
                    $trade->price   = $request->refund; //$order->product->price($order->user->shop_type_id) * $order->qty;
                    $trade->content = "주문번호: " . $order->id . "번 수취인:" . $order->receiver . " 의 반품으로 인한 사용가능적립금 추가";
                    if (!$trade->save()) {
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
        if ($validator->fails()) {
            $messages = $validator->errors()->messages();
            return $this->sendError('EMPTY_ORDERS', $messages);
        }

        $orders = $request->orders;
        foreach ($orders as $row) {
            //print_r($row);
            // $_order = json_decode($row);
            if ($order = Order::find($row['id'])) {
                if (!$order->delete()) {
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
        $order->zipcode = $request->zipcode;
        if (!$order->save()) {
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

        if ($request->delivery_status > 0) {
            $delivery_status = $request->delivery_status;
        }

        if ($request->sdate && $request->edate) {
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

        if ($request->keyword /*&& $request->keyword_option*/) {
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
        $product = Product::with("prices.shop_type")->find($product->id);
        $data = compact('product');
        return $this->sendResponse($data);
    }

    public function createProduct(Request $request)
    {
        $credentials = $request->only('model_id', 'name');
        $rules = [
            'model_id' => 'required|unique:products',
            'name'     => 'required',
            'prices.*' => 'required|integer'
        ];
        $messages = [
            'model_id.unique' => '모델아이디가 이미 존재합니다.'
        ];
        $validator = Validator::make($credentials, $rules, $messages);
        if ($validator->fails()) {
            $messages = $validator->errors()->messages();
            return $this->sendError('FAILED_CREATE_PRODUCT', $messages);
        }

        $product = new Product;
        $product->model_id = $request->model_id;
        $product->name     = $request->name;

        if (!$product->save()) {
            return $this->sendError('FAILED_CREATE_PRODUCT');
        }

        foreach ($request->prices as $row) {
            $product->prices()->create([
                'shop_type_id' => $row['shop_type']['id'],
                'price'        => $row['price'] ? $row['price'] : 0
            ]);
        }

        $data = compact('product');
        return $this->sendResponse($data);
    }

    public function updateProduct(Request $request, Product $product)
    {
        $credentials = $request->only('name');
        $rules = [
            'name'    => 'required',
            'price.*' => 'required|integer'
        ];
        $validator = Validator::make($credentials, $rules);
        if ($validator->fails()) {
            $messages = $validator->errors()->messages();
            return $this->sendError('FAILED_UPDATE_PRODUCT', $messages);
        }

        $product->name = $request->name;

        if (!$product->save()) {
            return $this->sendError('FAILED_UPDATE_PRODUCT');
        }

        foreach ($request->prices as $row) {
            if ($priductPrice = $product->prices->where('shop_type_id', $row['shop_type_id'])->first()) {
                $priductPrice->update([
                    'price' => $row['price']
                ]);
            } else {
                $product->prices()->create([
                    'shop_type_id' => $row['shop_type_id'],
                    'price'        => $row['price']
                ]);
            }
        }

        return $this->sendResponse([]);
    }

    public function deleteProduct(Request $request, Product $product)
    {
        if (!$product->delete()) {
            return $this->sendError('FAILED_DELETE_PRODUCT');
        }
        return $this->sendResponse([]);
    }

    public function orderImport(Request $request)
    {
        $credentials = $request->only('excel');
        $rules = [
            'excel' => 'required|file|mimetypes:application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ];
        $validator = Validator::make($credentials, $rules);
        if ($validator->fails()) {
            $messages = $validator->errors()->messages();
            return $this->sendError('PARAM_ERROR', $messages);
        }

        $orders = \Maatwebsite\Excel\Facades\Excel::toArray(new \App\Imports\OrdersImport,  $request->file('excel'));
        $_orders = collect($orders[0]);
        $orders = collect();

        //dd($_orders);

        DB::beginTransaction();

        foreach ($_orders as $idx => $row) {
            if ($idx == 0) continue;

            if (!$order_id = $row[2]) continue;
            if (!$order = Order::find($order_id)) continue;

            //delivery_provider row[0]
            //delivery_code row[1]
            //delivery_message row[15]

            if (!$delivery_provider = DeliveryProvider::where('name', trim($row[0]))->first()) continue;

            $order->delivery_provider = $delivery_provider->id;
            $order->delivery_code     = trim($row[1]);

            if ($order->delivery_provider && $order->delivery_code) {
                $order->delivery_status = 3;
            }

            if ($delivery_message = isset($row[15])) {
                $order->delivery_message = trim($row[15]);
            }

            if (!$order->save()) {
                DB::rollback();
                return $this->sendError('ERROR: DB 업데이트 실패');
            }

            $orders[] = $order;
        }

        DB::commit();

        if ($orders->count() < 1) {
            return $this->sendError('ERROR: 상품등록 갯수가 없습니다.');
        }

        return $this->sendResponse([]);
    }


    public function createShopType(Request $request)
    {
        $credentials = $request->only('type', 'delivery_price', 'delivery_status');
        $rules = [
            'type'            => 'required',
            'delivery_price'  => 'required|integer',
            'delivery_status' => 'required|integer',
        ];
        $validator = Validator::make($credentials, $rules);
        if ($validator->fails()) {
            $messages = $validator->errors()->messages();
            return $this->sendError('PARAM_ERROR', $messages);
        }

        // $shopType = new ShopType;
        // $shopType->delivery_price = 0; //$request->delivery_price;

        if (!$shopType = ShopType::create($credentials)) {
            return $this->sendError('CREATE_SHOP_TYPE_ERROR');
        }

        $shopType = ShopType::with("status")->find($shopType->id);

        $data = compact('shopType');
        return $this->sendResponse($data);
    }

    public function updateShopType(Request $request, ShopType $shopType)
    {
        $credentials = $request->only('delivery_price');
        $rules = [
            'delivery_price' => 'required|integer',
        ];
        $validator = Validator::make($credentials, $rules);
        if ($validator->fails()) {
            $messages = $validator->errors()->messages();
            return $this->sendError('PARAM_ERROR', $messages);
        }

        $shopType->delivery_price = $request->delivery_price;

        if (!$shopType->save()) {
            return $this->sendError('UPDATE_SHOP_TYPE_ERROR');
        }

        return $this->sendResponse([]);
    }

    public function deleteShopType(Request $request, ShopType $shopType)
    {
        if (!$shopType->delete()) {
            return $this->sendError('DELETE_SHOP_TYPE_ERROR');
        }

        return $this->sendResponse([]);
    }

    public function notices(Request $request)
    {
        $notices = Notice::paginate();

        $data = compact('notices');
        return $this->sendResponse($data);
    }

    public function notice(Request $request, Notice $notice)
    {
        $data = compact('notice');
        return $this->sendResponse($data);
    }

    public function createNotice(Request $request)
    {
        $credentials = $request->only('content');
        $rules = [
            'content' => 'required',
        ];
        $validator = Validator::make($credentials, $rules);
        if ($validator->fails()) {
            $messages = $validator->errors()->messages();
            return $this->sendError('PARAM_ERROR', $messages);
        }

        if (!$notice = Notice::create($credentials)) {
            return $this->sendError('CREATE_NOTICE_ERROR');
        }

        $data = compact('notice');
        return $this->sendResponse($data);
    }

    public function updateNotice(Request $request, Notice $notice)
    {
        $credentials = $request->only('content');
        $rules = [
            'content' => 'required',
            //'is_active' => "bool"
        ];
        $validator = Validator::make($credentials, $rules);
        if ($validator->fails()) {
            $messages = $validator->errors()->messages();
            return $this->sendError('PARAM_ERROR', $messages);
        }

        // $notice->content = $request->content;
        // $notice->is_active = $request->is_active;

        if (!$notice->update($credentials)) {
            return $this->sendError('UPDATE_NOTICE_ERROR');
        }

        $data = compact('notice');
        return $this->sendResponse($data);
    }

    public function uploadNoticeImage(Request $request)
    {
        $credentials = $request->only('image');
        $rules = [
            'image' => 'required|image|mimes:jpeg,jpg,png,gif',
        ];
        $validator = Validator::make($credentials, $rules);
        if ($validator->fails()) {
            $messages = $validator->errors()->messages();
            return $this->sendError('FAILED_UPLOAD_EXCEL', $messages);
        }

        $uploadedFile = $request->file("image");
        $filename = time() . "_" . $uploadedFile->getClientOriginalName();

        $imageUrl = Storage::disk('public')->putFileAs(
            '/upload',
            $uploadedFile,
            $filename
        );
        $imageUrl = "/storage/" . $imageUrl;
        $data = compact('imageUrl');
        return $this->sendResponse($data);
    }

    public function rawOrders(Request $request)
    {
        $count = $request->count ? $request->count : 100;
        $query = Order::query();

        $_edate = $request->edate . ' 23:59:59';
        $query->where([
            ['created_at', '>=', $request->sdate],
            ['created_at', '<=', $_edate]
        ]);

        //$orders = $query->limit($count)->get();
        $orders = $query->orderBy('id', "desc")->paginate($count);
        $data = compact('orders');
        return $this->sendResponse($data, '');
    }

    public function deleteAllOrders(Request $request)
    {
        $credentials = $request->only('sdate', 'edate');
        $rules = [
            'sdate' => 'required',
            'edate' => 'required',
        ];
        $validator = Validator::make($credentials, $rules);
        if ($validator->fails()) {
            $messages = $validator->errors()->messages();
            return $this->sendError('FAILED_DELETE_DB_ORDERS', $messages);
        }

        //db backup
        Artisan::call('db:backup');


        $query = Order::query();
        $_edate = $request->edate . ' 23:59:59';
        $query->where([
            ['created_at', '>=', $request->sdate],
            ['created_at', '<=', $_edate]
        ]);
        if (!$query->delete()) {
            return $this->sendError('FAILED_DELETE_DB_ORDERS');
        }


        return $this->sendResponse([]);
    }
}
