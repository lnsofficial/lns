@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading">管理ユーザー一覧(・・調整中・・)</div>

                <div class="panel-body">
                    @foreach($operators as $operator)
                        <div class="row alert">
                            <div class="col-md-9">
{{--
                                <a href="{{ url('/operator/detail/' . $operator->id) }}">
--}}
                                    {{ $operator->name }}
{{--
                                </a>
--}}
                            </div>
                            <div class="col-md-3">
                                <span class="label label-default">{{ $operator->activate }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
