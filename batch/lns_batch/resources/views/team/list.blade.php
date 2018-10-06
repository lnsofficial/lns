@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="panel panel-default">
                <div class="panel-heading">チーム一覧(ロゴ更新日順)</div>

                <div class="panel-body">
                    @foreach($teams as $team)
                        <div class="row alert">
                            <div class="col-md-9">
                                <a href="{{ url('/team/detail/' . $team->id) }}">
                                    {{ $team->team_name }}
                                </a>
                            </div>
                            <div class="col-md-3">
                            @if ($team->logo_status == App\Models\Team::LOGO_STATUS_UNREGISTERED)
                                <span class="label label-default">ロゴ未登録</span>
                            @elseif ($team->logo_status == App\Models\Team::LOGO_STATUS_UNAUTHENTICATED)
                                <span class="label label-warning">ロゴ未検閲</span>
                            @elseif ($team->logo_status == App\Models\Team::LOGO_STATUS_AUTHENTICATED)
                                <span class="label label-success">ロゴ検閲済み</span>
                            @elseif ($team->logo_status == App\Models\Team::LOGO_STATUS_AUTHENTICATEERROR)
                                <span class="label label-danger">ロゴ検閲NG</span>
                            @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
