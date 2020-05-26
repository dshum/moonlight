@extends('moonlight::layouts.login')

@section('title', 'Сброс пароля')

@push('scripts')
    <script src="{{ asset('packages/moonlight/js/reset.min.js') }}"></script>
@endpush

@section('content')
    <div class="login">
        <div class="path">
            Сброс пароля
        </div>
        <div class="block">
            @if ($error)
                <div class="error show">{!! $error !!}</div>
            @else
                <div class="error"></div>
            @endif
            <div class="ok"></div>
            <form data-save="true" action="{{ route('moonlight.reset.send') }}" autocomplete="off" method="POST">
                <div class="row">
                    <label>Логин</label><br>
                    <input type="text" name="login" value="{{ $login or null }}" placeholder="Логин">
                </div>
                <div class="row submit">
                    <input type="submit" value="Отправить" class="btn">
                </div>
                <div class="back">
                    <a href="{{ route('moonlight.login') }}">Вернуться на страницу входа</a>
                </div>
            </form>
        </div>
    </div>
@endsection
