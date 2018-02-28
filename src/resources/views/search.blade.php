@extends('moonlight::layouts.search')

@section('title', 'Поиск')

@section('css')
<link media="all" type="text/css" rel="stylesheet" href="/packages/moonlight/css/search.css">
@endsection

@section('js')
<script src="/packages/moonlight/js/search.js"></script>
@endsection

@section('body')
<div class="main">
    <div class="container">
        <div class="path">
            <div class="part"><span>Поиск</span></div>
        </div>
        <div class="leaf">
            <div class="items-container">{!! $items !!}</div>
        </div>
    </div>
</div>
@endsection