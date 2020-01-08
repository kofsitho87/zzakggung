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
        성공!
    </div> 
@endif

    {!! Form::open(['url' => '/admin/delivery/providers', 'method'=>'POST', 'name' => 'form_create', 'class'=>'d-hide']) !!}
    <div class="mb-3 card">
        <div class="card-header">
            배송사추가
        </div>
        <div class="card-body">
            <div class="form-group row">
                <label class="col-sm-2 col-form-label">배송사</label>
                <div class="col-sm-10">
                    <input type="text" name="provider" class="form-control" placeholder="배송사명" required>
                </div>
            </div>
        </div>
        <div class="card-footer text-right">
            <button class="btn btn-success" type="submit">생성</button>
            <button class="btn btn-info" type="button" id="close_provider">닫기</button>
        </div>
    </div>
    {!! Form::close() !!}

    <div class="mb-3 card">
        <div class="card-header">
            배송사 관리 
            <button class="btn btn-success btn-sm float-right" id="add_provider">추가</button>
        </div>
        <div class="card-body">
            <table class="table table-bordered">
                <thead>
                    <th>순서</th>
                    <th>아이디</th>
                    {{-- <th>삭제</th> --}}
                </thead>
                <tbody>
                @foreach($providers as $idx => $provider)
                    <tr>
                        <td>
                            {{ $idx + 1 }}
                        </td>
                        <td>{{ $provider->name }}</td>
                        {{-- <td>
                            <button type="button" class="btn btn-danger">삭제</button>
                        </td> --}}
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection


@push('scripts')
<script type="text/javascript">
var form_create = document.forms.form_create;

$('#add_provider').on('click', function(){
    $(form_create).removeClass('d-hide');
    $(this).prop('disabled', true);
});
$('#close_provider').on('click', function(){
    $(form_create).addClass('d-hide');
    $('#add_provider').prop('disabled', false);
});
</script>
@endpush