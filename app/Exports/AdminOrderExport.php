<?php

namespace App\Exports;

use App\Model\Order;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\Exportable;

class AdminOrderExport implements FromQuery, WithMapping, WithHeadings
{
    use Exportable;

    private $delivery_status;
    private $sdate;
    private $edate;

    private $keyword_option;
    private $keyword;
    private $order_by;
    private $count;

    public function __construct($delivery_status, $sdate, $edate, $keyword_option, $keyword, $order_by = null, $count = 15)
    {
        $this->delivery_status = $delivery_status;
        $this->sdate = $sdate;
        $this->edate = $edate;

        $this->keyword_option = $keyword_option;
        $this->keyword        = $keyword;
        $this->order_by       = $order_by;
        $this->count          = $count;
    }

    public function headings(): array
    {
        return [
            '택배사',
            '송장번호',
            '주문번호',
            '모델번호',
            '제품명',
            '옵션',
            '수량',

            '상품가격',
            '배송비',

            '거래처명',
            '수령자',
            '전화번호1(필수)',
            '전화번호2(선택)',
            '우편번호',
            '주소',
            '배송메세지'
        ];
    }

    public function query()
    {
        $query = Order::query();

        if($this->delivery_status)
        {
            $query->where('delivery_status', $this->delivery_status);
        }
        
        if($this->sdate && $this->edate)
        {
            $query->where([
                ['created_at', '>=', $this->sdate],
                ['created_at', '<=', $this->edate . ' 23:59:59']
            ]);
        }

        if($this->keyword_option && $this->keyword)
        {
            $keyword = $this->keyword;
            switch($this->keyword_option)
            {
                case 1:
                    $query->where('receiver', $this->keyword);
                    break;
                case 2:
                    $query->where('phone_1', $this->keyword);
                    break;
                case 3:
                    $query->where('delivery_code', $this->keyword);
                    break;
                case 4:
                    $query->where('id', $this->keyword);
                    break;
                case 5:
                    $query->whereHas('product', function($q) use($keyword){
                        $q->where('model_id', $keyword);
                    });
                case 6:
                    //거래처
                    $query->whereHas('user', function($q) use($keyword){
                        $q->where('name', $keyword);
                    });
                    break;
                default:
                    break;
            }
        }

        // if( isset($this->order_by) )
        // {
        //     $order = $this->order_by == 1 ? 'ASC' : 'DESC';
        //     $query->orderBy('id', $order);
        // }

        $order = $this->order_by == 1 ? 'ASC' : 'DESC';
        $query->orderBy('id', $order)->take($this->count);

        return $query;
    }

    public function map($order): array
    {
        $delivery_price = $order->delivery_price ? $order->delivery_price : '0';
        $prduct_price = $order->product->price( $order->user->shop_type_id );
        $prduct_price = $prduct_price ? $prduct_price * $order->qty : '0';
        return [
            optional($order->deliveryProvider)->name, // '택배사',
            $order->delivery_code, // '송장번호',
            $order->id, // '주문번호',
            $order->product->model_id, // '모델번호',
            $order->product->name, // '제품명',
            $order->option, // '옵션',
            $order->qty, // '수량',

            $prduct_price, //상품가격
            $delivery_price, //배송비

            $order->user->name, // '거래처명',
            $order->receiver, // '수령자',
            $order->phone_1, // '전화번호1(필수)',
            $order->phone_2, // '전화번호2(선택)',
            $order->zipcode, // '우편번호',
            $order->address, // '주소',
            $order->delivery_message, // '배송메세지'
        ];
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return Order::all();
    }
}
