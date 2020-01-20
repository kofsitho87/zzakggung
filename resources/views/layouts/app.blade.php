<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet" type="text/css">

    <!-- Styles -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
</head>
<body>
    <div id="app">
        <nav class="navbar navbar-expand-md navbar-light navbar-laravel">
            <div class="container">
                <a class="navbar-brand" href="{{ url('/') }}">
                    {{ config('app.name', 'Laravel') }}
                </a>
                <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                @auth
                    <!-- Left Side Of Navbar -->
                    <ul class="navbar-nav mr-auto bd-navbar-nav flex-row">
                    {{-- 관리자/일반유저를 분리 --}}
                    @if( Auth::user()->is_admin )
                        <li class="nav-item">
                            <a class="nav-link" href="/admin">관리자</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/admin/users">거래처관리</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/admin/orders">주문내역관리</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/admin/products">상품관리</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/admin/shop_types">거래처타입관리</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/admin/delivery/providers">배송사관리</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/admin/notice">공지사항</a>
                        </li>
                    @else
                        <li class="nav-item">
                            <a class="nav-link" href="/orders/upload">다량주문 업로드</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/orders/list">주문내역 확인</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/orders/trade">거래내역서</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('user.trades') }}">거래내역확인</a>
                        </li>
                    @endif
                    </ul>
                @endauth

                    <!-- Right Side Of Navbar -->
                    <ul class="navbar-nav ml-auto">
                        <!-- Authentication Links -->
                        @guest
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('login') }}">{{ __('Login') }}</a>
                            </li>
                            {{-- @if (Route::has('register'))
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('register') }}">{{ __('Register') }}</a>
                                </li>
                            @endif --}}
                        @else
                            <li class="nav-item dropdown">
                                <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                                    {{ Auth::user()->name }} <span class="caret"></span>
                                </a>

                                <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdown">
                                    <a href="/user/change_pw" class="dropdown-item">비밀번호변경</a>
                                    <a class="dropdown-item" href="{{ route('logout') }}"
                                       onclick="event.preventDefault();
                                                     document.getElementById('logout-form').submit();">
                                        {{ __('Logout') }}
                                    </a>

                                    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                                        @csrf
                                    </form>
                                </div>
                            </li>
                        @endguest
                    </ul>
                </div>
            </div>
        </nav>

        <main class="py-4">
            @yield('content')
        </main>
    </div>
</body>

<!-- Scripts -->
<!-- <script src="{{ asset('js/app.js') }}"></script> -->
<script
  src="https://code.jquery.com/jquery-3.4.1.min.js"
  integrity="sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFlBw8HfCJo="
  crossorigin="anonymous"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js"></script>
@stack('scripts')
</html>
