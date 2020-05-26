@extends('moonlight::layouts.trash')

@section('title', 'Корзина')

@push('styles')
    <link media="all" type="text/css" rel="stylesheet" href="{{ asset('packages/moonlight/css/trash.min.css') }}">
@endpush

@push('scripts')
    <script src="{{ asset('packages/moonlight/js/trash.min.js') }}"></script>
@endpush

@section('body')
    <div class="main">
        <div class="container">
            <div class="path">
                <div class="part"><a href="{{ route('moonlight.trash') }}">Корзина</a></div>
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
                                        <i class="fa fa-minus-square-o"></i>
                                    </div>
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
            <div class="item active" data-item="{{ $currentItem->getName() }}" data-url="{{ route('moonlight.trash.list') }}">
                {!! $elements !!}
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
