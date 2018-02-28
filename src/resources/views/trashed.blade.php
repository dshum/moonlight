@extends('moonlight::layouts.trash')

@section('title', $element->$mainProperty)

@section('css')
<link media="all" type="text/css" rel="stylesheet" href="/packages/moonlight/css/trashed.css">
@endsection

@section('js')
<script src="/packages/moonlight/js/trashed.js"></script>
@endsection

@section('body')
<div class="main">
    <div class="container">
        <div class="path">
            <div class="part"><a href="{{ route('moonlight.trash') }}">Корзина</a></div>
            <div class="divider">/</div>
            <div class="part"><a href="{{ route('moonlight.trash.item', $currentItem->getNameId()) }}">{{ $currentItem->getTitle() }}</a></div>
            <div class="divider">/</div>
            <div class="part"><span>{{ $element->$mainProperty }}</span></div>
        </div>
        <div class="item active">
            <ul class="header">
                <li class="h2"><span>Просмотр элемента типа &laquo;{{ $currentItem->getTitle() }}&raquo;</span></li>
            </ul>
            <div class="buttons">
                <div class="button restore enabled"><i class="fa fa-arrow-left"></i>Восстановить</div>
                <div class="button delete enabled"><i class="fa fa-ban"></i>Удалить</div>
            </div>
                <div class="edit">
                    @foreach ($views as $name => $view)
                    <div class="row" name="{{ $name }}">
                        {!! $view !!}
                    </div>
                    @endforeach
                </div>
        </div>
    </div>
</div>
<div class="confirm" id="restore">
    <div class="wrapper">
        <div class="container">
            <div class="content">
                Восстановить элемент &laquo;{{ $element->$mainProperty }}&raquo;?
            </div>
            <div class="bottom">
                <input type="button" value="Восстановить" class="btn restore" url="{{ route('moonlight.trashed.restore', $classId) }}">
                <input type="button" value="Отмена" class="btn cancel">
            </div>
        </div>
    </div>
</div>
<div class="confirm" id="delete">
    <div class="wrapper">
        <div class="container">
            <div class="content">
                Удалить элемент &laquo;{{ $element->$mainProperty }}&raquo;?
            </div>
            <div class="bottom">
                <input type="button" value="Удалить" class="btn danger remove" url="{{ route('moonlight.trashed.delete', $classId) }}">
                <input type="button" value="Отмена" class="btn cancel">
            </div>
        </div>
    </div>
</div>
@endsection

@section('sidebar')
<div class="sidebar">
    <div class="container">
        <input type="text" id="filter" placeholder="Название">
        <ul class="items">
            @foreach ($items as $id => $item)
            <li item="{{ $item->getNameId() }}" class="{{ $item->getNameId() == $currentItem->getNameId() ? 'active' : '' }}"><a href="{{ route('moonlight.trash.item', $item->getNameId()) }}">{{ $item->getTitle() }}</a><span class="total">{{ $totals[$id] }}</span><br><small>{{ $item->getNameId() }}</small></li>
            @endforeach
        </ul>
    </div>
</div>
@endsection