@extends('moonlight::layouts.admin')

@section('title', $group->name)

@push('styles')
    <link media="all" type="text/css" rel="stylesheet" href="{{ asset('packages/moonlight/css/permissions.min.css') }}">
@endpush

@push('scripts')
    <script src="{{ asset('packages/moonlight/js/permissions.min.js') }}"></script>
@endpush

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
                    <tr data-item="{{ $item->getName() }}">
                        @if ($item->getElementPermissions())
                            <td class="title">
                                <a href="{{ route('moonlight.group.elements', [$group->id, $item->getName()]) }}">{{ $item->getTitle() }}</a><br>
                                <small>{{ $item->getName() }}</small>
                            </td>
                        @else
                            <td class="title">
                                {{ $item->getTitle() }}<br>
                                <small>{{ $item->getName() }}</small>
                            </td>
                        @endif
                        <td data-permission="deny" class="deny {{ $permissions[$item->getClassName()] == 'deny' ? 'active' : '' }}">
                            <span></span>
                        </td>
                        <td data-permission="view" class="view {{ $permissions[$item->getClassName()] == 'view' ? 'active' : '' }}">
                            <span></span>
                        </td>
                        <td data-permission="update" class="update {{ $permissions[$item->getClassName()] == 'update' ? 'active' : '' }}">
                            <span></span>
                        </td>
                        <td data-permission="delete" class="delete {{ $permissions[$item->getClassName()] == 'delete' ? 'active' : '' }}">
                            <span></span>
                        </td>
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
