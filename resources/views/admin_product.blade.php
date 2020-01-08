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
        상품 업데이트 성공!
    </div> 
@endif

    <div class="mb-3 card">
        <div class="card-header">
            {{ $product->model_id }}
            <a href="{{ route('admin.products')}}" class="float-right btn btn-info">상품목록</a>
        </div>
        <div class="card-body">
            {!! Form::open(['url' => '/admin/products/' . $product->id, 'method'=>'PUT']) !!}
                <div class="form-group row">
                    <label class="col-sm-2 col-form-label">상품이름</label>
                    <div class="col-sm-10">
                        <input type="text" class="form-control" value="{{ $product->name }}" name="name" required>
                    </div>
                </div>
            @foreach($shop_types as $type)
                <div class="form-group row">
                    <label class="col-sm-2 col-form-label">가격: 거래처-{{$type->type}}</label>
                    <div class="col-sm-10">
                        <input type="number" name="prices[{{$type->id}}]" class="form-control" value="{{ $product->price($type->id) }}">
                    </div>
                </div>
            @endforeach
                <div class="text-right">
                    <button type="submit" class="btn btn-primary">업데이트</button>
                    <button type="button" class="btn btn-danger" id="delete">삭제</button>
                </div>
            {!! Form::close() !!}
        </div>
    </div>
</div>

{!! Form::open(['url' => '/admin/products/' . $product->id, 'method'=>'DELETE', 'name'=>'form_delete']) !!}
{!! Form::close() !!}
@endsection

@push('scripts')
<script type="text/javascript">
$('#delete').on('click', function(){
    if( !confirm('정말 삭제 하시겠습니까?') ) return;
    var form = document.forms.form_delete;
    form.submit();
});
</script>
@endpush