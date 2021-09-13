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
                <div class="part"><span>Корзина</span></div>
            </div>

            <div class="leaf">
                <input type="text" id="filter" placeholder="Название">

                <ul class="items" data-count-url="{{ route('moonlight.trash.count') }}">
                    @foreach ($items as $item)
                        <li data-item="{{ $item->name }}">
                            <a href="{{ route('moonlight.trash.item', $item->name) }}">{{ $item->title }}</a><span class="total">{{ $item->total }}</span><br>
                            <small>{{ $item->class_name }}</small>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
@endsection
