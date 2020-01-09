@extends('layouts.app')


@section('content')
<style>
table td, table th{
    font-size: 0.7rem;
}
table .update_order_status{
    font-size: 9px;
    padding: 2px;
}

#edit_root tr .edit_box {
    display: none;
}
#edit_root tr.edit .no{
    display: none;
}
#edit_root tr.edit .edit_box {
    display: block;
}
#edit_root tr.edit.active{
    background: #d9f4ff;
}
</style>

<div class="container-fluid">
    @if( $errors->first() )
        <div class = "alert alert-danger">                      
        @foreach ($errors->all() as $input_error)
            {{ $input_error }} <br>
        @endforeach 
        </div> 
    @elseif( session()->has('success') )
        <div class="alert alert-success">
        @if( session()->has('msg') )
            {{ session()->get('msg') }}
        @else
            주문내역 업데이트 성공!
        @endif
        </div> 
    @endif

    <div class="mb-3 card">
        <div class="card-header">
            주문내역 관리
        </div>
        <div class="card-body">
            <form>
                <div class="form-group row">
                    <label class="col-sm-2 col-form-label">정렬</label>
                    <div class="col-sm-10">
                        {{ Form::select(
                                'order_by', 
                                [
                                    '최신순',
                                    '오래된순',
                                ], 
                                $order_by,
                                [
                                    'class' => 'form-control'
                                ]
                            ) 
                        }}
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-sm-2 col-form-label">주문상태</label>
                    <div class="col-sm-10">
                        {{ Form::select(
                                'delivery_status', 
                                [
                                    '전체',
                                    '입금대기',
                                    '배송준비중',
                                    '발송대기',
                                    '발송완료',
                                    '반품요청',
                                    '교환요청',
                                    '반품완료',
                                    '교환완료'
                                ], 
                                $delivery_status,
                                [
                                    'class' => 'form-control'
                                ]
                            ) 
                        }}
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-sm-2 col-form-label">기간검색</label>
                    <div class="col-sm-10">
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
                <div class="form-group row">
                    <label class="col-sm-2 col-form-label">검색어</label>
                    <div class="col-sm-2">
                        {{ Form::select(
                            'keyword_option', 
                                $keyword_options, 
                                $keyword_option,
                                [
                                    'class' => 'form-control'
                                ]
                            ) 
                        }}
                    </div>
                    <div class="col-sm-8">
                        <input type="text" class="form-control" name="keyword" value="{{ $keyword }}">
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-sm-2 col-form-label">페이지검색갯수</label>
                    <div class="col-sm-10">
                    {{ Form::select(
                            'count', 
                            $page_counts, 
                            $count,
                            [
                                'class' => 'form-control'
                            ]
                        ) 
                    }}
                    </div>
                </div>
                <div class="text-right">
                    <button type="submit" class="btn btn-primary">검색</button>
                    <button type="button" class="btn btn-info" id="excel_uploader">엑셀업로드</button>
                    <a class="btn btn-success" href="{{ route('admin.orders.export') . '?' . Request::getQueryString() }}">엑셀다운로드</a>
                </div>
            </form>

            <div class="mt-5" id="edit_root">
                <div class="mb-2">
                    <button class="btn btn-outline-success btn-sm" id="edit_mode_btn">편집모드</button>
                    <button id="delivery_status_modal_btn" class="btn btn-outline-info btn-sm" data-toggle="modal" data-target="#delivery_status_modal" disabled>주문상태일괄변경</button>
                    <button id="comment_modal_btn" class="btn btn-outline-secondary btn-sm" data-toggle="modal" data-target="#comment_modal" disabled>참고사항일괄변경</button>
                    <button id="delete_orders_btn" class="btn btn-outline-danger btn-sm" disabled>주문내역일괄삭제</button>
                </div>

                {!! Form::open(['url' => '/admin/orders', 'method'=>'PUT', 'name' => 'form_order_update']) !!}
                    <input type="hidden" name="delivery_status">
                    <input type="hidden" name="comment">
                    <input type="hidden" name="is_deleted" value="0">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th style="min-width: 36px;">
                                <span class="no">No</span>
                                <input type="checkbox" class="form-control edit_box all_edit_checked" value="1">
                            </th>
                            <th>주문번호</th>
                            <th>거래처</th>
                            <th>주문일</th>
                            <th>모델번호</th>
                            <th>제품명</th>
                            <th>옵션</th>
                            <th>가격</th>
                            <th>수량</th>
                            <th>배송비</th>
                            <th>합계</th>
                            <th>수령자</th>
                            <th>전화번호1</th>
                            <th>전화번호2</th>
                            <th>배송메세지</th>
                            <th>배송상태</th>
                            <th>배송사</th>
                            <th>송장번호</th>
                            <th>참고사항</th>
                            <th>교환/반품메세지</th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach($orders as $idx => $order)
                        <tr>
                            <td>
                                <span class="no">{{ ($orders->currentPage()-1) * $orders->perPage() + ($idx+1) }}</span>
                                <input type="checkbox" name="orders[{{ $order->id }}]" class="form-control edit_box">
                                <button data-order="{{ json_encode($order) }}" class="btn btn-sm btn-primary edit_box change_receiver_btn" type="button">고객정보수정</button>
                                <!-- <button data-orderId="{{ $order->id }}" class="btn btn-sm btn-danger edit_box delete_order_btn" type="button">삭제</button>ㅗ -->
                            </td>
                            <td>
                                <a href="/admin/orders/{{ $order->id }}">{{ $order->id }}</a>
                            </td>
                            <td>
                                {{-- <a href="/admin/orders?keyword_option=6&keyword={{$order->user->name}}">{{ $order->user->name }}</a> --}}
                                <a href="/admin/users/{{ $order->user->id }}/trades">{{ $order->user->name }}</a>
                            </td>
                            <td>{{ $order->created_at }}</td>
                            <td>{{ $order->product->model_id }}</td>
                            <td>{{ $order->product->name }}</td>
                            <td>{{ $order->option }}</td>
                            <td>
                                {{ number_format($order->product->price($order->user->shop_type_id)) }}
                                원
                            </td>
                            <td>{{ $order->qty }}</td>
                            <td>{{ number_format($order->delivery_price) }}원</td>
                            <td>
                                {{ 
                                    number_format(
                                        ($order->product->price($order->user->shop_type_id) * $order->qty) 
                                        + $order->delivery_price
                                        - $order->minus_price
                                    ) 
                                }}원
                            @if($order->minus_price > 0)
                                (차감금액 : {{ number_format($order->minus_price) }}원)
                            @endif
                            </td>
                            <td>{{ $order->receiver }}</td>
                            <td>{{ $order->phone_1 }}</td>
                            <td>{{ $order->phone_2 }}</td>
                            <td>{{ $order->delivery_message }}</td>
                            <td>
                                {{ $order->status->name }}
                            @if($order->status->id == 3)
                                <button type="button" class="btn btn-outline-success btn-sm update_order_status" data-id="{{ $order->id }}">발송완료</button>
                            @endif
                            </td>
                            <td>{{ optional($order->deliveryProvider)->name }}</td>
                            <td>{{ $order->delivery_code }}</td>
                            <td>{{ $order->comment }}</td>
                            <td>{{ \Illuminate\Support\Str::words(optional($order->message)->content, 10, '...') }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="4">총합계액</th>
                            <td colspan="16">
                                <strong>{{ number_format($total_price) }}</strong>원
                            </td>
                        </tr>
                    </tfoot>
                </table>
                <div class="text-right">
                    <button name="submit_btn" type="submit" class="btn btn-success" disabled>일괄변경</button>
                </div>
                {!! Form::close() !!}

            </div>

            {{ $orders->appends($_GET)->links() }}
        </div>
    </div>

    {!! Form::open(['url' => '/admin/orders/upload', 'method'=>'POST', 'name'=> 'form_upload', 'enctype' => "multipart/form-data"]) !!}
        {{ Form::file('excel', ['class' => 'd-hide']) }}
    {!! Form::close() !!}

    {!! Form::open(['url' => '/admin/orders/', 'method'=>'PUT', 'name'=> 'form_update_order_status']) !!}
    {!! Form::close() !!}
    {!! Form::open(['url' => '/admin/orders/', 'method'=>'DELETE', 'name'=> 'form_delete_order']) !!}
    {!! Form::close() !!}
</div>



<div class="modal fade" id="delivery_status_modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title" id="exampleModalLabel">주문상태일괄변경</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <div class="modal-body">
            {{ 
                Form::select(
                    'delivery_status', 
                    $order_status, 
                    '',
                    [
                        'class' => 'form-control',
                    ]
                ) 
            }}
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            <button type="button" class="btn btn-primary" id="change_delivery_status">Save changes</button>
        </div>
        </div>
    </div>
</div>
<div class="modal fade" id="comment_modal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title">참고사항일괄변경</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <div class="modal-body">
            <input type="text" name="comment" placeholder="참고사항" class="form-control">
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">닫기</button>
            <button type="button" class="btn btn-primary" id="change_comment">저장</button>
        </div>
        </div>
    </div>
</div>
<div class="modal fade" id="receiver_change_modal" tabindex="-1" role="dialog">
    <!-- <form class="modal-dialog" role="document" name="form_receiver_change"> -->
    {!! Form::open(['url' => '/admin/order/', 'method'=>'PUT', 'name'=> 'form_receiver_change', 'class' => 'modal-dialog']) !!}
        <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title">고객정보수정</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <div class="modal-body">
            <div class="form-group">
                <input type="text" name="receiver" placeholder="수령자" class="form-control">
            </div>
            <div class="form-group">
                <input type="text" name="address" placeholder="주소" class="form-control">
            </div>
            <div class="form-group">
                <input type="text" name="phone_1" placeholder="전화번호1" class="form-control">
            </div>
            <div class="form-group">
                <input type="text" name="phone_2" placeholder="전화번호2" class="form-control">
            </div>
            <div class="form-group">
                <input type="text" name="delivery_code" placeholder="송장번호" class="form-control">
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">닫기</button>
            <button type="submit" class="btn btn-primary" id="change_receiver">저장</button>
        </div>
        </div>
    </form>
</div>
@endsection


@push('scripts')
<script type="text/javascript" src="/js/date.js"></script>
<script type="text/javascript">

$('#excel_uploader').on('click', function(){
    var form = document.forms.form_upload;
    $(form.excel).trigger('click');
    
    $(form.excel).on('change', function(e){
        form.submit();
    });
});

$('.update_order_status').on('click', function(){
    var orderId = $(this).data('id');
    var form = document.forms.form_update_order_status;
    form.action = '/admin/orders/' + orderId + '/status/4';
    form.submit();
});

//주문내역 업데이트 폼
var form_update = document.forms.form_order_update;

var editMode = false;
var editRoot = $('#edit_root');
var editTable = editRoot.children().eq(1);

//주문상태일괄변경 버튼
var delivery_status_modal_btn = $('#delivery_status_modal_btn');
var comment_modal_btn = $('#comment_modal_btn');
var delete_orders_btn = $('#delete_orders_btn');

//편집모드 토클
$('#edit_mode_btn').on('click', function(){
    editMode = !editMode;
    $(this).toggleClass('active');

    delivery_status_modal_btn.prop('disabled', !editMode);
    comment_modal_btn.prop('disabled', !editMode);
    delete_orders_btn.prop('disabled', !editMode);
    $(form_update.submit_btn).prop('disabled', !editMode);
    

    var trs = editTable.find('tr').each(function(idx, tr){
        //console.log( $(tr).children().eq(0) );
        $(tr).toggleClass('edit');
    });

    //편집모드 off일때 
    //delivery_status value reset
    //comment value reset
    if(!editMode) {
        form_update.delivery_status.value = null;
        form_update.comment.value = null;
    }
});

//전체선택해재
$('.all_edit_checked').on('change', function(){
    if( !editMode ) return;

    var checked = $(this).prop('checked');
    editTable.find('tbody tr').each(function(idx, tr){
        $(tr).toggleClass('active', checked);
        $(tr).children().eq(0).find('input').prop('checked', checked);
    });
});

//change delivery status
$('#change_delivery_status').on('click', function(){
    $('#delivery_status_modal').modal('hide');

    var delivery_status = $('#delivery_status_modal').find('select[name=delivery_status]').val();
    form_update.delivery_status.value = delivery_status;
});

//chnage comment
$('#change_comment').on('click', function(){
    $('#comment_modal').modal('hide');

    var comment = $('#comment_modal').find('input[name=comment]').val();
    form_update.comment.value = comment;
});

$('tbody input.edit_box').on('change', function(){
    var tr = $(this).parents('tr');
    var checked = this.checked;

    tr.toggleClass('active', checked);
});

//show change receiver modal
$(".change_receiver_btn").on("click", function(e){
    var form = document.forms.form_receiver_change;
    var order = JSON.parse(e.target.dataset.order);
    console.log(order);
    form.receiver.value = order.receiver;
    form.address.value = order.address;
    form.phone_1.value = order.phone_1;
    form.phone_2.value = order.phone_2;
    form.delivery_code.value = order.delivery_code;

    form.action = '/admin/orders/' + order.id + '/receiver'
    $('#receiver_change_modal').modal('show');
})

//주문내역 일괄삭제

//주문내역 개별삭제
// $(".delete_order_btn").on("click", function(e){
//     var orderId = e.target.dataset.orderid;
//     console.log(orderId);
//     var form = document.forms.form_delete_order;
//     form.action += "/" + orderId;
//     console.log(form.action);
//     form.submit();
// })

//주문내역 일괄삭제
delete_orders_btn.on("click", function(){
    if( confirm("해당주문내역을 정말 삭제하시겠습니까?") ){
        form_update.is_deleted.value = "1";
        form_update.submit();
    }
})
</script>
@endpush