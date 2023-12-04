@extends('moonlight::layouts.browse')

@section('title', $element->$mainProperty)

@push('styles')
    <link rel="stylesheet" href="{{ asset('packages/moonlight/js/codemirror/lib/codemirror.css') }}">
    <link rel="stylesheet" href="{{ asset('packages/moonlight/js/codemirror/addon/display/fullscreen.css') }}">
    <link rel="stylesheet" href="{{ asset('packages/moonlight/js/codemirror/addon/hint/show-hint.css') }}">
    <link rel="stylesheet" href="{{ asset('packages/moonlight/js/codemirror/theme/eclipse.css') }}">
    <link rel="stylesheet" href="{{ asset('packages/moonlight/css/edit.min.css') }}">
    <style>
        .CodeMirror {
            height: 30rem;
            border: 2px solid #999;
            border-radius: 2px;
        }
    </style>
@endpush

@push('scripts')
    <script src="{{ asset('packages/moonlight/js/tinymce/js/tinymce/tinymce.min.js') }}"></script>
    <script src="{{ asset('packages/moonlight/js/codemirror/lib/codemirror.js') }}"></script>
    <script src="{{ asset('packages/moonlight/js/codemirror/addon/display/autorefresh.js') }}"></script>
    <script src="{{ asset('packages/moonlight/js/codemirror/addon/display/fullscreen.js') }}"></script>
    <script src="{{ asset('packages/moonlight/js/codemirror/addon/hint/show-hint.js') }}"></script>
    <script src="{{ asset('packages/moonlight/js/codemirror/addon/hint/xml-hint.js') }}"></script>
    <script src="{{ asset('packages/moonlight/js/codemirror/addon/hint/html-hint.js') }}"></script>
    <script src="{{ asset('packages/moonlight/js/codemirror/addon/hint/css-hint.js') }}"></script>
    <script src="{{ asset('packages/moonlight/js/codemirror/mode/xml/xml.js') }}"></script>
    <script src="{{ asset('packages/moonlight/js/codemirror/mode/htmlmixed/htmlmixed.js') }}"></script>
    <script src="{{ asset('packages/moonlight/js/codemirror/mode/css/css.js') }}"></script>
    <script src="{{ asset('packages/moonlight/js/edit.min.js') }}"></script>
@endpush

@section('body')
    <div class="main">
        <div class="container">
            <div class="path">
                <div class="part"><a href="{{ route('moonlight.browse.root') }}">Корень сайта</a></div>
                <div class="divider">/</div>
                @foreach ($parents as $parent)
                    <div class="part">
                        <a href="{{ route('moonlight.browse.element', $parent->class_id) }}">{{ $parent->name }}</a>
                    </div>
                    <div class="divider">/</div>
                @endforeach
                <div class="part">
                    <a href="{{ route('moonlight.browse.element', $classId) }}" title="Открыть">{{ $element->$mainProperty }}</a>
                </div>
            </div>
            @if (method_exists($element, 'getWidget') && $element->getWidget())
                <div class="external-widget">
                    {!! $element->getWidget()->render() !!}
                </div>
            @endif
            @if (method_exists($element, 'getHref') && $element->getHref())
                <div class="external-link">
                    <a href="{{ $element->getHref() }}" target="_blank"><i class="fa fa-external-link"></i>Смотреть на сайте</a>
                </div>
            @endif
            <div class="item active" data-item="{{ $currentItem->getName() }}">
                <ul class="header">
                    <li class="h2">
                        <span>Редактирование элемента типа &laquo;{{ $currentItem->getTitle() }}&raquo;</span>
                    </li>
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
                <form data-save="true" action="{{ route('moonlight.element.save', $classId) }}" method="POST">
                    <div class="edit">
                        @foreach ($views as $name => $view)
                            <div class="field row" data-name="{{ $name }}">
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
    @includeWhen($copyPropertyView, 'moonlight::components.edit.confirm.copy')
    @includeWhen($movePropertyView, 'moonlight::components.edit.confirm.move')
    @include('moonlight::components.edit.confirm.favorite')
    @include('moonlight::components.edit.confirm.delete')
@endsection

@section('sidebar')
    <div class="sidebar">
        <div class="container">
            {!! $rubrics !!}
        </div>
    </div>
@endsection
