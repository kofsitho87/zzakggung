@extends('layouts.app')


@section('content')

<div class="container">
@if( $errors->first() )
    <div class = "alert alert-danger">                      
    @foreach ($errors->all() as $input_error)
        {{ $input_error }} <br>
    @endforeach 
    </div> 
@elseif( session()->has('success') )
    <div class="alert alert-success">
        주문내역 업데이트 성공!
    </div> 
@endif

    <div class="mb-3 card">
        <div class="card-header">
            주문번호 : {{ $order->id }}
        </div>
        <div class="card-body">
            {!! Form::open(['url' => '/admin/orders/' . $order->id, 'method'=>'PUT', 'name' => 'form_order_update']) !!}
                <div class="form-group row">
                    <label class="col-sm-2 col-form-label">주문일</label>
                    <div class="col-sm-10">
                        {{ $order->created_at }}
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-sm-2 col-form-label">거래처</label>
                    <div class="col-sm-10">
                        <a href="/admin/users/{{ $order->user->id }}">{{ $order->user->name }}</a>
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-sm-2 col-form-label">모델번호</label>
                    <div class="col-sm-10">
                        {{ $order->product->model_id }}
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-sm-2 col-form-label">제품명</label>
                    <div class="col-sm-10">
                        {{ $order->product->name }}
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-sm-2 col-form-label">수량</label>
                    <div class="col-sm-10">
                        {{ $order->qty }}
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-sm-2 col-form-label">배송비</label>
                    <div class="col-sm-10">
                        {{ $order->delivery_price }}
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-sm-2 col-form-label">수령자</label>
                    <div class="col-sm-10">
                        {{ $order->receiver }}
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-sm-2 col-form-label">우편번호</label>
                    <div class="col-sm-10">
                        {{ $order->zipcode }}
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-sm-2 col-form-label">주소</label>
                    <div class="col-sm-10">
                        {{ $order->address }}
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-sm-2 col-form-label">전화번호1</label>
                    <div class="col-sm-10">
                        {{ $order->phone_1 }}
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-sm-2 col-form-label">전화번호2</label>
                    <div class="col-sm-10">
                        {{ $order->phone_2 }}
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-sm-2 col-form-label">교환/반품 메세지</label>
                    <div class="col-sm-10">
                        {!! optional($order->message)->content !!}
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-sm-2 col-form-label">배송상태</label>
                    <div class="col-sm-10">
                        {{  
                            Form::select(
                                'delivery_status',
                                $order_status, 
                                $order->delivery_status,
                                [
                                    'class' => 'form-control delivery_status',
                                ]
                            )
                        }}
                    </div>
                </div>
                
                
                <div class="form-group row" id="minus_price_row">
                    <label class="col-sm-2 col-form-label">차감금액</label>
                    <div class="col-sm-10">
                        <input type="number" name="minus_price" value="{{ $order->minus_price }}" class="form-control" required>
                    </div>
                </div>
            
                <div class="form-group row">
                    <label class="col-sm-2 col-form-label">배송사</label>
                    <div class="col-sm-10">
                        {{  
                            Form::select(
                                'delivery_provider', 
                                $delivery_providers, 
                                $order->delivery_provider,
                                [
                                    'class' => 'form-control'
                                ]
                            ) 
                        }}
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-sm-2 col-form-label">송장번호</label>
                    <div class="col-sm-10">
                        <input type="text" class="form-control" name="delivery_code" value="{{ $order->delivery_code }}">
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-sm-2 col-form-label">참고사항</label>
                    <div class="col-sm-10">
                        <textarea class="form-control" name="comment" cols="30" rows="10">{{ $order->comment }}</textarea>
                    </div>
                </div>
                
                <div class="text-right">
                    <button type="button" class="btn btn-danger" id="delete_order">삭제</button>
                    <button type="submit" class="btn btn-primary">업데이트</button>
                </div>
            {!! Form::close() !!}
        </div>
    </div>
</div>

{!! Form::open(['url' => '/admin/orders/' . $order->id, 'method'=>'DELETE', 'name' => 'form_order_delete']) !!}
{!! Form::close() !!}

@endsection


@push('scripts')
<script type="text/javascript">
$('#delete_order').on('click', function(){
    if( !confirm('해당 주문내역을 정말 삭제하시겠습니까?') ) return;

    var form = document.forms.form_order_delete;
    form.submit();
});
// $('.delivery_status').on('change', function(e){
//     if(this.value == 7){
//         var form = document.forms.form_order_update;
//         var to = $(this).parents('.row');

//         var html = '<div class="form-group row" id="minus_price_row">';
//             html += '<label class="col-sm-2 col-form-label">차감금액</label>';
//             html += '<div class="col-sm-10">';
//             html += '<input type="number" name="minus_price" value="{{ $order->minus_price }}" class="form-control" required>';
//             html += '</div>';
//             html += '</div>';
        
//         to.after(html);
//     }else{
//         $('#minus_price_row').remove();
//     }
// });
</script>
@endpush