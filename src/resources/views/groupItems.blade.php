@extends('moonlight::layouts.admin')

@section('title', $group->name)

@section('css')
<link media="all" type="text/css" rel="stylesheet" href="/packages/moonlight/css/permissions.css">
@endsection

@section('js')
<script src="/packages/moonlight/js/permissions.js"></script>
@endsection

@section('body')
<div class="main">
    <div class="container">
        <div class="path">
            <div class="part"><a href="{{ route('moonlight.groups') }}">Группы</a></div>
            <div class="divider">/</div>
            <div class="part"><span>{{ $group->name }}</span></div>
        </div>
        <div class="search-field">
            <input type="text" id="filter" placeholder="Введите название">
            <input type="hidden" name="url" value="{{ route('moonlight.group.items', $group->id) }}">
            <input type="hidden" name="group" value="{{ $group->id }}">
        </div>
        <table class="permissions elements">
            <thead>
                <tr>
                    <th class="title">Класс элемента</th>
                    <th>Закрыто</th>
                    <th>Просмотр</th>
                    <th>Изменение</th>
                    <th>Удаление</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($items as $item)
                <tr item="{{ $item->getNameId() }}">
                    @if ($item->getElementPermissions())
                    <td class="title"><a href="{{ route('moonlight.group.elements', [$group->id, $item->getNameId()]) }}">{{ $item->getTitle() }}</a><br><small>{{ $item->getNameId() }}</small></td>
                    @else
                    <td class="title">{{ $item->getTitle() }}<br><small>{{ $item->getNameId() }}</small></td>
                    @endif
                    <td permission="deny" class="deny {{ $permissions[$item->getNameId()] == 'deny' ? 'active' : '' }}"><span></span></td>
                    <td permission="view" class="view {{ $permissions[$item->getNameId()] == 'view' ? 'active' : '' }}"><span></span></td>
                    <td permission="update" class="update {{ $permissions[$item->getNameId()] == 'update' ? 'active' : '' }}"><span></span></td>
                    <td permission="delete" class="delete {{ $permissions[$item->getNameId()] == 'delete' ? 'active' : '' }}"><span></span></td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection

@section('sidebar')
<div class="sidebar">
    <div class="container">
        <ul class="menu">
            <li class="active"><a href="{{ route('moonlight.groups') }}"><i class="fa fa-folder-open"></i>Группы</a></li>
            <li><a href="{{ route('moonlight.users') }}"><i class="fa fa-user"></i>Пользователи</a></li>
            <li><a href="{{ route('moonlight.log') }}"><i class="fa fa-clock-o"></i>Журнал</a></li>
            <li><a href="{{ route('moonlight.profile') }}"><i class="fa fa-pencil"></i>Редактировать профиль</a></li>
            <li><a href="{{ route('moonlight.password') }}"><i class="fa fa-lock"></i>Сменить пароль</a></li>
            <li><a href="{{ route('moonlight.logout') }}"><i class="fa fa-sign-out"></i>Выход</a></li>
        </ul>
    </div>
</div>
@endsection