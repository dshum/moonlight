@extends('moonlight::layouts.browse')

@section('title', 'Новый элемент')

@section('css')
    <link rel="stylesheet" href="/packages/moonlight/js/codemirror/lib/codemirror.css">
    <link rel="stylesheet" href="/packages/moonlight/js/codemirror/addon/display/fullscreen.css">
    <link rel="stylesheet" href="/packages/moonlight/js/codemirror/addon/hint/show-hint.css">
    <link rel="stylesheet" href="/packages/moonlight/js/codemirror/theme/eclipse.css">
    <link rel="stylesheet" href="/packages/moonlight/css/edit.min.css">
    <style>
        .CodeMirror {
            height: 30rem;
            border: 2px solid #999;
            border-radius: 2px;
        }
    </style>
@endsection

@section('js')
    <script src="/packages/moonlight/js/tinymce/js/tinymce/tinymce.min.js"></script>
    <script src="/packages/moonlight/js/codemirror/lib/codemirror.js"></script>
    <script src="/packages/moonlight/js/codemirror/addon/display/autorefresh.js"></script>
    <script src="/packages/moonlight/js/codemirror/addon/display/fullscreen.js"></script>
    <script src="/packages/moonlight/js/codemirror/addon/hint/show-hint.js"></script>
    <script src="/packages/moonlight/js/codemirror/addon/hint/xml-hint.js"></script>
    <script src="/packages/moonlight/js/codemirror/addon/hint/html-hint.js"></script>
    <script src="/packages/moonlight/js/codemirror/addon/hint/css-hint.js"></script>
    <script src="/packages/moonlight/js/codemirror/mode/xml/xml.js"></script>
    <script src="/packages/moonlight/js/codemirror/mode/htmlmixed/htmlmixed.js"></script>
    <script src="/packages/moonlight/js/codemirror/mode/css/css.js"></script>
    <script src="/packages/moonlight/js/edit.min.js"></script>
@endsection

@section('body')
    <div class="main">
        <div class="container">
            <div class="path">
                <div class="part"><a href="{{ route('moonlight.browse.root') }}">Корень сайта</a></div>
                <div class="divider">/</div>
                @foreach ($parents as $parent)
                    <div class="part">
                        <a href="{{ route('moonlight.browse.element', $parent['classId']) }}">{{ $parent['name'] }}</a>
                    </div>
                    <div class="divider">/</div>
                @endforeach
                <div class="part"><span>Новый элемент</span></div>
            </div>
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
