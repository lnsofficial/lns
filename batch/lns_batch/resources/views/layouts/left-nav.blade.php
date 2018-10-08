<div class="panel panel-default">
    <div class="panel-heading">Menu</div>


    <ul class="nav flex-column">
        <li class="nav-item {{ Auth::user()->activate ? '' : 'disabled' }}">
            <a class="nav-link" href="{{ url('/user/list') }}">ユーザー</a>
        </li>
        <li class="nav-item {{ Auth::user()->activate ? '' : 'disabled' }}">
            <a class="nav-link" href="{{ url('/team/list') }}">チーム</a>
        </li>
        <li class="nav-item {{ Auth::user()->activate ? '' : 'disabled' }}">
            <a class="nav-link" href="{{ url('/match/list') }}">マッチ</a>
        </li>
        <li class="nav-item {{ Auth::user()->activate ? '' : 'disabled' }}">
            <a class="nav-link" href="{{ url('/notice/list') }}">お知らせ</a>
        </li>
        <li class="nav-item {{ Auth::user()->activate ? '' : 'disabled' }}">
            <a class="nav-link" href="{{ url('/worklog/list') }}">作業ログ</a>
        </li>
        <li class="nav-item {{ Auth::user()->activate ? '' : 'disabled' }}">
            <a class="nav-link" href="{{ url('/operator/list') }}">管理ツールユーザー</a>
        </li>
{{--
        <li class="nav-item {{ Auth::user()->activate ? '' : 'disabled' }}">
            <a class="nav-link" href="#"></a>
        </li>
--}}
    </ul>

</div>
