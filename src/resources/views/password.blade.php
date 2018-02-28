@extends('moonlight::layouts.admin')

@section('title', 'Пароль')

@section('css')
@endsection

@section('js')
<script src="/packages/moonlight/js/password.js"></script>
@endsection

@section('body')
<div class="main">
    <div class="container">
        <div class="path">
            <div class="part"><span>Пароль</b></span></div>
        </div>
        <form save="true" action="{{ route('moonlight.password') }}" autocomplete="off" method="POST">
            <div class="edit">
                <div class="row">
                    <label>Текущий пароль:</label><span name="password_old" class="error"></span><br>
                    <input type="password" name="password_old" placeholder="Текущий пароль">
                </div>
                <div class="row">
                    <label>Новый пароль:</label><span name="password" class="error"></span><br>
                    <input type="password" name="password" placeholder="Новый пароль">
                </div>
                <div class="row">
                    <label>Подтверждение:</label><span name="password_confirmation" class="error"></span><br>
                    <input type="password" name="password_confirmation" placeholder="Подтверждение">
                </div>
                <div class="row submit">
                    <input type="submit" value="Сохранить" class="btn">
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
@section('sidebar')
<div class="sidebar">
    <div class="container">
        <ul class="menu">
            <li><a href="{{ route('moonlight.groups') }}"><i class="fa fa-folder-open"></i>Группы</a></li>
            <li><a href="{{ route('moonlight.users') }}"><i class="fa fa-user"></i>Пользователи</a></li>
            <li><a href="{{ route('moonlight.log') }}"><i class="fa fa-clock-o"></i>Журнал</a></li>
            <li><a href="{{ route('moonlight.profile') }}"><i class="fa fa-pencil"></i>Редактировать профиль</a></li>
            <li class="active"><a href="{{ route('moonlight.password') }}"><i class="fa fa-lock"></i>Сменить пароль</a></li>
            <li><a href="{{ route('moonlight.logout') }}"><i class="fa fa-sign-out"></i>Выход</a></li>
        </ul>
    </div>
</div>
@endsection