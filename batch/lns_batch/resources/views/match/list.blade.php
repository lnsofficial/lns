@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading">マッチ一覧(・・調整中・・)</div>

                <div class="panel-body">
                    @foreach($matches as $match)
                        <div class="row alert">
                            <div class="col-md-3">
{{--
                                <a href="{{ url('/match/detail/' . $match->id) }}">
--}}
                                    {{ $match->id }}
{{--
                                </a>
--}}
                            </div>
                            <div class="col-md-3">
                                {{ $match->host_team_id }}
                            </div>
                            <div class="col-md-3">
                                {{ $match->apply_team_id }}
                            </div>
                            <div class="col-md-3">
                                <span class="label label-default">{{ $match->match_date }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
