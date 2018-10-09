@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">


        <div class="panel panel-default">
            <div class="panel-heading">ユーザー一覧</div>

            <div class="panel-body">

                <form class="form-horizontal" method="GET" action="{{ url('/user/list') }}">
                    {{ csrf_field() }}

                    {{-- フィルタリング：login_id --}}
                    <div class="form-group">
                        <label class="control-label col-md-2">login_id</label>
                        <div class="col-md-6">
                            <input name="login_id" type="text" class="form-control" value="{{ $login_id }}" />
                        </div>
                    </div>

                    {{-- フィルタリング：summoner_id --}}
                    <div class="form-group">
                        <label class="control-label col-md-2">summoner_id</label>
                        <div class="col-md-6">
                            <input name="summoner_id" type="text" class="form-control" value="{{ $summoner_id }}" />
                        </div>
                    </div>

                    {{-- フィルタリング：account_id --}}
                    <div class="form-group">
                        <label class="control-label col-md-2">account_id</label>
                        <div class="col-md-6">
                            <input name="account_id" type="text" class="form-control" value="{{ $account_id }}" />
                        </div>
                    </div>

                    {{-- フィルタリング：summoner_name --}}
                    <div class="form-group">
                        <label class="control-label col-md-2">summoner_name</label>
                        <div class="col-md-6">
                            <input name="summoner_name" type="text" class="form-control" value="{{ $summoner_name }}" />
                        </div>
                    </div>

                    {{-- ソート --}}
                    <div class="form-group">
                        <label class="control-label col-md-2">並び順</label>
                        <div class="col-md-3">
                            <select class="form-control" name="sort">
                                <option value="id"              @if ("id"             ==$sort) selected @endif>id</option>
                                <option value="created_at"      @if ("created_at"     ==$sort) selected @endif>created_at</option>
                                <option value="updated_at"      @if ("updated_at"     ==$sort) selected @endif>updated_at</option>
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
                        <th>login_id</th>
                        <th>summoner_id</th>
                        <th>account_id</th>
                        <th>summoner_name</th>
                        <th>discord_id</th>
                        <th>created_at</th>
                        <th>updated_at</th>
                    </tr>
                </thead>

                <tbody>
                @foreach($users as $user)
                    <tr>
                        <td>{{ $user->id }}</td>
                        <td>{{ $user->login_id }}</td>
                        <td>{{ $user->summoner_id }}</td>
                        <td>{{ $user->account_id }}</td>
                        <td>
                            <a href="{{ url('/user/detail/' . $user->id) }}">
                                {{ str_limit($user->summoner_name, 30) }}
                            </a>
                        </td>
                        <td>{{ $user->discord_id }}</td>
                        <td>{{ $user->created_at }}</td>
                        <td>{{ $user->updated_at }}</td>
                    </tr>
                @endforeach
                </tbody>

                <tfoot>
                    <tr>
                        <td colspan=5>
                            <div class="center-block text-center">
                                {{ $users->appends(['login_id'=>$login_id,'summoner_id'=>$summoner_id,'account_id'=>$account_id,'summoner_name'=>$summoner_name,'sort'=>$sort,'order'=>$order,])->render() }}
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
