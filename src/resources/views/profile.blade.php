@extends('moonlight::layouts.admin')

@section('title', 'Профиль')

@section('css')
@endsection

@section('js')
<script src="/packages/moonlight/js/profile.js"></script>
@endsection

@section('body')
<div class="main">
    <div class="container">
        <div class="path">
            <div class="part"><span>Профиль пользователя <b>{{ $login }}</b></span></div>
        </div>
        <form save="true" action="{{ route('moonlight.profile') }}" autocomplete="off" method="POST">
            <div class="edit">
                @if (sizeof($groups))
                <div class="row">
                    Состоит в группах:
                    @foreach ($groups as $k => $group)
                    <div><a href="{{ route('moonlight.group', $group->id) }}">{{ $group->name }}</a></div>
                    @endforeach
                </div>
                @endif
                @if ($loggedUser->isSuperUser())
                <div class="row">
                    <div><b>Суперпользователь</b></div>
                </div>
                @endif
                <div class="row">
                    <div>Дата создания:{{ $created_at->format('d.m.Y') }} <small>{{ $created_at->format('H:i:s') }}</small></div>
                    @if ($last_login)
                    <div>Последний вход: {{ $last_login->format('d.m.Y') }} <small>{{ $last_login->format('H:i:s') }}</small></div>
                    @endif
                </div>
                <div class="row">
                    <label>Имя:</label><span name="first_name" class="error"></span><br>
                    <input type="text" name="first_name" value="{{ $first_name }}" placeholder="Имя">
                </div>
                <div class="row">
                    <label>Фамилия:</label><span name="last_name" class="error"></span><br>
                    <input type="text" name="last_name" value="{{ $last_name }}" placeholder="Фамилия">
                </div>
                <div class="row">
                    <label>E-mail:</label><span name="email" class="error"></span><br>
                    <input type="text" name="email" value="{{ $email }}" placeholder="E-mail">
                </div>
                <div class="row">
                    <label>Аватар:</label><span name="photo" class="error"></span><br>
                    <div id="photo-container">
                        @if ($loggedUser->photoExists())
                        <img src="{{ $loggedUser->getPhotoSrc() }}">
                        @endif
                    </div>
                    <div><small class="red">Максимальный размер файла 1024 Кб</small></div>
                    <div><small class="red">Минимальный размер изображения 100&#215;100 пикселей</small></div>
                    <div class="loadfile">
                        <div class="file" name="photo">Выберите файл</div>
                        <span class="reset" name="photo">&#215;</span>
                        <div class="file-hidden"><input type="file" name="photo"></div>
                    </div>
                    <p>
                        <input type="checkbox" name="drop" id="drop" value="1">
                        <label for="drop">Удалить</label>
                    </p>
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
            <li class="active"><a href="{{ route('moonlight.profile') }}"><i class="fa fa-pencil"></i>Редактировать профиль</a></li>
            <li><a href="{{ route('moonlight.password') }}"><i class="fa fa-lock"></i>Сменить пароль</a></li>
            <li><a href="{{ route('moonlight.logout') }}"><i class="fa fa-sign-out"></i>Выход</a></li>
        </ul>
    </div>
</div>
@endsection