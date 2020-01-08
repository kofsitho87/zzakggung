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
        거래처 생성 성공!
    </div> 
@endif

    <div class="mb-3 card">
        <div class="card-header">
            거래처 생성
            <a href="{{ route('admin.users') }}" class="btn btn-info float-right">목록으로</a>
        </div>
        <div class="card-body">
            {!! Form::open(['url' => '/admin/users/', 'method'=>'POST']) !!}
                <div class="form-group row">
                    <label class="col-sm-2 col-form-label">거래처이름</label>
                    <div class="col-sm-10">
                        <input type="text" class="form-control{{ $errors->has('name') ? ' is-invalid' : '' }}" name="name" required autocomplete="off" value="{{ old('name') }}">
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-sm-2 col-form-label">거래처아이디</label>
                    <div class="col-sm-10">
                        <input type="text" class="form-control{{ $errors->has('email') ? ' is-invalid' : '' }}" name="email" required autocomplete="off" value="{{ old('email') }}">
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-sm-2 col-form-label">비밀번호</label>
                    <div class="col-sm-10">
                        <input type="password" class="form-control{{ $errors->has('password') ? ' is-invalid' : '' }}" name="password" required>
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-sm-2 col-form-label">거래처타입</label>
                    <div class="col-sm-10">
                        {{ 
                            Form::select(
                                'shop_type', 
                                $shop_types, 
                                old('shop_type'),
                                [
                                    'class' => 'form-control'
                                ]
                            ) 
                        }}
                    </div>
                </div>
                <div class="text-right">
                    <button type="submit" class="btn btn-primary">거래처 생성</button>
                </div>
            {!! Form::close() !!}
        </div>
    </div>
</div>
@endsection