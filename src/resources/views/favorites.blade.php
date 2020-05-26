@extends('moonlight::layouts.home')

@section('title', 'Избранное')

@push('styles')
    <link media="all" type="text/css" rel="stylesheet" href="{{ asset('packages/moonlight/css/favorites.min.css') }}">
@endpush

@push('scripts')
    <script src="{{ asset('packages/moonlight/js/favorites.min.js') }}"></script>
@endpush

@section('body')
    <div class="main">
        <div class="container">
            <div class="path">
                <div class="part"><span>Избранное</span></div>
            </div>
            @if (sizeof($favoriteRubrics))
                <div class="favorites">
                    @foreach ($favoriteRubrics as $favoriteRubric)
                        <div class="elements" data-rubric="{{ $favoriteRubric->id }}">
                            <div class="h2">
                                <span class="title">{{ $favoriteRubric->name }}</span>
                                <span class="remove {{ ! empty($favoriteMap[$favoriteRubric->id]) ? '' : 'enabled' }}"><i class="fa fa-times-circle"></i></span>
                            </div>
                            @if (! empty($favoriteMap[$favoriteRubric->id]))
                                <ul>
                                    @foreach ($favoriteMap[$favoriteRubric->id] as $favorite)
                                        <li data-favorite="{{ $favorite->id }}">
                                            <span class="element">{{ $favorite->name }}</span>
                                            <span class="remove enabled"><i class="fa fa-times-circle"></i></span>
                                        </li>
                                    @endforeach
                                </ul>
                            @else
                                <ul>
                                    <li>Элементов не найдено.</li>
                                </ul>
                            @endif
                        </div>
                    @endforeach
                </div>
            @else
                <div class="empty">Избранных элементов не найдено.</div>
            @endif
        </div>
    </div>
@endsection
