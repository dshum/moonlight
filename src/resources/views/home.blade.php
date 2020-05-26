@extends('moonlight::layouts.home')

@section('title', 'Moonlight')

@push('styles')
    <link media="all" type="text/css" rel="stylesheet" href="{{ asset('packages/moonlight/css/home.min.css') }}">
@endpush

@push('scripts')
    <script src="{{ asset('packages/moonlight/js/home.min.js') }}"></script>
@endpush

@section('body')
    <div class="main">
        <div class="container">
            @if ($homeComponentView)
                {!! $homeComponentView !!}
            @endif
            <div class="leaf">
                {!! $rubricsView !!}
            </div>
        </div>
    </div>
@endsection
