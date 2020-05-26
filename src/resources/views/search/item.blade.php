@extends('moonlight::layouts.search')

@section('title', 'Поиск')

@push('styles')
    <link media="all" type="text/css" rel="stylesheet" href="{{ asset('packages/moonlight/css/search.min.css') }}">
@endpush

@push('scripts')
    <script src="{{ asset('packages/moonlight/js/search.min.js') }}"></script>
@endpush

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
                <div class="search-form" data-item="{{ $currentItem->getName() }}">
                    <div class="search-form-links">
                        <div class="row">
                            @foreach ($properties as $property)
                                <div class="link {{ isset($actives[$property->getName()]) ? 'active' : '' }}" data-name="{{ $property->getName() }}">
                                    {!! $links[$property->getName()] !!}
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="search-form-params">
                        <div class="row">
                            @foreach ($properties as $property)
                                <div class="block {{ isset($actives[$property->getName()]) ? 'active' : '' }}" data-name="{{ $property->getName() }}">
                                    <div class="close" data-name="{{ $property->getName() }}">
                                        <i class="fa fa-minus-square-o"></i></div>
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
            <div class="item active" data-item="{{ $currentItem->getName() }}" data-url="{{ route('moonlight.search.list') }}">
                @if ($itemComponentView)
                    {!! $itemComponentView !!}
                @endif
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
