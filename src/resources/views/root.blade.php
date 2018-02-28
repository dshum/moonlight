@extends('moonlight::layouts.browse')

@section('title', 'Корень сайта')

@section('css')
<link media="all" type="text/css" rel="stylesheet" href="/packages/moonlight/css/browse.css">
@endsection

@section('js')
<script src="/packages/moonlight/js/browse.js"></script>
@endsection

@section('body')
<div class="main">
    <div class="container">
        <div class="path">
            <div class="part"><span>Корень сайта</span></div>
        </div>
        @if ($creates)
        <div class="add-element">
            Добавить:
            @foreach ($creates as $index => $create)
            <a href="{{ route('moonlight.element.create', ['root', $create['id']]) }}">{{ $create['name'] }}</a>{{ $index < sizeof($creates) - 1 ? ',' : '' }}
            @endforeach
        </div>
        @endif
        @foreach ($items as $item)
        <div classId="" item="{{ $item['id'] }}"></div>
        @endforeach
        <div class="empty {{ sizeof($items) > 0 ? 'dnone' : '' }}">Элементов не найдено.</div>
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