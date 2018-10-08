@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12">
            @if (!Auth::user()->activate)
            <div class="panel panel-danger">
            @else
            <div class="panel panel-info">
            @endif
                <div class="panel-heading">Notice</div>

                <div class="panel-body">
                    @if (session('status'))
                        <div class="alert alert-success">
                            {{ session('status') }}
                        </div>
                    @endif

                    @if (!Auth::user()->activate)
                        <div>
                            「{{Auth::user()->name}}さん、どこかで会ったかしら？」
                        </div>
                        <div>
                            <img src="{{ asset('img/20180422_lulu.png') }}" />
                        </div>
                        <div>
                            ※アカウントがactivateされていないので、Slackにて<br />
                            @スミス さん @ラクラク / webエンジニア さん<br />
                            に依頼してください～。
                        </div>
                    @else
                        <div>
                            「データはね、壊すためにあるのよ。codeもdbもみんな……ね！」
                        </div>
                        <div>
                            <img src="{{ asset('img/20180819_jinx.png') }}" />
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
