@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="panel panel-default">
                <div class="panel-heading">Functions</div>

                <div class="panel-body">
                    @if (session('status'))
                        <div class="alert alert-success">
                            {{ session('status') }}
                        </div>
                    @endif

                    @if (!Auth::user()->activate)
                        <div>
                            Not activated.
                        </div>
                    @else
                        <div>
                            <!-- Branding Image -->
                            <a href="{{ url('/team/list') }}">
                                チーム一覧
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
