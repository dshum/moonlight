@extends('moonlight::layouts.browse')

@section('title', 'Новый элемент')

@section('css')
<link media="all" type="text/css" rel="stylesheet" href="/packages/moonlight/css/edit.css">
@endsection

@section('js')
<script src="/packages/moonlight/js/tinymce/js/tinymce/tinymce.min.js"></script>
<script src="/packages/moonlight/js/edit.js"></script>
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
            <div class="part"><span>Новый элемент</span></div>
        </div>
        <div class="item active">
            <ul class="header">
                <li class="h2"><span>Создание элемента типа &laquo;{{ $currentItem->getTitle() }}&raquo;</span></li>
            </ul>
            <form save="true" action="{{ route('moonlight.element.add', $currentItem->getNameId()) }}" method="POST">
                <div class="edit">
                    @foreach ($views as $name => $view)
                    <div class="row" name="{{ $name }}">
                        {!! $view !!}
                    </div>
                    @endforeach
                    <div class="row submit">
                        <input type="submit" value="Сохранить" class="btn">
                    </div>
                </div>
            </form>
        </div>
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