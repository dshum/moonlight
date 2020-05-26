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
                <div class="part"><span>Поиск</span></div>
            </div>
            <div class="leaf">
                <div class="items-container">{!! $items !!}</div>
            </div>
        </div>
    </div>
@endsection
