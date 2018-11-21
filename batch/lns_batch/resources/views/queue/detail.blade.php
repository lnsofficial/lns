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
                        キュー情報
                    </div>
                </div>
            </div>

            {{-- パネルボディ --}}
            <div class="panel-body">
                <form class="form-horizontal" method="POST" action="{{ url('/queue/detail/' . $queue->id) }}">
                {{ csrf_field() }}

                <div class="row">
                    <div class="col-md-2">
                        APIキューID
                    </div>
                    <div class="col-md-10">
                        {{ $queue->id }}
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-2">
                        種別
                    </div>
                    <div class="col-md-10">
                        {{ $queue->viewAction() }}
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-2">
                        状態
                    </div>
                    <div class="col-md-4">
                        <select name="state" class="form-control">
                            @foreach(App\Models\ApiQueue::STATE_MESSAGES as $key=>$st_val)
                                <option value={{ $key }} @if ($queue->state==$key) selected @endif>{{ $st_val }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                            <button type="submit" class="btn btn-primary">
                                Update
                            </button>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-2">
                        処理優先度
                    </div>
                    <div class="col-md-10">
                        {{ $queue->priority }}
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-2">
                        payload
                    </div>
                    <div class="col-md-10">
                        {{ $queue->payload }}
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-2">
                        処理結果
                    </div>
                    <div class="col-md-10">
                        {{ $queue->result }}
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-2">
                        created_at
                    </div>
                    <div class="col-md-10">
                        {{ $queue->created_at }}
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-2">
                        updated_at
                    </div>
                    <div class="col-md-10">
                        {{ $queue->updated_at }}
                    </div>
                </div>

                </form>
            </div>{{-- panel-body --}}
        </div>{{-- panel panel-default --}}


    </div>{{-- row --}}
</div>{{-- container --}}
@endsection
