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
            <div class="part"><a href="{{ route('moonlight.group.items', $group->id) }}">{{ $group->name }}</a></div>
            <div class="divider">/</div>
            <div class="part"><span>{{ $item->getTitle() }}</span></div>
        </div>
        <div class="search-field">
            <input type="text" id="filter" placeholder="Введите название">
            <input type="hidden" name="url" value="{{ route('moonlight.group.elements', [$group->id, $item->getNameId()]) }}">
            <input type="hidden" name="group" value="{{ $group->id }}">
        </div>
        @if (sizeof($elements))
        <table class="permissions elements">
            <thead>
                <tr>
                    <th class="title">Название</th>
                    <th>Закрыто</th>
                    <th>Просмотр</th>
                    <th>Изменение</th>
                    <th>Удаление</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($elements as $classId => $element)
                <tr item="{{ $classId }}">
                    <td class="title">{{ $element->{$item->getMainProperty()} }}<br><small>{{ $classId }}</small></td>
                    <td permission="deny" class="deny {{ $permissions[$classId] == 'deny' ? 'active' : '' }}"><span></span></td>
                    <td permission="view" class="view {{ $permissions[$classId] == 'view' ? 'active' : '' }}"><span></span></td>
                    <td permission="update" class="update {{ $permissions[$classId] == 'update' ? 'active' : '' }}"><span></span></td>
                    <td permission="delete" class="delete {{ $permissions[$classId] == 'delete' ? 'active' : '' }}"><span></span></td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @else
        <div class="empty">Элементов не найдено.</div>
        @endif
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