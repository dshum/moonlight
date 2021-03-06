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
            <div class="error"></div>
            <div class="ok"></div>
            <form data-save="true" action="{{ route('moonlight.reset.save') }}" autocomplete="off" method="POST">
                <input type="hidden" name="login" value="{{ $login }}">
                <input type="hidden" name="token" value="{{ $token }}">
                <div class="row">
                    <label>Новый пароль:</label><span data-name="password" class="error"></span><br>
                    <input type="password" name="password" placeholder="Новый пароль">
                </div>
                <div class="row">
                    <label>Подтверждение:</label><span data-name="password_confirmation" class="error"></span><br>
                    <input type="password" name="password_confirmation" placeholder="Подтверждение">
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
