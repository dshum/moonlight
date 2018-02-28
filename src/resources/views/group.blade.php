@extends('moonlight::layouts.admin')

@section('title', $group ? $group->name : 'Новая группа')

@section('css')
@endsection

@section('js')
<script src="/packages/moonlight/js/group.js"></script>
@endsection

@section('body')
<div class="main">
    <div class="container">
        <div class="path">
            <div class="part"><a href="{{ route('moonlight.groups') }}">Группы</a></div>
            <div class="divider">/</div>
            <div class="part"><span>{{ $group ? $group->name : 'Новая группа' }}</span></div>
        </div>
        <form save="true" action="{{ $group ? route('moonlight.group.save', $group->id) : route('moonlight.group.add') }}" autocomplete="off" method="POST">
            <input type="hidden" name="back" value="{{ route('moonlight.groups') }}">
            <div class="edit">    
                <div class="row">
                    <label>Название:</label><span name="name" class="error"></span><br>
                    <input type="text" name="name" value="{{ $group ? $group->name : '' }}" placeholder="Название">
                </div>
                <div class="row">
                    <p><input type="checkbox" name="admin" id="admin" value="1"{{ $group && $group->hasAccess('admin') ? ' checked' : '' }}> <label for="admin">Управление пользователями</label></p>
                </div>
                <div class="row">
                    <label>Доступ к элементам по умолчанию:<span name="default_permission" class="error"></span></label>
                    <p><input type="radio" name="default_permission" id="permission_deny" value="deny"{{ $group && $group->default_permission == 'deny' ? ' checked' : '' }}> <label for="permission_deny">Доступ закрыт</label></p>
                    <p><input type="radio" name="default_permission" id="permission_view" value="view"{{ $group && $group->default_permission == 'view' ? ' checked' : '' }}> <label for="permission_view">Просмотр</label></p>
                    <p><input type="radio" name="default_permission" id="permission_update" value="update"{{ $group && $group->default_permission == 'update' ? ' checked' : '' }}> <label for="permission_update">Изменение</label></p>
                    <p><input type="radio" name="default_permission" id="permission_delete" value="delete"{{ $group && $group->default_permission == 'delete' ? ' checked' : '' }}> <label for="permission_delete">Удаление</label></p>
                </div>
                <div class="row">
                    @if ($group && $group->created_at)
                    Дата создания: {{ $group->created_at->format('d.m.Y') }} <small>{{ $group->created_at->format('H:i:s') }}</small><br>
                    @endif
                    @if ($group && $group->updated_at)
                    Последнее изменение: {{ $group->updated_at->format('d.m.Y') }} <small>{{ $group->updated_at->format('H:i:s') }}</small><br>
                    @endif
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