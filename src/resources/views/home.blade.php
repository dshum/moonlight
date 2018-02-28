@extends('moonlight::layouts.home')

@section('title', 'Moonlight')

@section('css')
<link media="all" type="text/css" rel="stylesheet" href="/packages/moonlight/css/home.css">
@endsection

@section('js')
<script src="/packages/moonlight/js/home.js"></script>
@endsection

@section('body')
<div class="main">
    <div class="container">
        @if ($homePluginView)
            {!! $homePluginView !!}
        @endif
        <div class="leaf">
            {!! $rubrics !!}
        </div>
    </div>
</div>
@endsection