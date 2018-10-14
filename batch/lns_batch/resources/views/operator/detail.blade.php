@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">


        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if ($errors->any())
            <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            </div>
        @endif


        <div class="panel panel-default">

            {{-- パネルヘッダ --}}
            <div class="panel-heading">
                <div class="row">
                    <div class="col-md-2">
                        管理ユーザー情報(・・調整中・・)
                    </div>
                </div>
            </div>

            {{-- パネルボディ --}}
            <div class="panel-body">
                <div class="row">
                    <div class="col-md-2">
                        id
                    </div>
                    <div class="col-md-10">
                        {{ $operator->id }}
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-2">
                        name
                    </div>
                    <div class="col-md-10">
                        {{ $operator->name }}
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-2">
                        email
                    </div>
                    <div class="col-md-10">
                        {{ $operator->email }}
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-2">
                        created_at
                    </div>
                    <div class="col-md-10">
                        {{ $operator->created_at }}
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-2">
                        updated_at
                    </div>
                    <div class="col-md-10">
                        {{ $operator->updated_at }}
                    </div>
                </div>

            </div>{{-- panel-body --}}
        </div>{{-- panel panel-default --}}


    </div>{{-- row --}}
</div>{{-- container --}}
@endsection
