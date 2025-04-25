@extends('moonlight::layouts.browse')

@section('title', $element->$mainProperty)

@prepend('styles')
    <link media="all" type="text/css" rel="stylesheet" href="{{ asset('packages/moonlight/css/browse.min.css') }}">
@endprepend

@prepend('scripts')
    <script src="{{ asset('packages/moonlight/js/browse.min.js') }}"></script>
@endprepend

@section('body')
    <div class="main">
        <div class="container">
            <div class="path">
                <div class="part"><a href="{{ route('moonlight.browse.root') }}">Корень сайта</a></div>
                <div class="divider">/</div>
                @foreach ($parents as $parent)
                    <div class="part">
                        <a href="{{ route('moonlight.browse.element', $parent->class_id) }}">{{ $parent->name }}</a>
                    </div>
                    <div class="divider">/</div>
                @endforeach
                <div class="part">
                    <span>{{ $element->$mainProperty }}</span><a href="{{ route('moonlight.element.edit', $classId) }}"
                                                                 class="edit" title="Редактировать"><i
                            class="fa fa-pencil"></i></a>
                </div>
            </div>
            @if ($creates)
                <div class="add-element">
                    Добавить:
                    @foreach ($creates as $create)
                        <a href="{{ route('moonlight.element.create', [$classId, $create->getName()]) }}">{{ $create->getTitle() }}</a>{{ $loop->last ? '' : ',' }}
                    @endforeach
                </div>
            @endif
            @if ($browseComponentView)
                <div class="browse-plugin">
                    {!! $browseComponentView !!}
                </div>
            @endif
            @foreach ($items as $item)
                <div class="item active hidden" data-item="{{ $item->getName() }}" data-class-id="{{ $classId }}"
                     data-url="{{ route('moonlight.elements.list') }}"></div>
            @endforeach
            @if (! $browseComponentView)
                <div class="empty{{ sizeof($items) > 0 ? ' dnone' : '' }}">Элементов не найдено.</div>
            @endif
        </div>
    </div>
@endsection

@section('sidebar')
    <div class="sidebar">
        <div class="container">
            {!! $rubrics !!}
        </div>
    </div>
@endsection
