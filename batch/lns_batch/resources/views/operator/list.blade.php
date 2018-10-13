@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">


        <div class="panel panel-default">
            <div class="panel-heading">管理ユーザー一覧</div>

            <div class="panel-body">

                <form class="form-horizontal" method="GET" action="{{ url('/operator/list') }}">
                    {{ csrf_field() }}

                    {{-- フィルタリング：name --}}
                    <div class="form-group">
                        <label class="control-label col-md-2">name</label>
                        <div class="col-md-6">
                            <input name="name" type="text" class="form-control" value="{{ $name }}" />
                        </div>
                    </div>

                    {{-- フィルタリング：email --}}
                    <div class="form-group">
                        <label class="control-label col-md-2">email</label>
                        <div class="col-md-6">
                            <input name="email" type="text" class="form-control" value="{{ $email }}" />
                        </div>
                    </div>

                    {{-- フィルタリング：activate --}}
                    <div class="form-group">
                        <label class="control-label col-md-2">activate</label>
                        <div class="col-md-6">
                            <select class="form-control" name="activate">
                                <option value="">指定なし</option>
                                @foreach(App\Models\Operator::ACTIVATE_STATUS_MESSAGES as $key=>$a_val)
                                <option value={{ $key }} @if ($activate===$key) selected @endif>{{ $a_val }}</option>
                                @endforeach
                            </select>
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
                        <th>name</th>
                        <th>email</th>
                        <th>activate</th>
                        <th>created_at</th>
                        <th>updated_at</th>
                    </tr>
                </thead>

                <tbody>
                @foreach($operators as $operator)
                    <tr>
                        <td>{{ $operator->id }}</td>
                        <td>
                            <a href="{{ url('/operator/detail/' . $operator->id) }}">
                                {{ str_limit($operator->name, 30) }}
                            </a>
                        </td>
                        <td>{{ $operator->email }}</td>
                        <td class="{{ App\Models\Operator::ACTIVATE_STATUS_COLOR_CLASS['table'][$operator->activate] }}">
                            {{ App\Models\Operator::ACTIVATE_STATUS_MESSAGES[$operator->activate] }}
                        </td>
                        <td>{{ $operator->created_at }}</td>
                        <td>{{ $operator->updated_at }}</td>
                    </tr>
                @endforeach
                </tbody>

                <tfoot>
                    <tr>
                        <td colspan=5>
                            <div class="center-block text-center">
                                {{ $operators->appends(['name'=>$name,'email'=>$email,'activate'=>$activate,'sort'=>$sort,'order'=>$order,])->render() }}
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
