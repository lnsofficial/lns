@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">


        <div class="panel panel-default">
            <div class="panel-heading">[WIP]作業ログ一覧</div>

            <div class="panel-body">

                <form class="form-horizontal" method="GET" action="{{ url('/operator/list') }}">
                    {{ csrf_field() }}

                    <div class="form-group">
                        <label class="control-label col-md-12">
                            いつからいつまでとか、誰々のだけ、みたいなフィルターを後でつけれるとよさげ。
                        </label>
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
                        <th>とりあえずそのまんま出力</th>
                    </tr>
                </thead>

                <tbody>
                @foreach($worklogs as $worklog)
                    <tr>
                        <td>{{ $worklog }}</td>
                    </tr>
                @endforeach
                </tbody>
                </table>

            </div>
        </div>

    </div>





</div>
@endsection
