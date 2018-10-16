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
                    <div class="col-md-12">
                        ユーザー情報(・・調整中・・)
                    </div>
                </div>
            </div>

            {{-- パネルボディ --}}
            <div class="panel-body">
                <div class="row">
                    <div class="col-md-2">
                        ユーザーID
                    </div>
                    <div class="col-md-10">
                        {{ $user->id }}
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-2">
                        ログインID
                    </div>
                    <div class="col-md-10">
                        {{ $user->login_id }}
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-2">
                        サモナーID
                    </div>
                    <div class="col-md-10">
                        {{ $user->summoner_id }}
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-2">
                        アカウントID
                    </div>
                    <div class="col-md-10">
                        {{ $user->account_id }}
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-2">
                        サモナー名
                    </div>
                    <div class="col-md-10">
                        {{ $user->summoner_name }}
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-2">
                        サモナー名(かな)
                    </div>
                    <div class="col-md-10">
                        {{ $user->summoner_name_kana }}
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-2">
                        ディスコードID
                    </div>
                    <div class="col-md-10">
                        {{ $user->discord_id }}
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-2">
                        ランク
                    </div>
                    <div class="col-md-10">
                        {{ $user->rank()->tier }} {{ $user->rank()->rank }}&nbsp;(ポイント：{{ App\Models\UserRank::RANK_LIST[$user->rank()->tier][$user->rank()->rank] }})
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-2">
                        メインロール
                    </div>
                    <div class="col-md-10">
                        {{ App\Models\Team::ROLE_LABELS[$user->main_role] }}
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-2">
                        コメント
                    </div>
                    <div class="col-md-10">
                        {{ $user->comment }}
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-2">
                        created_at
                    </div>
                    <div class="col-md-10">
                        {{ $user->created_at }}
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-2">
                        updated_at
                    </div>
                    <div class="col-md-10">
                        {{ $user->updated_at }}
                    </div>
                </div>

            </div>{{-- panel-body --}}
        </div>{{-- panel panel-default --}}


    </div>{{-- row --}}
</div>{{-- container --}}
@endsection
