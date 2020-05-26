@extends('moonlight::layouts.trash')

@section('title', $element->$mainProperty)

@push('styles')
    <link media="all" type="text/css" rel="stylesheet" href="{{ asset('packages/moonlight/css/trashed.min.css') }}">
@endpush

@push('scripts')
    <script src="{{ asset('packages/moonlight/js/trashed.min.js') }}"></script>
@endpush

@section('body')
    <div class="main">
        <div class="container">
            <div class="path">
                <div class="part"><a href="{{ route('moonlight.trash') }}">Корзина</a></div>
                <div class="divider">/</div>
                <div class="part">
                    <a href="{{ route('moonlight.trash.item', $currentItem->getName()) }}">{{ $currentItem->getTitle() }}</a>
                </div>
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
                        <div class="row">
                            {!! $view !!}
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
    <div class="confirm" data-confirm-type="restore" data-url="{{ route('moonlight.trashed.restore', $classId) }}">
        <div class="wrapper">
            <div class="container">
                <div class="content">
                    Восстановить элемент &laquo;{{ $element->$mainProperty }}&raquo;?
                </div>
                <div class="bottom">
                    <input type="button" value="Восстановить" class="btn restore">
                    <input type="button" value="Отмена" class="btn cancel">
                </div>
            </div>
        </div>
    </div>
    <div class="confirm" data-confirm-type="delete" data-url="{{ route('moonlight.trashed.delete', $classId) }}">
        <div class="wrapper">
            <div class="container">
                <div class="content">
                    Удалить элемент &laquo;{{ $element->$mainProperty }}&raquo;?
                </div>
                <div class="bottom">
                    <input type="button" value="Удалить" class="btn danger remove">
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
                @foreach ($items as $item)
                    <li class="{{ $item->name == $currentItem->getName() ? 'active' : '' }}">
                        <a href="{{ route('moonlight.trash.item', $item->name) }}">{{ $item->title }}</a><span class="total">{{ $item->total }}</span><br>
                        <small>{{ $item->class_name }}</small>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
@endsection
