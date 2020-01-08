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
        업데이트 성공!
    </div> 
@endif

    {!! Form::open(['url' => '/admin/shop_types', 'method'=>'POST', 'name' => 'form_create', 'class'=>'d-hide']) !!}
    <div class="mb-3 card">
        <div class="card-header">
            거채처 타입 추가
        </div>
        <div class="card-body">
            <div class="form-group row">
                <div class="col-sm-3">
                    <input type="text" name="type" class="form-control" placeholder="거래처타입" required>
                </div>
                <div class="col-sm-3">
                    {{-- <select name="delivery_status">
                        <option value=""></option>
                    </select> --}}
                    {{ 
                        Form::select(
                            'delivery_status', 
                            $order_status,
                            '',
                            [
                                'class' => 'form-control'
                            ]
                        ) 
                    }}
                </div>
                <div class="col-sm-6">
                    <input type="number" name="delivery_price" class="form-control" placeholder="배송비" required>
                </div>
            </div>
        </div>
        <div class="card-footer text-right">
            <button class="btn btn-success" type="submit">생성</button>
            <button class="btn btn-info" type="button" id="close">닫기</button>
        </div>
    </div>
    {!! Form::close() !!}

    <div class="mb-3 card">
        <div class="card-header">
            거채처 타입
            <button class="btn btn-success btn-sm float-right" id="add">추가</button>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-12">
                {!! Form::open(['url' => '/admin/shop_types', 'method'=>'PUT']) !!}
                    <table class="table table-bordered">
                        <thead>
                            <th>거래처 타입</th>
                            <th>배송비</th>
                            <th>초기배송상태</th>
                            <th>삭제</th>
                        </thead>
                        <tbody>
                        @foreach($shopTypes as $idx => $shopType)
                            <tr>
                                <td>{{ $shopType->type }}</td>
                                <td>
                                    {{-- {{ number_format($shopType->delivery_price) }}원 --}}
                                    <input type="number" class="form-control" name="delivery_price[{{ $shopType->id }}]" value="{{ $shopType->delivery_price }}">
                                </td>
                                <td>{{ $shopType->status->name }}</td>
                                <td>
                                    <button type="button" class="btn btn-danger delete_shop_type" data-id="{{ $shopType->id }}">삭제</button>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                    <div class="text-right">
                        <button type="submit" class="btn btn-primary">수정</button>
                    </div>
                {!! Form::close() !!}
                </div>
            </div>
        </div>
    </div>
</div>


{!! Form::open(['url' => '/admin/shop_types', 'method'=>'DELETE', 'name'=> 'form_shop_delete']) !!}
{!! Form::close() !!}
@endsection


@push('scripts')
<script type="text/javascript">
var form = document.forms.form_shop_delete;
$('.delete_shop_type').on('click', function(e){
    if( !confirm('해당 데이터를 정말로 삭제하시겠습니까?') ) return;
    var id = $(this).data('id');
    form.action = '/admin/shop_types/' + id;
    form.submit();
});

var form_create = document.forms.form_create;
$('#add').on('click', function(){
    $(form_create).removeClass('d-hide');
    $(this).prop('disabled', true);
});
$('#close').on('click', function(){
    $(form_create).addClass('d-hide');
    $('#add').prop('disabled', false);
});
</script>
@endpush