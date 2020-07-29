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
                <div class="part"><a href="{{ route('moonlight.groups.items.index', $group->id) }}">{{ $group->name }}</a></div>
                <div class="divider">/</div>
                <div class="part"><span>{{ $item->getTitle() }}</span></div>
            </div>
            <div class="search-field">
                <input type="text" id="filter" placeholder="Введите название">
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
                        <tr data-url="{{ route('moonlight.groups.items.elements.update', [$group->id, $item->getName(), $element->id]) }}">
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
    @include('moonlight::components.sidebar.admin', ['active' => 'groups'])
@endsection
