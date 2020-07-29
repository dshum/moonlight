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
                <div class="part"><a href="{{ route('moonlight.groups.index') }}">Группы</a></div>
                <div class="divider">/</div>
                <div class="part"><span>{{ $group->name }}</span></div>
            </div>
            <div class="search-field">
                <input type="text" id="filter" placeholder="Введите название">
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
                    <tr data-url="{{ route('moonlight.groups.items.update', [$group->id, $item->getName()]) }}">
                        @if ($item->getElementPermissions())
                            <td class="title">
                                <a href="{{ route('moonlight.groups.items.elements.index', [$group->id, $item->getName()]) }}">{{ $item->getTitle() }}</a><br>
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
    @include('moonlight::components.sidebar.admin', ['active' => 'groups'])
@endsection
