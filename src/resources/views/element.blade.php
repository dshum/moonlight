@extends('moonlight::layouts.browse')

@section('title', $element->$mainProperty)

@section('css')
<link media="all" type="text/css" rel="stylesheet" href="/packages/moonlight/css/browse.min.css">
@endsection

@section('js')
<script src="/packages/moonlight/js/browse.min.js"></script>
@endsection

@section('body')
<div class="main">
    <div class="container">
        <div class="path">
            <div class="part"><a href="{{ route('moonlight.browse.root') }}">Корень сайта</a></div>
            <div class="divider">/</div>
            @foreach ($parents as $parent)
            <div class="part"><a href="{{ route('moonlight.browse.element', $parent['classId']) }}">{{ $parent['name'] }}</a></div>
            <div class="divider">/</div>
            @endforeach
            <div class="part"><span>{{ $element->$mainProperty }}</span><a href="{{ route('moonlight.element.edit', $classId) }}" class="edit" title="Редактировать"><i class="fa fa-pencil"></i></a></div>
        </div>
        @if ($creates)
        <div class="add-element">
            Добавить:
            @foreach ($creates as $index => $create)
            <a href="{{ route('moonlight.element.create', [$classId, $create['id']]) }}">{{ $create['name'] }}</a>{{ $index < sizeof($creates) - 1 ? ',' : '' }}
            @endforeach
        </div>
        @endif
        @if ($browsePluginView)
        <div class="browse-plugin">
            {!! $browsePluginView !!}
        </div>
        @endif
        @foreach ($items as $item)
        <div classId="{{ $classId }}" item="{{ $item['id'] }}"></div>
        @endforeach
        @if (! $browsePluginView)
        <div class="empty {{ sizeof($items) > 0 ? 'dnone' : '' }}">Элементов не найдено.</div>
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