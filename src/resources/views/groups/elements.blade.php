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
                <div class="part"><a href="{{ route('moonlight.group.items', $group->id) }}">{{ $group->name }}</a></div>
                <div class="divider">/</div>
                <div class="part"><span>{{ $item->getTitle() }}</span></div>
            </div>
            <div class="search-field">
                <input type="text" id="filter" placeholder="Введите название">
                <input type="hidden" name="url" value="{{ route('moonlight.group.elements', [$group->id, $item->getName()]) }}">
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
                    @foreach ($elements as $element)
                        <tr data-item="{{ $element->id }}">
                            <td class="title">
                                {{ $element->{$item->getMainProperty()} }}<br>
                                <small>{{ $item->getName() }}.{{ $element->id }}</small>
                            </td>
                            <td data-permission="deny" class="deny {{ $permissions[$element->id] == 'deny' ? 'active' : '' }}">
                                <span></span>
                            </td>
                            <td data-permission="view" class="view {{ $permissions[$element->id] == 'view' ? 'active' : '' }}">
                                <span></span>
                            </td>
                            <td data-permission="update" class="update {{ $permissions[$element->id] == 'update' ? 'active' : '' }}">
                                <span></span>
                            </td>
                            <td data-permission="delete" class="delete {{ $permissions[$element->id] == 'delete' ? 'active' : '' }}">
                                <span></span>
                            </td>
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
