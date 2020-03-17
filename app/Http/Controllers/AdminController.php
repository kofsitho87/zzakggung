<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use Validator;
use DB;

use App\Model\User;
use App\Model\Order;
use App\Model\OrderStatus;
use App\Model\Product;
use App\Model\ShopType;
use App\Model\DeliveryProvider;
use App\Model\Trade;

use App\Exports\OrdersExport;
use App\Exports\AdminOrderExport;

class AdminController extends Controller
{
    public $page_counts = [
        15,
        30,
        50,
        100,
        200,
        500,
        1000
    ];
    public $keyword_options = [
        '전체',
        '받는분',
        '받는분의 전화',
        '운송장번호',
        '주문번호',
        '모델번호',
        '거래처',
    ];

    public function __construct(Request $request)
    {
    }

    public function getShopTypes()
    {
        $shop_types = [];
        foreach (ShopType::where('type', '!=', 'Z')->get() as $type) {
            $shop_types[$type->id] = $type->type;
        }
        return $shop_types;
    }

    public function checkAdmin()
    {
        $user = Auth::user();

        if (!$user->is_admin) {
            abort(401, 'This action is unauthorized.');
        }
    }

    public function productsExample(Request $request)
    {
        $file_path = storage_path('app/example/admin_product.xlsx');
        return response()->download($file_path, '상품업로드샘플.xlsx');
    }

    public function index(Request $request)
    {
        return redirect('admin/users');
    }

    public function users(Request $request)
    {
        $this->checkAdmin();

        $users = User::where('is_admin', false)->paginate(50);
        $data = compact('users');
        return view('admin_users', $data);
    }

    public function user(Request $request, User $user)
    {
        $this->checkAdmin();

        $shop_types = [];
        foreach (ShopType::where('type', '!=', 'Z')->get() as $type) {
            $shop_types[$type->id] = $type->type;
        }

        $data = compact('user', 'shop_types');
        return view('admin_user', $data);
    }

    public function deleteUser(Request $request, User $user)
    {
        $this->checkAdmin();

        if (!$user->delete()) {
            return redirect()->back()->withErrors(['유저삭제실패']);
        }

        return redirect('/admin/users');
    }

    public function updateUser(Request $request, User $user)
    {
        $this->checkAdmin();

        $credentials = $request->only('name', 'email', 'shop_type');
        $rules = [
            'name' => "required|unique:users,name," . $user->id,
            'email' => 'required|unique:users,email,' . $user->id,
            'shop_type' => 'required'
        ];
        $messages = [
            'name.required'  => '거래처 이름은 필수값입니다.',
            'name.unique'    => '이미 존재하는 거래처이름입니다.',
            'email.required' => '아이디는 필수값입니다.',
            'email.unique'   => '이미 존재하는 아이디입니다.',
        ];
        $validator = Validator::make($credentials, $rules, $messages);
        if ($validator->fails()) {
            $messages = $validator->errors()->messages();
            return redirect()->back()->withErrors($validator->errors());
        }

        $user->name         = $request->name;
        $user->email        = $request->email;
        $user->shop_type_id = $request->shop_type;

        if (!$user->save()) {
            return redirect()->back()->withErrors('DB_ERROR: 유저 업데이트 실패');
        }

        return redirect()->back()->with(['success' => true]);
    }

    public function createUserView(Request $request)
    {
        $this->checkAdmin();

        $shop_types = $this->getShopTypes();

        $data = compact('shop_types');

        return view('admin_user_create', $data);
    }

    public function createUser(Request $request)
    {
        $this->checkAdmin();

        $credentials = $request->only('name', 'email', 'shop_type', 'password');
        $rules = [
            'name'      => "required|unique:users",
            'email'     => 'required|unique:users',
            //'shop_type' => 'required|in:A,B',
            'password'  => 'required|min:6'
        ];
        $messages = [
            'name.required'  => '거래처 이름은 필수값입니다.',
            'name.unique'    => '이미 존재하는 거래처이름입니다.',
            'email.required' => '이메일은 필수값입니다.',
            'email.unique'   => '이미 존재하는 이메일입니다.',
            'password.required' => '비밀번호는 필수값 입니다.',
            'password.min' => '비밀번호는 6글자 이상 입력해주세요!',
        ];
        $validator = Validator::make($credentials, $rules, $messages);
        if ($validator->fails()) {
            $messages = $validator->errors()->messages();
            return redirect()->back()->withErrors($validator->errors())->withInput($request->except('password'));
        }

        $user = new User;
        $user->user_id      = $request->name;
        $user->name         = $request->name;
        $user->email        = $request->email;
        $user->shop_type_id = $request->shop_type;
        $user->password     = bcrypt($request->password);

        if (!$user->save()) {
            return redirect()->back()->withErrors('DB_ERROR: 유저 업데이트 실패')->withInput($request->except('password'));
        }

        return redirect()->back()->with(['success' => true]);
    }

    public function orders(Request $request)
    {
        $this->checkAdmin();

        $keyword = trim($request->keyword);
        $query = Order::with('status');

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
        $orders = $query->orderBy('id', $desc)->paginate($cnt);


        $total_price = $orders->sum(function ($order) {
            $shop_type_id = $order->user->shop_type_id;
            return ($order->qty * $order->product->price($shop_type_id)) + $order->delivery_price - $order->minus_price;
        });

        $order_status = [];
        foreach (OrderStatus::all() as $status) {
            $order_status[$status->id] = $status->name;
        }


        $data = compact('orders', 'delivery_status', 'sdate', 'edate', 'keyword_options', 'keyword_option', 'keyword', 'order_by', 'page_counts', 'count', 'total_price', 'order_status');

        return view('admin_orders', $data);
    }

    public function order(Request $request, Order $order)
    {
        $this->checkAdmin();

        $orderStatus = \App\Model\OrderStatus::all();
        $order_status = [];
        $delivery_providers = ['없음'];

        foreach ($orderStatus as $status) {
            $order_status[$status->id] = $status->name;
        }

        foreach (\App\Model\DeliveryProvider::all() as $provider) {
            $delivery_providers[$provider->id] = $provider->name;
        }

        $data = compact('order', 'order_status', 'delivery_providers');
        return view('admin_order', $data);
    }

    public function updateOrders(Request $request)
    {
        $delivery_status = $request->delivery_status;
        $comment         = $request->comment;
        $is_deleted      = $request->is_deleted;

        // var_dump($delivery_status);
        // var_dump(!!$is_deleted);
        // die();

        if (!$orders = $request->orders) {
            return redirect()->back();
        } else if (!$delivery_status && !$comment && !$is_deleted) {
            return redirect()->back()->withErrors('업데이트할 정보가 없습니다.');
        }

        $msg = "";
        foreach ($orders as $order_id => $value) {
            if ($order = Order::find($order_id)) {
                if ($is_deleted) {
                    $order->delete();
                    $msg = "주문내역 삭제가 완료되었습니다.";
                } else {
                    if ($delivery_status) {
                        $order->delivery_status = $delivery_status;
                    }
                    if ($comment) {
                        $order->comment = $comment;
                    }
                    $msg = "주문내역 업데이트가 완료되었습니다.";

                    //order 업데이트시 상태가 반품완료일때 업체에게 사용가능적립금 추가!(2)
                    if ($order->save() && $delivery_status == 7) {
                        $trade = new Trade;
                        $trade->user_id = $order->user_id;
                        $trade->is_plus = true;
                        $trade->price   = $order->product->price($order->user->shop_type_id) * $order->qty;
                        $trade->content = "주문번호: " . $order->id . "번 수취인:" . $order->receiver . " 의 반품으로 인한 사용가능적립금 추가";
                        if (!$trade->save()) {
                            return redirect()->back()->withErrors(['DB ERROR : 반품완료일때 업체에게 사용가능적립금 추가 실패']);
                        }
                    }
                }
            }
        }

        return redirect()->back()->with(['success' => true, 'msg' => $msg]);
    }

    public function updateOrder(Request $request, Order $order)
    {
        $this->checkAdmin();

        $credentials = $request->only('delivery_status', 'minus_price');
        $rules = [
            'delivery_status' => 'required',
            'minus_price'     => 'integer',
        ];
        $validator = Validator::make($credentials, $rules);
        if ($validator->fails()) {
            $messages = $validator->errors()->messages();
            return redirect()->back()->withErrors($validator->errors());
        }

        //반품완료일때 차감금액을 같이 업데이트한다
        // if( $request->delivery_status == 7 )
        // {
        //     $order->minus_price = $request->minus_price;
        // }

        //??
        $order->minus_price = $request->minus_price;

        $order->delivery_status = $request->delivery_status;
        $order->delivery_code   = $request->delivery_code;
        $order->comment         = $request->comment;

        if ($delivery_provider = $request->delivery_provider) {
            if ($delivery_provider = DeliveryProvider::find($delivery_provider)) {
                $order->delivery_provider = $delivery_provider->id;
            }
        } else {
            $order->delivery_provider = null;
        }

        if (!$order->save()) {
            return redirect()->back()->withErrors('DB ERROR: 주문내역 업데이트 실패');
        }

        return redirect()->back()->with(['success' => true]);
    }

    public function updateOrderStatus(Request $request, Order $order, OrderStatus $status)
    {
        $this->checkAdmin();

        $order->delivery_status = $status->id;
        if (!$order->save()) {
            return redirect()->back()->withErrors('DB ERROR: 주문정보 업데이트 실패');
        }

        return redirect()->back()->with(['success' => true]);
    }

    public function updateOrderReceiver(Request $request, Order $order)
    {
        $this->checkAdmin();
        $order->receiver = $request->receiver;
        $order->address = $request->address;
        $order->phone_1 = $request->phone_1;
        $order->phone_2 = $request->phone_2;
        $order->delivery_code = $request->delivery_code;
        if (!$order->save()) {
            return redirect()->back()->withErrors('DB ERROR: 주문정보 업데이트 실패');
        }

        return redirect()->back()->with(['success' => true]);
    }

    public function deleteOrder(Request $request, Order $order)
    {
        $this->checkAdmin();

        if (!$order->delete()) {
            return redirect()->back()->withErrors('DB ERROR: 주문내역 삭제 실패');
        }

        return redirect('/admin/orders')->with(['success' => true, 'msg' => '주문내역 삭제 성공']);
    }

    public function products(Request $request)
    {
        $this->checkAdmin();

        $keyword_options = ['전체', '모델번호', '상품이름'];
        $keyword = trim($request->keyword);
        $keyword_option  = trim($request->keyword_option);

        $query = Product::query();

        if ($request->keyword && $request->keyword_option) {
            $option = $request->keyword_option == 1 ? 'model_id' : ($request->keyword_option == 2 ? 'name' : '');
            $query->where($option, $keyword);
        } else {
            $keyword = '';
        }

        $products = $query->paginate();

        $data = compact('products', 'keyword', 'keyword_options', 'keyword_option');
        return view('admin_products', $data);
    }

    public function product(Request $request, Product $product)
    {
        $this->checkAdmin();
        $shop_types = ShopType::where('type', '!=', 'Z')->get();
        $data = compact('product', 'shop_types');
        return view('admin_product', $data);
    }

    public function deleteProduct(Request $request, Product $product)
    {
        $this->checkAdmin();

        if (!$product->delete()) {
            return redirect()->back()->withErrors('DB ERROR: 상품정보 삭제 실패');
        }

        return redirect('/admin/products');
    }

    public function updateProduct(Request $request, Product $product)
    {
        $this->checkAdmin();

        $credentials = $request->only('name');
        $rules = [
            'name'    => 'required',
            'price.*' => 'required|integer'
        ];
        $validator = Validator::make($credentials, $rules);
        if ($validator->fails()) {
            $messages = $validator->errors()->messages();
            return redirect()->back()->withErrors($validator->errors());
        }

        $product->name = $request->name;

        if (!$product->save()) {
            return redirect()->back()->withErrors('DB ERROR: 상품정보 업데이트 실패');
        }

        foreach ($request->prices as $typeId => $price) {
            if ($priductPrice = $product->prices->where('shop_type_id', $typeId)->first()) {
                $priductPrice->update([
                    'price' => $price
                ]);
            } else {
                $product->prices()->create([
                    'shop_type_id' => $typeId,
                    'price'        => $price
                ]);
            }
        }

        return redirect()->back()->with(['success' => true]);
    }

    public function createProductView(Request $request)
    {
        $this->checkAdmin();

        // $credentials = $request->only('model_id', 'name');
        // $rules = [
        //     'model_id' => 'required|unique:products',
        //     'name'     => 'required',
        //     'price.*'  => 'required|integer'
        // ];
        // $validator = Validator::make($credentials, $rules);
        // if( $validator->fails() )
        // {
        //     $messages = $validator->errors()->messages();
        //     return redirect()->back()->withErrors( $validator->errors() );
        // }

        $shop_types = ShopType::where('type', '!=', 'Z')->get();
        $data = compact('shop_types');

        return view('admin_product_create', $data);
    }

    public function createProduct(Request $request)
    {
        $this->checkAdmin();

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
            return redirect()->back()->withErrors($validator->errors())->withInput();
        }

        $product = new Product;
        $product->model_id = $request->model_id;
        $product->name     = $request->name;

        if (!$product->save()) {
            return redirect()->back()->withErrors('DB ERROR: 상품등록 실패');
        }

        foreach ($request->prices as $typeId => $price) {
            $product->prices()->create([
                'shop_type_id' => $typeId,
                'price'        => $price ? $price : 0
            ]);
        }


        return redirect()->back()->with(['success' => true]);
    }

    public function createProductByExcel(Request $request)
    {
        $this->checkAdmin();

        $credentials = $request->only('excel');
        $rules = [
            'excel' => 'required|file|mimetypes:application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ];
        $validator = Validator::make($credentials, $rules);
        if ($validator->fails()) {
            $messages = $validator->errors()->messages();
            return redirect()->back()->withErrors($validator->errors());
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
            return redirect()->back()->withErrors('ERROR: 상품등록 갯수가 없습니다.');
        }

        return redirect()->back()->with(['success' => true]);
    }

    public function orderImport(Request $request)
    {
        $this->checkAdmin();

        $credentials = $request->only('excel');
        $rules = [
            'excel' => 'required|file|mimetypes:application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ];
        $validator = Validator::make($credentials, $rules);
        if ($validator->fails()) {
            $messages = $validator->errors()->messages();
            return redirect()->back()->withErrors($validator->errors());
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
                return redirect()->back()->withErrors('ERROR: DB 업데이트 실패');
            }

            $orders[] = $order;
        }

        DB::commit();

        if ($orders->count() < 1) {
            return redirect()->back()->withErrors('ERROR: 상품등록 갯수가 없습니다.');
        }

        return redirect()->back()->with(['success' => true]);
    }

    public function orderExport(Request $request)
    {
        $this->checkAdmin();

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

        return (new AdminOrderExport($delivery_status, $sdate, $edate, $keyword_option, $keyword, $order_by))->download('관리자주문내역.xlsx');
    }

    public function shopTypes(Request $request)
    {
        $this->checkAdmin();

        $order_status = [];
        foreach (OrderStatus::all() as $status) {
            $order_status[$status->id] = $status->name;
        }

        $shopTypes = ShopType::where('type', '!=', 'Z')->get();

        $data = compact('shopTypes', 'order_status');
        return view('admin_shop_types', $data);
    }

    public function updateShopTypes(Request $request)
    {
        $this->checkAdmin();

        foreach ($request->delivery_price as $id => $price) {
            if ($shopType = ShopType::find($id)) {
                $shopType->delivery_price = $price;
                $shopType->save();
            }
        }

        return redirect()->back()->with(['success' => true]);
    }

    public function createShopTypes(Request $request)
    {
        $this->checkAdmin();

        $credentials = $request->only('type', 'delivery_price', 'delivery_status');
        $rules = [
            'type'            => 'required',
            'delivery_price'  => 'required|integer',
            'delivery_status' => 'required|integer',
        ];
        $validator = Validator::make($credentials, $rules);
        if ($validator->fails()) {
            $messages = $validator->errors()->messages();
            return redirect()->back()->withErrors($validator->errors());
        }

        if (!ShopType::create($credentials)) {
            return redirect()->back()->withErrors(['DB ERROR : 배송사 생성실패']);
        }

        return redirect()->back()->with(['success' => true]);
    }

    public function deleteShopType(Request $request, ShopType $shop)
    {
        $this->checkAdmin();

        if (User::whereHas('shop_type', function ($q) use ($shop) {
            $q->where('shop_type_id', $shop->id);
        })->first()) {
            return redirect()->back()->withErrors('해당타입의 거래처가 있어 삭제할수 없습니다.');
        } else if (Order::whereHas('user', function ($q) use ($shop) {
            $q->where('shop_type_id', $shop->id);
        })->first()) {
            return redirect()->back()->withErrors('해당타입의 주문건이 있어 삭제할수 없습니다.');
        }

        if (!$shop->delete()) {
            return redirect()->back()->withErrors('ERROR: 샵타입 삭제 실패');
        }

        return redirect()->back()->with(['success' => true]);
    }

    public function deliveryProviders(Request $request)
    {
        $this->checkAdmin();

        $providers = DeliveryProvider::all();
        $delivery_providers = [];

        foreach ($providers as $provider) {
            $delivery_providers[$provider->id] = $provider->name;
        }

        $data = compact('providers', 'delivery_providers');
        return view('admin_delivery_providers', $data);
    }

    public function createDeliveryProvider(Request $request)
    {
        $this->checkAdmin();

        $credentials = $request->only('provider');
        $rules = [
            'provider' => 'required',
        ];
        $validator = Validator::make($credentials, $rules);
        if ($validator->fails()) {
            $messages = $validator->errors()->messages();
            return redirect()->back()->withErrors($validator->errors());
        }

        $provider = new DeliveryProvider;
        $provider->name = $request->provider;
        if (!$provider->save()) {
            return redirect()->back()->withErrors(['DB ERROR : 배송사 생성실패']);
        }

        return redirect()->back()->with(['success' => true]);
    }

    public function userTrades(Request $request, User $user)
    {
        $this->checkAdmin();
        //임시카운트 100->10으로 테스트
        $count = 100;
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
        return view('admin_user_trades', $data);
    }

    public function createUserTrades(Request $request, User $user)
    {
        $this->checkAdmin();

        $credentials = $request->only('is_plus', 'price', 'content');
        $rules = [
            'is_plus'    => 'required|bool',
            'price'      => 'required|integer',
            'content'    => 'required'
        ];
        $validator = Validator::make($credentials, $rules);
        if ($validator->fails()) {
            $messages = $validator->errors()->messages();
            return redirect()->back()->withErrors($validator->errors());
        }

        $trade = new Trade;

        $trade->user_id = $user->id;
        $trade->is_plus = $request->is_plus;
        $trade->price   = $request->price;
        $trade->content = $request->content;

        //dd($trade->getAttributes());

        if (!$trade->save()) {
            return redirect()->back()->withErrors(['DB ERROR : 생성실패']);
        }

        return redirect()->back()->with(['success' => true]);
    }

    public function deleteUserTrades(Request $request, User $user)
    {
        $this->checkAdmin();

        $credentials = $request->only('trades');
        $rules = [
            'trades.*'    => 'required',
        ];
        $validator = Validator::make($credentials, $rules);
        if ($validator->fails()) {
            $messages = $validator->errors()->messages();
            return redirect()->back()->withErrors($validator->errors());
        }

        //dd($request->trades);

        if (!$request->trades || !is_array($request->trades)) {
            return redirect()->back()->withErrors(['삭제할 내역이 없음']);
        }

        foreach ($request->trades as $idx => $row) {
            if ($trade = Trade::find($idx)) {
                $trade->delete();
            }
        }

        return redirect()->back()->with(['success' => true]);
    }
}
