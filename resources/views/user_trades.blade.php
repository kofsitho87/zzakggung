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
        생성 성공!
    </div> 
@endif

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
            <table class="table table-bordered">
                <thead class="thead-light">
                    <th>No</th>
                    <th>상세내용</th>
                    <th>일자</th>
                    <th>적립(+)</th>
                    <th>적립(-)</th>
                    <th>잔액</th>
                </thead>
                <tbody>
                @foreach($read_trades as $idx => $trade)
                    <tr>
                        <td>{{ ($read_trades->currentPage()-1) * $read_trades->perPage() + ($idx+1) }}</td>
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

            {{ $read_trades->appends($_GET)->links() }}
        </div>
    </div>
</div>
@endsection