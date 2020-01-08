@extends('layouts.app')


@section('content')

<style>
#edit_root tr .edit_box {
    display: none;
}
#edit_root tr.edit .no{
    display: none;
}
#edit_root tr.edit .edit_box {
    display: block;
}
</style>

<div class="container">
@if( $errors->first() )
    <div class = "alert alert-danger">                      
    @foreach ($errors->all() as $input_error)
        {{ $input_error }} <br>
    @endforeach 
    </div> 
@elseif( session()->has('success') )
    <div class="alert alert-success">
        생성 성공!
    </div> 
@endif

    {!! Form::open(['url' => '/admin/users/' . $user->id . '/trades', 'method'=>'POST']) !!}
    <div class="card">
        <div class="card-header">적립내역 추가</div>
        <div class="card-body">
            <table class="table table-hover">
                <thead>
                    <th>증감여부</th>
                    <th>적립금</th>
                    <th>내용</th>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            <select name="is_plus" class="form-control">
                                <option value="0">(-)적립금차감</option>
                                <option value="1">(+)적립금증액</option>
                            </select>
                        </td>
                        <td>
                            <input type="number" name="price" class="form-control" required>
                        </td>
                        <td>
                            <input type="text" name="content" class="form-control" required>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="card-footer text-right">
            <button type="submit" class="btn btn-success">생성</button>
        </div>
    </div>
    {!! Form::close() !!}

    <div class="mt-5 card">
        <div class="card-header">
            {{ $user->name }}님 거래내역관리
        </div>
        <div class="card-body">
            <table class="table table-bordered mb-3">
                <colgroup>
                    <col width="15%">
                    <col width="30%">
                    <col width="15%">
                    <col width="*">
                </colgroup>
                <tbody>
                    <tr>
                        <th colspan="2">총적립금</th>
                        <td colspan="2">{{ number_format($total_plus_price) }}원</td>
                    </tr>
                    <tr>
                        <th>사용가능적립금</th>
                        <td>{{ number_format($total_availble_price) }}원</td>
                        <th>사용된적립금</th>
                        <td>{{ number_format($total_minus_price) }}원</td>
                    </tr>
                </tbody>
            </table>

            <div id="edit_root">
                <div class="mb-2">
                    <button class="btn btn-outline-success btn-sm" id="edit_mode_btn">편집모드</button>
                    <button id="remove_btn" class="btn btn-outline-danger btn-sm" disabled>일괄삭제</button>
                </div>

                {!! Form::open(['url' => '/admin/users/' . $user->id . '/trades', 'method'=>'DELETE', 'name' => 'form_order_update']) !!}
                <table class="table table-bordered">
                    <thead class="thead-light">
                        <th>
                            <span class="no">No</span>
                            <input type="checkbox" class="form-control edit_box all_edit_checked" value="1">
                        </th>
                        <th>상세내용</th>
                        <th>일자</th>
                        <th>적립(+)</th>
                        <th>적립(-)</th>
                        <th>잔액</th>
                    </thead>
                    <tbody>
                    @foreach($read_trades as $idx => $trade)
                        <tr>
                            <td>
                                <span class="no">{{ ($read_trades->currentPage()-1) * $read_trades->perPage() + ($idx+1) }}</span>
                                <input type="checkbox" name="trades[{{ $trade->id }}]" class="form-control edit_box">
                            </td>
                            <td>
                                {{ $trade->content }}
                            </td>
                            <td>
                                {{ $trade->created_at }}
                            </td>
                            <td> {{ $trade->is_plus ? number_format($trade->price) : 0 }}원 </td>
                            <td> {{ $trade->is_plus ? 0 : number_format($trade->price) }}원 </td>
                                
                            <td>{{ number_format($trades[ ($read_trades->currentPage()-1) * $read_trades->perPage() + $idx ]->change) }}원</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
                {!! Form::close() !!}
            </div>

            {{ $read_trades->appends($_GET)->links() }}
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script type="text/javascript">

//주문내역 업데이트 폼
var form_update = document.forms.form_order_update;

var editMode = false;
var editRoot = $('#edit_root');
var editTable = editRoot.children().eq(1);

var remove_btn = $('#remove_btn');

//편집모드 토클
$('#edit_mode_btn').on('click', function(){
    editMode = !editMode;
    $(this).toggleClass('active');

    var trs = editTable.find('tr').each(function(idx, tr){
        //console.log( $(tr).children().eq(0) );
        $(tr).toggleClass('edit');
    });

    //편집모드 off일때 
    //delivery_status value reset
    //comment value reset
    if(!editMode) {
        //form_update.delivery_status.value = null;
        //form_update.comment.value = null;
        
    }
    remove_btn.prop('disabled', !editMode);
    remove_btn.toggleClass('active', editMode);
});

//전체선택해재
$('.all_edit_checked').on('change', function(){
    if( !editMode ) return;

    var checked = $(this).prop('checked');
    editTable.find('tr').each(function(idx, tr){
        $(tr).children().eq(0).find('input').prop('checked', checked);
    });
});

remove_btn.on('click', function(){
    
    var checkeds = editTable.find('tr input:checked').length;

    if( checkeds < 1 ){
        alert('삭제할 내역을 한개이상 선택해주세요!');
        return;
    }
    

    if( !confirm('선택한 내역을 삭제하시겠습니까?') ) return;

    form_update.submit();
});
</script>
@endpush