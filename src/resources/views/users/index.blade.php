@extends('moonlight::layouts.admin')

@section('title', 'Пользователи')

@push('styles')
    <link media="all" type="text/css" rel="stylesheet" href="{{ asset('packages/moonlight/css/users.min.css') }}">
@endpush

@push('scripts')
    <script src="{{ asset('packages/moonlight/js/users.min.js') }}"></script>
@endpush

@section('body')
    <div class="main">
        <div class="container">
            <div class="path">
                <div class="part"><span>Пользователи</span></div>
            </div>
            @if (sizeof($users))
                <div class="item">
                    <table class="users elements">
                        <thead>
                        <tr>
                            <th>Логин</th>
                            <th>Имя</th>
                            <th>Группы</th>
                            <th>Статус</th>
                            <th>Дата</th>
                            <th class="remove"><i class="fa fa-times-circle"></i></th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach ($users as $user)
                            <tr data-user="{{ $user->id }}">
                                <td><a href="{{ route('moonlight.user', $user->id) }}">{{ $user->login }}</a></td>
                                <td>{{ $user->first_name }} {{ $user->last_name }}<br><small>{{ $user->email }}</small></td>
                                <td>
                                    @foreach ($user->groups as $group)
                                        <div>
                                            <a href="{{ route('moonlight.group', $group->id) }}">{{ $group->name }}</a>
                                        </div>
                                    @endforeach
                                    @if ($user->isSuperUser())
                                        <div>Суперпользователь</div>
                                    @endif
                                </td>
                                <td>{{ $user->banned ? 'Заблокирован' : 'Активен' }}</td>
                                <td>{{ $user->created_at->format('d.m.Y') }}
                                    <br><small>{{ $user->created_at->format('H:i:s') }}</small>
                                </td>
                                <td class="remove" data-name="{{ $user->first_name }} {{ $user->last_name }}" data-url="{{ route('moonlight.user.delete', $user->id) }}">
                                    <i class="fa fa-times-circle"></i>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
            <div>
                <a href="{{ route('moonlight.user.create') }}" class="addnew">Добавить пользователя<i class="fa fa-arrow-right"></i></a>
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
