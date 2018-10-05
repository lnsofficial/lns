@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-8 col-md-offset-2">

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
                <div class="panel-heading">
					<div class="row">
						<div class="col-md-4">
							チーム詳細
						</div>
						<div class="col-md-8 text-right">
							<a href="{{ url('/team/list') }}">
								チーム一覧
							</a>
						</div>
					</div>
				</div>



{{--
                    <form class="form-horizontal" method="POST" action="{{ route('login') }}">
                        {{ csrf_field() }}
                        <div class="form-group">
                            <div class="col-md-8 col-md-offset-4">
                                <button type="submit" class="btn btn-primary">
                                    Logo status Update
                                </button>

                                <a class="btn btn-link" href="{{ route('password.request') }}">
                                    Forgot Your Password?
                                </a>
                            </div>
                        </div>
                    </form>
--}}



                <div class="panel-body">
	                <form class="form-horizontal" method="POST" action="{{ url('/team/detail/' . $team->id) }}">
                    {{ csrf_field() }}

					<div class="row">
						<div class="col-md-4">
							チームID
						</div>
						<div class="col-md-8">
							{{ $team->id }}
						</div>
					</div>
					<div class="row">
						<div class="col-md-4">
							チーム名
						</div>
						<div class="col-md-8">
							{{ $team->team_name }}
						</div>
					</div>
					<div class="row">
						<div class="col-md-4">
							チームタグ名
						</div>
						<div class="col-md-8">
							{{ $team->team_tag }}
						</div>
					</div>
					<div class="row">
						<div class="col-md-4">
							チームロゴ状況
						</div>
						<div class="col-md-5">
							<select name="logo_status" class="form-control">
								<option value="{{ App\Models\Team::LOGO_STATUS_UNREGISTERED      }}" @if($team->logo_status == App\Models\Team::LOGO_STATUS_UNREGISTERED)      selected @endif>未登録</option>
								<option value="{{ App\Models\Team::LOGO_STATUS_UNAUTHENTICATED   }}" @if($team->logo_status == App\Models\Team::LOGO_STATUS_UNAUTHENTICATED)   selected @endif>未検閲</option>
								<option value="{{ App\Models\Team::LOGO_STATUS_AUTHENTICATED     }}" @if($team->logo_status == App\Models\Team::LOGO_STATUS_AUTHENTICATED)     selected @endif>検閲済み</option>
								<option value="{{ App\Models\Team::LOGO_STATUS_AUTHENTICATEERROR }}" @if($team->logo_status == App\Models\Team::LOGO_STATUS_AUTHENTICATEERROR) selected @endif>検閲NG</option>
							</select>
						</div>
						<div class="col-md-3">
								<button type="submit" class="btn btn-primary">
									Update
								</button>
						</div>
					</div>
					</form>


					<div class="row">
						<div class="col-md-4">
							チームロゴ(オリジナル)
						</div>
						<div class="col-md-8">
							@if ( Storage::exists('public/logo/' . $team->id . '_logo.png') )
								<img width="200px" height="200px" src="{{ asset('/storage/logo/' . $team->id . '_logo.png') }}" />
							@else
								なし
							@endif
						</div>
					</div>
					<div class="row">
						<div class="col-md-4">
							チームロゴ(運営修正版)
						</div>
						<div class="col-md-8">
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
						<div class="col-md-4">
							修正版ロゴアップ
						</div>
						<div class="col-md-5">
								<input id="logo" type="file" name="logo" />
						</div>
						<div class="col-md-3">
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
</div>
@endsection
