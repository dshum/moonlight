@extends('moonlight::layouts.admin')

@section('title', 'Группы')

@push('styles')
    <link media="all" type="text/css" rel="stylesheet" href="{{ asset('packages/moonlight/css/groups.min.css') }}">
@endpush

@push('scripts')
    <script src="{{ asset('packages/moonlight/js/groups.min.js') }}"></script>
@endpush

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
                            <th class="remove">
                                <i class="fa fa-times-circle"></i>
                            </th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach ($groups as $group)
                            <tr data-group="{{ $group->id }}" data-name="{{ $group->name }}" data-delete-url="{{ route('moonlight.groups.destroy', $group->id) }}">
                                <td><a href="{{ route('moonlight.groups.edit', $group->id) }}">{{ $group->name }}</a></td>
                                <td>
                                    <div>
                                        <a href="{{ route('moonlight.groups.items.index', $group->id) }}">{{ $group->getPermissionTitle() }}</a>
                                    </div>
                                    @if ($group->hasAccess('admin'))
                                        <div><small>Управление пользователями</small></div>
                                    @endif
                                </td>
                                <td>{{ $group->created_at->format('d.m.Y') }}
                                    <br><small>{{ $group->created_at->format('H:i:s') }}</small>
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
                <a href="{{ route('moonlight.groups.create') }}" class="addnew">Добавить группу<i class="fa fa-arrow-right"></i></a>
            </div>
        </div>
    </div>
    @include('moonlight::components.groups.confirm.delete')
@endsection
@section('sidebar')
    @include('moonlight::components.sidebar.admin', ['active' => 'groups'])
@endsection
