@extends('moonlight::layouts.browse')

@section('title', $element->$mainProperty)

@section('css')
<link media="all" type="text/css" rel="stylesheet" href="/packages/moonlight/css/edit.min.css">
@endsection

@section('js')
<script src="/packages/moonlight/js/tinymce/js/tinymce/tinymce.min.js"></script>
<script src="/packages/moonlight/js/edit.min.js"></script>
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
            <div class="part"><a href="{{ route('moonlight.browse.element', $classId) }}" title="Открыть">{{ $element->$mainProperty }}</a></div>
        </div>
        @if (method_exists($element, 'getHref') && $element->getHref())
        <div class="external-link">
            <a href="{{ $element->getHref() }}" target="_blank"><i class="fa fa-external-link"></i>Смотреть на сайте</a>
        </div>
        @endif
        @if ($itemPluginView)
        <div class="item-plugin">
            {!! $itemPluginView !!}
        </div>
        @endif
        @if ($editPluginView)
        <div class="edit-plugin">
            {!! $editPluginView !!}
        </div>
        @endif
        <div class="item active">
            <ul class="header">
                <li class="h2"><span>Редактирование элемента типа &laquo;{{ $currentItem->getTitle() }}&raquo;</span></li>
            </ul>
            <div class="buttons">
                <div class="button save enabled"><i class="fa fa-floppy-o"></i>Сохранить</div>
                @if ($copyPropertyView)
                <div class="button copy enabled"><i class="fa fa-clone"></i>Копировать</div>
                @else
                <div class="button copy"><i class="fa fa-clone"></i>Копировать</div>
                @endif
                @if ($movePropertyView)
                <div class="button move enabled"><i class="fa fa-arrow-right"></i>Перенести</div>
                @else
                <div class="button move"><i class="fa fa-arrow-right"></i>Перенести</div>
                @endif
                <div class="button favorite enabled"><i class="fa fa-bookmark-o"></i>Избранное</div>
                <div class="button delete enabled"><i class="fa fa-trash-o"></i>Удалить</div>
            </div>
            <form save="true" action="{{ route('moonlight.element.save', $classId) }}" method="POST">
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
@if ($copyPropertyView)
<div class="confirm" id="copy">
    <div class="wrapper">
        <div class="container">
            <div class="content">
                <div>Куда копируем?</div>
                <div class="edit">
                    <div class="row">
                        {!! $copyPropertyView !!}
                    </div>
                </div>
            </div>
            <div class="bottom">
                <input type="button" value="Скопировать" class="btn copy" url="{{ route('moonlight.element.copy', $classId) }}">
                <input type="button" value="Отмена" class="btn cancel">
            </div>
        </div>
    </div>
</div>
@endif
@if ($movePropertyView)
<div class="confirm" id="move">
    <div class="wrapper">
        <div class="container">
            <div class="content">
                <div>Куда переносим?</div>
                <div class="edit">
                    <div class="row">
                        {!! $movePropertyView !!}
                    </div>
                </div>
            </div>
            <div class="bottom">
                <input type="button" value="Перенести" class="btn move" url="{{ route('moonlight.element.move', $classId) }}">
                <input type="button" value="Отмена" class="btn cancel">
            </div>
        </div>
    </div>
</div>
@endif
<div class="confirm" id="favorite" url="{{ route('moonlight.element.favorite', $classId) }}">
    <div class="wrapper">
        <div class="container">
            @if (sizeof($favoriteRubrics))
            <div class="favorite-settings" title="Настроить избранное"><a href="{{ route('moonlight.favorites.edit') }}"><i class="fa fa-cog"></i></a></div>
            @endif
            <div class="content">
                <div name="add" class="{{ sizeof($elementFavoriteRubrics) < sizeof($favoriteRubrics) ? '' : 'dnone' }}">Добавить в рубрику:</div>
                <div class="favorite-list add {{ sizeof($elementFavoriteRubrics) < sizeof($favoriteRubrics) ? '' : 'dnone' }}">
                    @foreach ($favoriteRubrics as $favoriteRubric)                
                    <div rubric="{{ $favoriteRubric->id }}" display="{{ isset($elementFavoriteRubrics[$favoriteRubric->id]) ? 'hide' : 'show' }}">{{ $favoriteRubric->name }}</div>
                    @endforeach
                </div>
                <div name="remove" class="{{ sizeof($elementFavoriteRubrics) ? '' : 'dnone' }}">Убрать из рубрики:</div>
                <div class="favorite-list remove {{ sizeof($elementFavoriteRubrics) ? '' : 'dnone' }}">
                    @foreach ($favoriteRubrics as $favoriteRubric)                
                    <div rubric="{{ $favoriteRubric->id }}" display="{{ isset($elementFavoriteRubrics[$favoriteRubric->id]) ? 'show' : 'hide' }}">{{ $favoriteRubric->name }}</div>
                    @endforeach
                </div>
                <div class="favorite-new">
                    <input type="text" name="favorite_rubric_new" value="" placeholder="Новая рубрика">
                </div>
            </div>
            <div class="bottom">
            <input type="button" value="Добавить" class="btn favorite">
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
                <input type="button" value="Удалить" class="btn remove" url="{{ route('moonlight.element.delete', $classId) }}">
                <input type="button" value="Отмена" class="btn cancel">
            </div>
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