@extends('moonlight::layouts.trash')

@section('title', 'Корзина')

@section('css')
<link media="all" type="text/css" rel="stylesheet" href="/packages/moonlight/css/trash.css">
@endsection

@section('js')
<script src="/packages/moonlight/js/trash.js"></script>
@endsection

@section('body')
<div class="main">
    <div class="container">
        <div class="path">
            <div class="part"><span>Корзина</span></div>
        </div>
        <div class="leaf">
            <input type="text" id="filter" placeholder="Название">
            <ul class="items">
                @foreach ($items as $id => $item)
                <li item="{{ $item->getNameId() }}"><a href="{{ route('moonlight.trash.item', $item->getNameId()) }}">{{ $item->getTitle() }}</a><span class="total">{{ $totals[$id] }}</span><br><small>{{ $item->getNameId() }}</small></li>
                @endforeach
            </ul>
        </div>
    </div>
</div>
@endsection