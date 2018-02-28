@extends('moonlight::layouts.admin')

@section('title', 'Журнал')

@section('css')
<link media="all" type="text/css" rel="stylesheet" href="/packages/moonlight/css/log.css">
@endsection

@section('js')
<script src="/packages/moonlight/js/log.js"></script>
@endsection

@section('body')
<div class="main">
    <div class="container">
        <div class="path">
            <div class="part"><span>Журнал</span></div>
        </div>
        <form action="{{ route('moonlight.log') }}" autocomplete="off">
            <input type="hidden" name="action" value="search">
            <div class="search-form">
                <div class="search-form-params log">
                    <div class="row">
                        <div class="block active">
                            <div class="label">Комментарий:</div>
                            <input type="text" name="comments" value="{{ $comments }}" placeholder="Комментарий">
                        </div>
                        <div class="block active">
                            <div class="label">Пользователь:</div>
                            <select name="user">
                                <option value="">Все пользователи</option>
                                @foreach ($users as $user)
                                <option value="{{ $user->id }}"{{ $userId == $user->id ? ' selected' : '' }}>{{ $user->login }} ({{ $user->first_name }} {{ $user->last_name }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="block active">
                            <div class="label">Тип операции:</div>
                            <select name="type">
                                <option value="">Все операции</option>
                                @foreach ($userActionTypes as $id => $name)
                                <option value="{{ $id }}"{{ $typeId == $id ? ' selected' : '' }}>{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="block active">
                            <div class="label date">Дата:</div>
                            <div>
                                <input type="text" name="dateFrom" value="{{ $dateFrom }}" class="date" placeholder="От">
                                <input type="text" name="dateTo" value="{{ $dateTo }}" class="date" placeholder="До">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row-submit">
                    <input type="submit" value="Найти" class="btn">
                </div>
            </div>
        </form>
        @if ($html)
        <div class="list-container">
            {!! $html !!}
        </div>
        @endif
    </div>
</div>
@endsection
@section('sidebar')
<div class="sidebar">
    <div class="container">
        <ul class="menu">
            <li><a href="{{ route('moonlight.groups') }}"><i class="fa fa-folder-open"></i>Группы</a></li>
            <li><a href="{{ route('moonlight.users') }}"><i class="fa fa-user"></i>Пользователи</a></li>
            <li class="active"><a href="{{ route('moonlight.log') }}"><i class="fa fa-clock-o"></i>Журнал</a></li>
            <li><a href="{{ route('moonlight.profile') }}"><i class="fa fa-pencil"></i>Редактировать профиль</a></li>
            <li><a href="{{ route('moonlight.password') }}"><i class="fa fa-lock"></i>Сменить пароль</a></li>
            <li><a href="{{ route('moonlight.logout') }}"><i class="fa fa-sign-out"></i>Выход</a></li>
        </ul>
    </div>
</div>
@endsection