@extends('moonlight::layouts.base')

@section('nav')
<nav>
    <div class="logo"><a href="{{ route('moonlight.home') }}">Moonlight</a></div>
    <ul class="menu">
        <li><a href="{{ route('moonlight.browse') }}">Страницы</a></li>
        <li><a href="{{ route('moonlight.search') }}">Поиск</a></li>
        <li><a href="{{ route('moonlight.trash') }}">Корзина</a></li>
        <li><a href="{{ route('moonlight.users') }}">Пользователи</a></li>
    </ul>
    <div class="avatar">
        @if ($loggedUser->photoExists())
        <img src="{{ $loggedUser->getPhotoSrc() }}">
        @else
        <img src="/packages/moonlight/img/avatar.png">
        @endif
    </div>
    <div class="dropdown">
        <ul>
            <li class="title">{{ $loggedUser->first_name }} {{ $loggedUser->last_name }}<br /><small>{{ $loggedUser->email }}</small></li>
            <li><a href="{{ route('moonlight.profile') }}">Редактировать профиль</a></li>
            <li><a href="{{ route('moonlight.password') }}">Сменить пароль</a></li>
            <li class="divider"></li>
            <li><a href="{{ route('moonlight.logout') }}">Выход</a></li>
        </ul>
    </div>
</nav>
@endsection