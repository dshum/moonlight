@extends('moonlight::layouts.admin')

@section('title', 'Группы')

@section('css')
<link media="all" type="text/css" rel="stylesheet" href="/packages/moonlight/css/groups.css">
@endsection

@section('js')
<script src="/packages/moonlight/js/groups.js"></script>
@endsection

@section('body')
<div class="main">
    <div class="container">
        <div class="path">
            <div class="part"><span>Группы</span></div>
        </div>
        @if (sizeof($groups))
        <div class="item">
            <table class="groups elements">
                <thead>
                    <tr>
                        <th>Название</th>
                        <th>Права доступа</th>
                        <th>Дата</th>
                        <th class="remove"><i class="fa fa-times-circle"></i></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($groups as $group)
                    <tr group="{{ $group->id }}">
                        <td><a href="{{ route('moonlight.group', $group->id) }}">{{ $group->name }}</a></td>
                        <td>
                            <a href="{{ route('moonlight.group.items', $group->id) }}">{{ $group->getPermissionTitle() }}</a>
                            @if ($group->hasAccess('admin'))
                            <div><small>Управление пользователями</small></div>
                            @endif
                        </td>
                        <td>{{ $group->created_at->format('d.m.Y') }}<br><small>{{ $group->created_at->format('H:i:s') }}</small></td>
                        <td class="remove" name="{{ $group->name }}" url="{{ route('moonlight.group.delete', $group->id) }}"><i class="fa fa-times-circle"></i></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif        
        <div>
            <a href="{{ route('moonlight.group.create') }}" class="addnew">Добавить группу<i class="fa fa-arrow-right"></i></a>
        </div>
    </div>
</div>
<div class="confirm">
    <div class="wrapper">
        <div class="container">
            <div class="content"></div>
            <div class="bottom">
                <input type="button" value="Удалить" class="btn danger remove">
                <input type="button" value="Отмена" class="btn cancel">
            </div>
        </div>
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