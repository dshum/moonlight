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
                            <tr data-user="{{ $user->id }}" data-name="{{ $user->first_name }} {{ $user->last_name }}" data-delete-url="{{ route('moonlight.users.destroy', $user->id) }}">
                                <td><a href="{{ route('moonlight.users.edit', $user->id) }}">{{ $user->login }}</a></td>
                                <td>{{ $user->first_name }} {{ $user->last_name }}<br><small>{{ $user->email }}</small>
                                </td>
                                <td>
                                    @foreach ($user->groups as $group)
                                        <div>
                                            <a href="{{ route('moonlight.groups.edit', $group->id) }}">{{ $group->name }}</a>
                                        </div>
                                    @endforeach
                                    @if ($user->isSuperUser())
                                        <div>Суперпользователь</div>
                                    @endif
                                </td>
                                <td>{{ $user->banned ? 'Заблокирован' : 'Активен' }}</td>
                                <td>{{ $user->created_at->format('d.m.Y') }}<br>
                                    <small>{{ $user->created_at->format('H:i:s') }}</small>
                                </td>
                                <td class="remove">
                                    <i class="fa fa-times-circle"></i>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
            <div>
                <a href="{{ route('moonlight.users.create') }}" class="addnew">Добавить пользователя<i class="fa fa-arrow-right"></i></a>
            </div>
        </div>
    </div>
    @include('moonlight::components.groups.confirm.delete')
@endsection

@section('sidebar')
    @include('moonlight::components.sidebar.admin', ['active' => 'users'])
@endsection
