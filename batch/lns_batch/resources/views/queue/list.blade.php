@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">


        <div class="panel panel-default">
            <div class="panel-heading">APIキュー一覧</div>

            <div class="panel-body">

                <form class="form-horizontal" method="GET" action="{{ url('/queue/list') }}">
                    {{ csrf_field() }}

                    {{-- フィルタリング：action --}}
                    <div class="form-group">
                        <label class="control-label col-md-2">action</label>
                        <div class="col-md-6">
                            <select class="form-control" name="action">
                                <option value="">指定なし</option>
                                @foreach(App\Models\ApiQueue::ACTION_MESSAGES as $key=>$ac_val)
                                <option value={{ $key }} @if ($action===$key) selected @endif>{{ $ac_val }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- フィルタリング：state --}}
                    <div class="form-group">
                        <label class="control-label col-md-2">state</label>
                        <div class="col-md-6">
                            <select class="form-control" name="state">
                                <option value="">指定なし</option>
                                @foreach(App\Models\ApiQueue::STATE_MESSAGES as $key=>$st_val)
                                <option value={{ $key }} @if ($state===$key) selected @endif>{{ $st_val }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- フィルタリング：priority --}}
                    <div class="form-group">
                        <label class="control-label col-md-2">priority</label>
                        <div class="col-md-6">
                            <input name="priority" type="text" class="form-control" value="{{ $priority }}" />
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
                        <th>action</th>
                        <th>state</th>
                        <th>priority</th>
                        <th>payload</th>
                        <th>result</th>
                        <th>created_at</th>
                        <th>updated_at</th>
                    </tr>
                </thead>

                <tbody>
                @foreach($queues as $queue)
                    <tr>
                        <td>
                            <a href="{{ url('/queue/detail/' . $queue->id) }}">
                                {{ $queue->id }}</td>
                            </a>
                        <td>
                            {{ $queue->viewAction() }}
                        </td>
                        <td style="background-color: {{ App\Models\ApiQueue::STATE_COLOR_CLASS['background-color'][$queue->state] }}">
                            {{ $queue->viewState() }}
                        </td>
                        <td>
                            {{ $queue->priority }}
                        </td>
                        <td>
                            <a href="{{ $queue->viewPayloadLink() }}">
                            {{ $queue->payload }}
                        </td>
                        <td>
                            @if( !empty($queue->result) )
                                {{ $queue->viewResult() }}
                            @endif
                        </td>
                        <td>{{ $queue->created_at }}</td>
                        <td>{{ $queue->updated_at }}</td>
                    </tr>
                @endforeach
                </tbody>

                <tfoot>
                    <tr>
                        <td colspan=5>
                            <div class="center-block text-center">
                                {{ $queues->appends(['action'=>$action,'state'=>$state,'priority'=>$priority,'sort'=>$sort,'order'=>$order,])->render() }}
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
