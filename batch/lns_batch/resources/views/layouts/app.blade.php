@extends('layouts.base')

@section('base-content')
    {{-- 左サイドメニュー --}}
    <div class="col-md-2">
        @include('layouts.left-nav')
    </div>

    {{-- メインコンテンツ --}}
    <div class="col-md-10">
        @yield('content')
    </div>
@endsection
