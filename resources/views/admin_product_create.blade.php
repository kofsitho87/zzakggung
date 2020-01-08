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
        상품 생성 성공!
    </div> 
@endif

    <div class="mb-3 card">
        <div class="card-header">
            새상품등록
        </div>
        <div class="card-body">
            {!! Form::open(['url' => '/admin/products', 'method'=>'POST']) !!}
                <div class="form-group row">
                    <label class="col-sm-2 col-form-label">모델번호</label>
                    <div class="col-sm-10">
                        <input type="text" class="form-control{{ $errors->has('model_id') ? ' is-invalid' : '' }}" name="model_id" value="{{ old('model_id') }}" required>
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-sm-2 col-form-label">상품이름</label>
                    <div class="col-sm-10">
                        <input type="text" class="form-control{{ $errors->has('name') ? ' is-invalid' : '' }}" name="name" value="{{ old('name') }}" required>
                    </div>
                </div>
            @foreach($shop_types as $type)
                <div class="form-group row">
                    <label class="col-sm-2 col-form-label">가격: 거래처-{{$type->type}}</label>
                    <div class="col-sm-10">
                        <input type="number" name="prices[{{$type->id}}]" class="form-control" placeholder="상품가격입력" value="{{ old('prices['. $type->id .']') }}">
                    </div>
                </div>
            @endforeach
                <div class="text-right">
                    <button type="submit" class="btn btn-primary">생성</button>
                </div>
            {!! Form::close() !!}
        </div>
    </div>
</div>
@endsection