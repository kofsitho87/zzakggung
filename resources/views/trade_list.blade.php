@extends('layouts.app')

@section('content')
<style>

table td, table th{
    font-size: 0.7rem;
}
</style>
<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h5>거래내역서</h5>
            <form>
                <div class="form-group row">
                    <label class="col-sm-1 col-form-label">기간검색</label>
                    <div class="col-sm-11">
                        <input type="date" id="sdate" name="sdate" value="{{ $sdate }}"> 
                        - 
                        <input type="date" id="edate" name="edate" value="{{ $edate }}">

                        <button type="button" class="btn btn-outline-primary btn-sm date today">오늘</button>
                        <button type="button" class="btn btn-outline-primary btn-sm date yesterday">어제</button>
                        <button type="button" class="btn btn-outline-primary btn-sm date week">이번주</button>
                        <button type="button" class="btn btn-outline-primary btn-sm date month">이번달</button>
                        <button type="button" class="btn btn-outline-primary btn-sm date reset">전체</button>
                    </div>
                </div>
                <div class="text-right">
                    <button type="submit" class="btn btn-primary">검색</button>
                    {{-- <a class="btn btn-success" href="{{ route('order.export') }}">엑셀다운로드</a> --}}
                </div>
            </form>
        </div>
        <div class="card-body">
        <h2>총가격: {{ number_format($all_total_price) }}원</h2>
        @foreach($orders as $idx => $order_list)
            <h3>{{ $order_list['list']->first()->created_at->format('Y-m-d') }}</h3>
            <table class="table table-bordered table-hover">
                <thead class="thead-light">
                    <th>주문번호</th>
                    <th>주문일</th>
                    <th>모델번호</th>
                    <th>제품명</th>
                    <th>가격</th>
                    <th>수량</th>
                    <th>배송비</th>
                    <th>합계</th>
                    <th>수령자</th>
                    <th>배송상태</th>
                    <th>송장번호</th>
                    <th>참고사항</th>
                </thead>
                <tbody>
                @foreach($order_list['list'] as $idx => $order)
                    <tr>
                        <td>{{ $order->id }}</td>
                        <td>{{ $order->created_at }}</td>
                        <td>{{ $order->product->model_id }}</td>
                        <td>{{ $order->product->name }}</td>
                        <td>
                            {{ number_format($order->product->price( Auth::user()->shop_type_id )) }}
                            원
                        </td>
                        <td>{{ $order->qty }}</td>
                        <td>{{ number_format($order->delivery_price) }}원</td>
                        <td>
                            {{ 
                                number_format(
                                    ($order->product->price( Auth::user()->shop_type_id ) * $order->qty) 
                                    + $order->delivery_price
                                    - $order->minus_price
                                ) 
                            }}
                            원
                        </td>
                        <td>{{ $order->receiver }}</td>
                        <td>{{ $order->status->name }}</td>
                        <td>{{ $order->delivery_code }}</td>
                        <td></td>
                    </tr>
                @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="3">총 금액</th>
                        <td colspan="10">{{ number_format($order_list['total_price']) }}원</td>
                    </tr>
                </tfoot>
            </table>
        @endforeach
        </div>
    </div>
</div>
@endsection


@push('scripts')
<script type="text/javascript" src="/js/date.js"></script>
@endpush