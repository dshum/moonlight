@extends('moonlight::layouts.home')

@section('title', 'Избранное')

@section('css')
<link media="all" type="text/css" rel="stylesheet" href="/packages/moonlight/css/favorites.css">
@endsection

@section('js')
<script src="/packages/moonlight/js/favorites.js"></script>
@endsection

@section('body')
<div class="main">
    <div class="container">
        <div class="path">
            <div class="part"><span>Избранное</span></div>
        </div>
        @if (sizeof($favoriteRubrics))
        <div class="favorites">
            @foreach ($favoriteRubrics as $favoriteRubric)
            <div class="elements" rubric="{{ $favoriteRubric->id }}">
                <div class="h2"><span class="title">{{ $favoriteRubric->name }}</span><span rubric="{{ $favoriteRubric->id }}" class="remove {{ sizeof($favorites[$favoriteRubric->id]) ? '' : 'enabled' }}"><i class="fa fa-times-circle"></i></span></div>
                @if (sizeof($favorites[$favoriteRubric->id]))
                <ul>
                    @foreach ($favorites[$favoriteRubric->id] as $favorite)
                    <li favorite="{{ $favorite['id'] }}"><span class="element">{{ $favorite['name'] }}</span><span favorite="{{ $favorite['id'] }}" class="remove enabled"><i class="fa fa-times-circle"></i></span></li>
                    @endforeach
                </ul>
                @else
                <ul>
                    <li>Элементов не найдено.</li>
                </ul>
                @endif
            </div>
            @endforeach
        </div>
        @else
        <div class="empty">Избранных элементов не найдено.</div>
        @endif
    </div>
</div>
@endsection