@extends('moonlight::layouts.admin')

@section('title', $user ? $user->login : 'Новый пользователь')

@section('js')
<script src="/packages/moonlight/js/user.js"></script>
@endsection

@section('body')
<div class="main">
    <div class="container">
        <div class="path">
            <div class="part"><a href="{{ route('moonlight.users') }}">Пользователи</a></div>
            <div class="divider">/</div>
            <div class="part"><span>{{ $user ? $user->login : 'Новый пользователь' }}</span></div>
        </div>
        <form save="true" action="{{ $user ? route('moonlight.user.save', $user->id ) : route('moonlight.user.add') }}" autocomplete="off" method="POST">
            <input type="hidden" name="back" value="{{ route('moonlight.users') }}">    
            <div class="edit">    
                <div class="row">
                    <label>Логин:</label><span name="login" class="error"></span><br>
                    <input type="text" name="login" value="{{ $user ? $user->login : '' }}" placeholder="Логин">
                </div>
                <div class="row">
                    <label>Имя:</label><span name="first_name" class="error"></span><br>
                    <input type="text" name="first_name" value="{{ $user ? $user->first_name : '' }}" placeholder="Имя">
                </div>
                <div class="row">
                    <label>Фамилия:</label><span name="last_name" class="error"></span><br>
                    <input type="text" name="last_name" value="{{ $user ? $user->last_name : '' }}" placeholder="Фамилия">
                </div>
                <div class="row">
                    <label>E-mail:</label><span name="email" class="error"></span><br>
                    @if (! $user)
                    <div><small class="red">На указанный адрес будет отправлено письмо<br>
                    с параметрами доступа</small></div>
                    @endif
                    <input type="text" name="email" value="{{ $user ? $user->email : '' }}" placeholder="E-mail">
                </div>
                <div class="row">
                    <p><input type="checkbox" name="banned" id="banned" value="1"{{ $user && $user->banned ? ' checked' : '' }}> <label for="banned">Заблокирован</label></p>
                </div>
                <div class="row">
                    <label>Группы:</label><span name="groups" class="error"></span><br>
                    @foreach ($groups as $group)
                    <p>
                        <input type="checkbox" name="groups[]" id="group_{{ $group->id }}" value="{{ $group->id }}"{{ isset($userGroups[$group->id]) ? ' checked' : '' }}>
                        <label for="group_{{ $group->id }}">{{ $group->name }}</label>
                    </p>
                    @endforeach
                </div>
                @if ($user)
                <div class="row">
                @if ($user->isSuperUser())
                    <div><b>Суперпользователь</b></div>
                @endif
                @if ($user->created_at)
                    <div>Дата создания: {{$user->created_at->format('d.m.Y')}} <small>{{$user->created_at->format('H:i:s')}}</small></div>
                @endif
                @if ($user->last_login)
                    <div>Последний логин: {{$user->last_login->format('d.m.Y')}} <small>{{$user->last_login->format('H:i:s')}}</small></div>
                @endif
                </div>
                @endif
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
            <li class="active"><a href="{{ route('moonlight.users') }}"><i class="fa fa-user"></i>Пользователи</a></li>
            <li><a href="{{ route('moonlight.log') }}"><i class="fa fa-clock-o"></i>Журнал</a></li>
            <li><a href="{{ route('moonlight.profile') }}"><i class="fa fa-pencil"></i>Редактировать профиль</a></li>
            <li><a href="{{ route('moonlight.password') }}"><i class="fa fa-lock"></i>Сменить пароль</a></li>
            <li><a href="{{ route('moonlight.logout') }}"><i class="fa fa-sign-out"></i>Выход</a></li>
        </ul>
    </div>
</div>
@endsection