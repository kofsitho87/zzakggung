@extends('layouts.app')

@section('content')

<style>
table .tr-danger{
    background-color: #f9d6d5 !important; 
}
table .tr-set_item{
    background-color: aliceblue;
}
table td, table th{
    font-size: 0.7rem;
}
</style>

<div class="container-fluid">

@if( $errors->first() )
    <div class = "alert alert-danger">                      
    @foreach ($errors->all() as $input_error)
        {{ $input_error }}
    @endforeach 
    </div> 
@elseif( session()->has('orders') )
    <div class="alert alert-success">
        엑셀 업로드 성공!
    </div> 
@elseif( session('upload_finish') )
    <div class = "alert alert-success">
        주문 업로드 성공!
    </div> 
@endif

    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    다량주문업로드
                    <div class="float-right">
                        <form action="{{ route('order.uploadExcel') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <input type="file" name="excel" id="file_upload" accept=".csv, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel">
                            <button disabled type="submit" class="btn btn-primary file_upload_btn">파일 업로드</button>
                            <a href="/orders/example" class="btn btn-success">샘플엑셀파일 다운로드</a>
                        </form>
                    </div>
                </div>
                <div class="card-body">
                    {{
                        Form::open(['route' => 'order.uploadOrders', 'method' => 'POST'])
                    }}
                    <table class="table table-bordered">
                        <thead>
                            <th scope="col">순서</th>
                            <th scope="col">모델번호</th>
                            <th scope="col">제품명</th>
                            <th scope="col">옵션</th>
                            <th scope="col">수량</th>
                            <th scope="col">수령자</th>
                            <th scope="col">전화번호1</th>
                            <th scope="col">전화번호2</th>
                            <th scope="col">우편번호</th>
                            <th scope="col">주소</th>
                            <th scope="col">배송메세지</th>
                            <th scope="col">제품가액</th>
                            <th scope="col">배송비</th>
                            <th scope="col">합계액</th>
                        </thead>
                        <tbody>
                    @if( session()->has('orders') )
                        @foreach(session('orders') as $key => $order)
                            <tr class="{{ (!$order->can_upload ? 'tr-danger' : '') . ($order->set_item ? ' tr-set_item' : '') }}">
                                <td>{{ $key + 1 }}</td>
                                <td>
                                    <input type="hidden" name="order[model_id][]" value="{{ $order->model_id }}">
                                    <input type="hidden" name="order[product_id][]" value="{{ $order->product_id }}">
                                    {{ $order->model_id }}
                                </td>
                                <td>
                                    <input type="hidden" name="order[product_name][]" value="{{ $order->product_name }}">
                                    {{ $order->product_name }}
                                </td>
                                <td>
                                    <input type="hidden" name="order[option][]" value="{{ $order->option }}">
                                    {{ $order->option }}
                                </td>
                                <td>
                                    <input type="hidden" name="order[qty][]" value="{{ $order->qty }}">
                                    {{ $order->qty }}
                                </td>
                                <td>
                                    <input type="hidden" name="order[receiver][]" value="{{ $order->receiver }}">
                                    {{ $order->receiver }}
                                </td>
                                <td>
                                    <input type="hidden" name="order[phone_1][]" value="{{ $order->phone_1 }}">
                                    {{ $order->phone_1 }}
                                </td>
                                <td>
                                    <input type="hidden" name="order[phone_2][]" value="{{ $order->phone_2 }}">
                                    {{ $order->phone_2 }}
                                </td>
                                <td>
                                    <input type="hidden" name="order[zipcode][]" value="{{ $order->zipcode }}">
                                    {{ $order->zipcode }}
                                </td>
                                <td>
                                    <input type="hidden" name="order[address][]" value="{{ $order->address }}">
                                    {{ $order->address }}
                                </td>
                                <td>
                                    <input type="hidden" name="order[delivery_message][]" value="{{ $order->delivery_message }}">
                                    {{ $order->delivery_message }}
                                </td>
                                <td>{{ number_format($order->price) }}원</td>
                                <td>
                                    <input type="hidden" name="order[delivery_price][]" value="{{ $order->delivery_price }}">
                                    {{ number_format($order->delivery_price) }}원
                                </td>
                                <td>{{ number_format($order->delivery_price + ($order->price * $order->qty)) }}원</td>
                            </tr>
                        @endforeach
                        </tbody>
                        @if( session()->has('total_price') )
                        <tfoot>
                            <tr>
                                <th colspan="3">총 금액</th>
                                <td colspan="11">{{ number_format(session('total_price')) }}원</td>
                            </tr>
                        </tfoot>
                        @endif
                    @endif
                    </table>
                @if( session('orders') )
                    @if( session('can_upload') )
                    <div class="text-right">
                        <button type="submit" class="btn btn-primary">최종전송</button>
                    </div>
                    @else
                    <div class="alert alert-danger">
                        엑셀데이터에 잘못된 데이터가 있어 업로드가 불가하오니 수정후 다시 파일업로드를 해주시기 바랍니다!
                    </div>
                    @endif                    
                @endif
                    {!! Form::close() !!}
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript" defer>
//var fileUploadBtn = document.querySelector('.file_upload_btn');
// fileUploadBtn.addEventListener('click', function(){
//     //file upload to excel
// });

document.querySelector('#file_upload').addEventListener('change', function(e){
    var files = e.target.files;
    if( files.length < 1 ) return;

    document.querySelector('.file_upload_btn').removeAttribute('disabled');
});
</script>

@endsection