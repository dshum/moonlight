@extends('moonlight::layouts.browse')

@section('title', 'Корень сайта')

@prepend('styles')
    <link media="all" type="text/css" rel="stylesheet" href="{{ asset('packages/moonlight/css/browse.min.css') }}">
@endprepend

@prepend('scripts')
    <script src="{{ asset('packages/moonlight/js/browse.min.js') }}"></script>
@endprepend

@section('body')
    <div class="main">
        <div class="container">
            <div class="path">
                <div class="part"><span>Корень сайта</span></div>
            </div>
            @if ($creates)
                <div class="add-element">
                    <span>Добавить:</span>
                    @foreach ($creates as $index => $create)
                        <a href="{{ route('moonlight.element.create', ['root', $create->getName()]) }}">{{ $create->getTitle() }}</a>{{ $loop->last ? '' : ',' }}
                    @endforeach
                </div>
            @endif
            @foreach ($items as $item)
                <div class="item active hidden" data-item="{{ $item->getName() }}" data-url="{{ route('moonlight.elements.list') }}"></div>
            @endforeach
            <div class="empty {{ sizeof($items) > 0 ? 'dnone' : '' }}">
                Элементов не найдено.
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
