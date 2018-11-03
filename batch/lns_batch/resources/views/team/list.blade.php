@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger">{{ session('danger') }}</div>
        @endif


        <div class="panel panel-default">
            <div class="panel-heading">チーム一覧</div>

            <div class="panel-body">

                <form class="form-horizontal" method="GET" action="{{ url('/team/list') }}">
                    {{ csrf_field() }}

                    {{-- フィルタリング：team_name --}}
                    <div class="form-group">
                        <label class="control-label col-md-2">team_name</label>
                        <div class="col-md-6">
                            <input name="team_name" type="text" class="form-control" value="{{ $team_name }}" />
                        </div>
                    </div>

                    {{-- フィルタリング：team_tag --}}
                    <div class="form-group">
                        <label class="control-label col-md-2">team_tag</label>
                        <div class="col-md-6">
                            <input name="team_tag" type="text" class="form-control" value="{{ $team_tag }}" />
                        </div>
                    </div>

                    {{-- フィルタリング：logo_status --}}
                    <div class="form-group">
                        <label class="control-label col-md-2">logo_status</label>
                        <div class="col-md-6">
                            <select class="form-control" name="logo_status">
                                <option value="">指定なし</option>
                                @foreach(App\Models\Team::LOGO_STATUS_MESSAGES as $key=>$ls_val)
                                <option value={{ $key }} @if ($logo_status===$key) selected @endif>{{ $ls_val }}</option>
                                @endforeach

                            </select>
                        </div>
                    </div>

                    {{-- ソート --}}
                    <div class="form-group">
                        <label class="control-label col-md-2">並び順</label>
                        <div class="col-md-3">
                            <select class="form-control" name="sort">
                                <option value="id"              @if ("id"             ==$sort) selected @endif>id</option>
                                <option value="logo_status"     @if ("logo_status"    ==$sort) selected @endif>logo_status</option>
                                <option value="logo_updated_at" @if ("logo_updated_at"==$sort) selected @endif>logo_updated_at</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select class="form-control" name="order">
                                <option value="asc"  @if ("asc"  ==$order) selected @endif>昇順</option>
                                <option value="desc" @if ("desc" ==$order) selected @endif>降順</option>
                            </select>
                        </div>
                    </div>

                    {{-- submitボタン --}}
                    <div class="form-group">
                        <div class="col-md-offset-2 col-md-10">
                            <button type="submit" class="btn btn-default">検索</button>
                        </div>
                    </div>

                </form>

            </div>
        </div>

    </div>



    <div class="row">

        <div class="panel panel-default">
            <div class="panel-body">

                <table class="table table-hover">
                <thead>
                    <tr>
                        <th>id</th>
                        <th>team_name</th>
                        <th>team_tag</th>
                        <th>logo_status</th>
                        <th>logo_updated_at</th>
                    </tr>
                </thead>

                <tbody>
                @foreach($teams as $team)
                    <tr>
                        <td>{{ $team->id }}</td>
                        <td>
                            <a href="{{ url('/team/detail/' . $team->id) }}">
                                {{ $team->viewTeamName() }}
                            </a>
                        </td>
                        <td>{{ $team->team_tag }}</td>
                        <td class="{{ App\Models\Team::LOGO_STATUS_COLOR_CLASS['table'][$team->logo_status] }}">
                            {{ App\Models\Team::LOGO_STATUS_MESSAGES[$team->logo_status] }}
                        </td>
                        <td>{{ $team->logo_updated_at }}</td>
                    </tr>
                @endforeach
                </tbody>

                <tfoot>
                    <tr>
                        <td colspan=5>
                            <div class="center-block text-center">
                                {{ $teams->appends(['team_name'=>$team_name,'team_tag'=>$team_name,'logo_status'=>$logo_status,'sort'=>$sort,'order'=>$order,])->render() }}
                            </div>
                        </td>
                    </tr>
                </tfoot>
                </table>

            </div>
        </div>

    </div>
</div>
@endsection
