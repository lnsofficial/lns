@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading">ユーザー一覧(・・調整中・・)</div>

                <div class="panel-body">
                    @foreach($users as $user)
                        <div class="row alert">
                            <div class="col-md-9">
{{--
                                <a href="{{ url('/user/detail/' . $user->id) }}">
--}}
                                    {{ $user->summoner_name }}
{{--
                                </a>
--}}
                            </div>
                            <div class="col-md-3">
                                <span class="label label-default">{{ $user->summoner_id }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
