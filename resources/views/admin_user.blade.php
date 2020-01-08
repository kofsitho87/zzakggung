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
        거래처 업데이트 성공!
    </div> 
@endif
    <div class="mb-3 card">
        <div class="card-header">{{ $user->name }} 거래처</div>
        <div class="card-body">
            {!! Form::open(['url' => '/admin/users/' . $user->id, 'method'=>'PUT']) !!}
                <div class="form-group row">
                    <label class="col-sm-2 col-form-label">거래처이름</label>
                    <div class="col-sm-10">
                        <input type="text" class="form-control" value="{{ $user->name }}" name="name">
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-sm-2 col-form-label">거래처아이디</label>
                    <div class="col-sm-10">
                        <input type="text" class="form-control" value="{{ $user->email }}" name="email">
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-sm-2 col-form-label">거래처타입</label>
                    <div class="col-sm-10">
                        {{ 
                            Form::select(
                                'shop_type', 
                                $shop_types, 
                                $user->shop_type_id,
                                [
                                    'class' => 'form-control'
                                ]
                            ) 
                        }}
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-sm-2 col-form-label">가입일</label>
                    <div class="col-sm-10">
                        {{ $user->created_at }}
                    </div>
                </div>
                <div class="text-right">
                    <button type="submit" class="btn btn-primary">업데이트</button>
                    <button type="button" id="user_delete_btn" class="btn btn-danger">삭제</button>
                </div>
            {!! Form::close() !!}
        </div>
    </div>
</div>


{!! Form::open(['url' => '/admin/users/' . $user->id, 'method'=>'DELETE', 'name' => 'form_user_delete']) !!}
{!! Form::close() !!}
@endsection


@push('scripts')
<script type="text/javascript">
var form_user_delete = document.forms.form_user_delete;
$('#user_delete_btn').on('click', function(e){
    if( !confirm('해당 거래처를 정말로 삭제하시겠습니까?') ) return;
    form_user_delete.submit();
});
</script>
@endpush