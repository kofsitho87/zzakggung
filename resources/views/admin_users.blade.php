@extends('layouts.app')


@section('content')
<div class="container">
    <div class="mb-3 card">
        <div class="card-header">
            거래처 생성/수정 및 거래처별 주문내역 관리
            <a href="{{ route('admin.createUser') }}" class="btn btn-info float-right">거래처생성</a>
        </div>
        <div class="card-body">
            <table class="table table-bordered">
                <thead>
                    <th>순서</th>
                    <th>아이디</th>
                    <th>거래처이름</th>
                    <th>거래처타입</th>
                    <th>생성일</th>
                    <th>거래내역관리</th>
                </thead>
                <tbody>
                @foreach($users as $idx => $user)
                    <tr>
                        <td>{{ ($users->currentPage()-1) * $users->perPage() + ($idx+1) }}</td>
                        <td>
                            <a href="/admin/users/{{ $user->id }}">
                                {{ $user->email }}
                            </a>
                        </td>
                        <td>{{ $user->name }}</td>
                        <td>{{ optional($user->shop_type)->type }}</td>
                        <td>{{ $user->created_at }}</td>
                        <td>
                            <a href="/admin/users/{{ $user->id }}/trades">거래내역확인</a>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>

            {{ $users->appends($_GET)->links() }}
        </div>
    </div>
</div>
@endsection