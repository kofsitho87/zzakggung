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
            상품
            <div class="float-right">
                <a href="{{ route('admin.products.create') }}" class="btn btn-info">새상품등록</a>
                <a href="/admin/products/example" class="btn btn-success">상품등록샘플다운로드</a>
            </div>
        </div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-12">
                {!! Form::open(['url' => '/admin/products', 'method'=>'GET']) !!}
                    <div class="form-group row">
                        {{-- <label class="col-sm-2 col-form-label">검색</label> --}}
                        <div class="col-sm-2">
                            {{-- <input type="text" class="form-control" value="{{ $user->name }}" name="name"> --}}
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
                        <div class="col-sm-10">
                            <input type="text" class="form-control" name="keyword" value="{{ $keyword }}">
                        </div>
                    </div>
                    <div class="text-right">
                        <button type="submit" class="btn btn-primary">검색</button>
                        <button type="button" class="btn btn-success" id="excel_uploader">엑셀파일로 상품 대량 업로드</button>
                    </div>
                {!! Form::close() !!}
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <table class="table table-bordered">
                        <thead>
                            {{-- <th>No</th> --}}
                            <th>상품번호</th>
                            <th>모델번호</th>
                            <th>상품이름</th>
                            <th>가격</th>
                        </thead>
                        <tbody>
                        @foreach($products as $idx => $product)
                            <tr>
                                {{-- <td>{{ ($products->currentPage()-1) * $products->perPage() + ($idx+1) }}</td> --}}
                                <td>{{ $product->id }}</td>
                                <td>
                                    <a href="/admin/products/{{ $product->id }}">{{ $product->model_id }}</a>
                                </td>
                                <td>
                                    {{ $product->name }}
                                </td>
                                <td>
                                @foreach($product->prices as $price)
                                    @if($price->shop_type)
                                    거래처-{{ optional($price->shop_type)->type }}
                                    {{ number_format($price->price) }}원
                                    <br>
                                    @endif
                                @endforeach
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>

                    {{ $products->appends($_GET)->links() }}
                </div>
            </div>
        </div>
    </div>

    {!! Form::open(['url' => '/admin/products/upload', 'method'=>'POST', 'name'=> 'form_products_upload', 'enctype' => "multipart/form-data"]) !!}
        {{ Form::file('excel', ['class' => 'd-hide']) }}
    {!! Form::close() !!}
</div>
@endsection


@push('scripts')
<script type="text/javascript">
$('#excel_uploader').on('click', function(){
    var form = document.forms.form_products_upload;
    $(form.excel).trigger('click');
    
    $(form.excel).on('change', function(e){
        form.submit();
    });
});
</script>
@endpush