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
                            チーム基本情報
                        </div>
                    </div>
                </div>

                {{-- パネルボディ --}}
                <div class="panel-body">
                    <div class="row">
                        <div class="col-md-2">
                            チームID
                        </div>
                        <div class="col-md-10">
                            {{ $team->id }}
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-2">
                            チーム名
                        </div>
                        <div class="col-md-10">
                            {{ $team->team_name }}
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-2">
                            チームタグ名
                        </div>
                        <div class="col-md-10">
                            {{ $team->team_tag }}
                        </div>
                    </div>

                </div>
            </div>


    </div>



    <div class="row">


            <div class="panel panel-default">

                {{-- パネルヘッダ --}}
                <div class="panel-heading">
                    <div class="row">
                        <div class="col-md-2">
                            チームロゴ情報
                        </div>
                    </div>
                </div>


                {{-- パネルボディ --}}
                <div class="panel-body">
                    <form class="form-horizontal" method="POST" action="{{ url('/team/detail/' . $team->id) }}">
                    {{ csrf_field() }}

                    <div class="row">
                        <div class="col-md-2">
                            チームロゴ状況
                        </div>
                        <div class="col-md-4">
                            <select name="logo_status" class="form-control">
                                <option value="{{ App\Models\Team::LOGO_STATUS_UNREGISTERED      }}" @if($team->logo_status == App\Models\Team::LOGO_STATUS_UNREGISTERED)      selected @endif>未登録</option>
                                <option value="{{ App\Models\Team::LOGO_STATUS_UNAUTHENTICATED   }}" @if($team->logo_status == App\Models\Team::LOGO_STATUS_UNAUTHENTICATED)   selected @endif>未検閲</option>
                                <option value="{{ App\Models\Team::LOGO_STATUS_AUTHENTICATED     }}" @if($team->logo_status == App\Models\Team::LOGO_STATUS_AUTHENTICATED)     selected @endif>検閲済み</option>
                                <option value="{{ App\Models\Team::LOGO_STATUS_AUTHENTICATEERROR }}" @if($team->logo_status == App\Models\Team::LOGO_STATUS_AUTHENTICATEERROR) selected @endif>検閲NG</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                                <button type="submit" class="btn btn-primary">
                                    Update
                                </button>
                        </div>
                    </div>
                    </form>

                    <div class="row">
                        <div class="col-md-2">
                            チームロゴ(オリジナル)
                        </div>
                        <div class="col-md-10">
                            @if ( Storage::exists('public/logo/' . $team->id . '_logo.png') )
                                <img width="200px" height="200px" src="{{ asset('/storage/logo/' . $team->id . '_logo.png') }}" />
                            @else
                                なし
                            @endif
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-2">
                            チームロゴ(運営修正版)
                        </div>
                        <div class="col-md-10">
                            @if ( Storage::exists('public/logo/modified/' . $team->id . '_logo.png') )
                                <img width="200px" height="200px" src="{{ asset('/storage/logo/modified/' . $team->id . '_logo.png') }}" />
                            @else
                                なし
                            @endif
                        </div>
                    </div>

                    <div class="row">
                        <form class="form-horizontal" method="POST" action="{{ url('/team/logo/' . $team->id) }}" enctype="multipart/form-data">
                        {{ csrf_field() }}
                        <div class="col-md-2">
                            修正版ロゴアップ
                        </div>
                        <div class="col-md-4">

                            <input id="logo" name="logo" type="file" onchange="document.getElementById('logoFileName').value = document.getElementById('logo');" style="display:none" />
                            <div class="input-group">
                                <input type="text" id="logoFileName" class="form-control" readonly placeholder="select file..." />
                                <span class="input-group-btn">
                                    <button type="button" class="btn btn-info" onclick="document.getElementById('logo').click();">
                                        Browse
                                    </button>
                                </span>
                            </div>
                            <p class="help-block">png形式、最大512x512、サイズ上限1M</p>

                        </div>
                        <div class="col-md-6">
                                <button type="submit" class="btn btn-primary">
                                    Upload
                                </button>
                        </div>
                        </form>
                    </div>

                </div>
            </div>


    </div>
</div>
@endsection
