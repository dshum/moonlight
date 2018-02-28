@extends('moonlight::layouts.login')

@section('title', 'Сброс пароля')

@section('js')
<script src="/packages/moonlight/js/reset.js"></script>
@endsection

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
        <form action="{{ route('moonlight.reset.send') }}" autocomplete="off" method="POST">
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