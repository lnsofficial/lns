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

                <form class="form-horizontal" method="POST" action="{{ url('/operator/detail/' . $operator->id) }}">
                {{ csrf_field() }}
                <div class="row">
                    <div class="col-md-2">
                        activate
                    </div>
                    <div class="col-md-4">
                        <select name="activate" class="form-control">
                            @foreach(App\Models\Operator::ACTIVATE_STATUS_MESSAGES as $key=>$ac_val)
                                <option value={{ $key }} @if ($operator->activate==$key) selected @endif>{{ $ac_val }}</option>
                            @endforeach
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
