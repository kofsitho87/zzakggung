@extends('layouts.app')


@section('content')

<!-- include summernote css/js -->
<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.15/dist/summernote.min.css" rel="stylesheet">


<div class="container">
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
        공지사항 업데이트 성공!
    @endif
    </div> 
@endif
    <div class="mb-3 card">
        <div class="card-header">
            공지사항
        </div>
        <div class="card-body">
            {!! Form::open(['url' => '/admin/notice', 'method'=>'POST', 'name' => 'form_notice']) !!}
                <input type="hidden" name="content">
                <div class="summernote">
                    {!! $notice->content !!}
                </div>
                
                <div class="text-right mt-4">
                    <button type="submit" class="btn btn-primary">저장</button>
                </div>
            {!! Form::close() !!}
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.15/dist/summernote.min.js"></script>
<script type="text/javascript">
$(document).ready(function() {
  $('.summernote').summernote({
    height: 500,
    focus: true
  });
  $(document.forms.form_notice).on("submit", function(e){
    var form = this;
    e.preventDefault();

    var content = $('.summernote').summernote('code');
    //console.log(content);
    if(content){
        form.content.value = content;
        form.submit();
    }
  })
})
</script>
@endpush