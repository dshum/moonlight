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
            <div class="part"><a href="{{ route('moonlight.search') }}">Поиск</a></div>
            <div class="divider">/</div>
            <div class="part"><span>{{ $currentItem->getTitle() }}</span></div>
        </div>
        <form name="search-form">
            <input type="hidden" name="action" value="search">
            <input type="hidden" name="page" value="1">
            <div class="search-form">
                <div class="search-form-links">
                    <div class="row">
                        @foreach ($properties as $property)
                        <div class="link {{ isset($actives[$property->getName()]) ? 'active' : '' }}" item="{{ $currentItem->getNameId() }}" name="{{ $property->getName() }}">
                            {!! $links[$property->getName()] !!}
                        </div>
                        @endforeach
                    </div>
                </div>
                <div class="search-form-params">
                    <div class="row">
                        @foreach ($properties as $property)
                        <div class="block {{ isset($actives[$property->getName()]) ? 'active' : '' }}" name="{{ $property->getName() }}">
                            <div class="close" item="{{ $currentItem->getNameId() }}" name="{{ $property->getName() }}"><i class="fa fa-minus-square-o"></i></div>
                            {!! $views[$property->getName()] !!}
                        </div>
                        @endforeach
                    </div>
                </div>
                <div class="row-submit">
                    <input type="submit" value="Найти" class="btn">
                </div>
            </div>
        </form>
        <div class="list-container" item="{{ $currentItem->getNameId() }}">
            @if ($action == 'search')
            {!! $elements !!}
            @endif
        </div>
    </div>
</div>
@endsection

@section('sidebar')
<div class="sidebar">
    <div class="container">
        <div class="items-container">{!! $items !!}</div>
    </div>
</div>
@endsection