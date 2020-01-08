@extends('layouts.app')

@section('content')
<style>
.table{
    background-color: white;
}
.table td.status-back {
    color:red;
}
.table td.status-change {
    color:blue;
}
table td, table th{
    font-size: 0.7rem;
}
.item_back, .item_change{
    font-size: 9px;
    padding: 2px;
}
</style>

<div class="container-fluid">
    <div class="search_area mb-3 card">
        <div class="card-header">
            주문내역확인
        </div>
        <div class="card-body">
            <form>
                {{-- <div class="form-group row">
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
                </div> --}}
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
                                    '교환완료',
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
                        <input type="date" name="sdate" value="{{ $sdate }}"> - <input type="date" name="edate" value="{{ $edate }}">
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
                            $cnt,
                            [
                                'class' => 'form-control'
                            ]
                        ) 
                    }}
                    </div>
                </div>
                <div class="text-right">
                    <button type="submit" class="btn btn-primary">검색</button>
                    {{-- <button type="button" class="btn btn-success">엑셀다운로드</button> --}}
                    <a class="btn btn-success" href="{{ route('order.export') . '?' . Request::getQueryString() }}">엑셀다운로드</a>
                </div>
            </form>
        </div>
    </div>
    <table class="table table-bordered table-hover">
        <colgroup>
            <col with="30px"/>
            <col/>
            <col/>
            <col/>
            <col/>
            <col/>
            <col/>
            <col/>
            <col/>
            <col/>
            <col/>
        </colgroup>
        <thead class="thead-light">
            <th>No</th>
            <th>주문번호</th>
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
            <th>배송상태</th>
            <th>송장번호</th>
            <th>참고사항</th>
            <th>반품/교환</th>
        </thead>
        <tbody>
        @foreach($orders as $idx => $order)
            <tr data-pid="{{ $order->id }}">
                <td>{{ (($orders->currentPage() - 1) * $orders->perPage()) + $idx+1 }}</td>
                <td>{{ $order->id }}</td>
                <td>{{ $order->created_at->format('Y-m-d') }}</td>
                <td>{{ $order->product->model_id }}</td>
                <td>{{ $order->product->name }}</td>
                <td>{{ $order->option }}</td>
                <td>
                    {{ number_format($order->product->price( Auth::user()->shop_type_id )) }}
                </td>
                <td>{{ $order->qty }}</td>
                <td>{{ $order->delivery_price }}</td>
                <td>
                    {{ 
                        number_format(
                            ($order->product->price( Auth::user()->shop_type_id ) 
                            * $order->qty) + $order->delivery_price
                            - $order->minus_price
                        ) 
                    }}
                </td>
                <td>{{ $order->receiver }}</td>
                <td>{{ $order->phone_1 }}</td>
                <td
                    class="{{ $order->delivery_status == 5 ? 'status-back' : ($order->delivery_status == 6 ? 'status-change' : '') }}"
                >{{ $order->status->name }}</td>
                <td>{{ $order->delivery_code }}</td>
                <td>{{ $order->comment }}</td>
                <td>
                @if( $order->delivery_status == 4 )
                    <button class="btn btn-sm btn-danger item_back">반품</button>
                    <button class="btn btn-sm btn-info item_change">교환</button>
                @endif
                {{-- @if( $order->delivery_status != 5 )
                    <button class="btn btn-sm btn-danger item_back">반품</button>
                @endif
                @if( $order->delivery_status != 6 )
                    <button class="btn btn-sm btn-info item_change">교환</button>
                @endif --}}
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>

    {{ $orders->appends($_GET)->links() }}
</div>

{{-- 모달영역 --}}
<div class="modal fade" id="message_modal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title">참고메세지작성</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <div class="modal-body">
            <textarea class="form-control" placeholder="메세지작성"></textarea>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            <button type="button" class="btn btn-primary" id="send_message">Save changes</button>
        </div>
        </div>
    </div>
</div>

{!! Form::open([
    //'url' => '/admin/users/user/' . $user->id, 
    'name' => 'form_item_update',
    'method'=>'PUT'
]) !!}
    @csrf
    <input type="hidden" name="act">
    <input type="hidden" name="message">
{!! Form::close() !!}

@endsection

@push('scripts')
<script type="text/javascript">
$(function(){
    var action, pid;

    var updateForm = document.forms.form_item_update;
    $('.item_back').on('click', function(e){
        var target = $(this);
        var _pid = target.parents('tr').data('pid');

        action = 'back';
        pid = _pid;
        
        $('#message_modal').modal('show');

        // updateForm.act.value = 'back';
        // updateForm.action = '/orders/' + pid;
        // updateForm.submit();
    });
    $('.item_change').on('click', function(e){
        var target = $(this);
        var _pid = target.parents('tr').data('pid');

        action = 'change';
        pid = _pid;

        $('#message_modal').modal('show');

        // var target = $(this);
        // var pid = target.parents('tr').data('pid');
        
        // updateForm.act.value = 'change';
        // updateForm.action = '/orders/' + pid;
        // updateForm.submit();
    });

    $('#send_message').on('click', function(){
        if(!action || !pid) return;

        var textarea = $('#message_modal').find('textarea');
        var message = textarea.val();

        updateForm.message.value = message;

        //form submit
        updateForm.act.value = action;
        updateForm.action = '/orders/' + pid;
        updateForm.submit();
        
        textarea.val('');
        action = null;
        pid = null;
    });
});
</script>
@endpush