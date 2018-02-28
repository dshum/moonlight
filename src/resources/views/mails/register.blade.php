<p>{{ $first_name }}!</p>

<p>Вы зарегистрированы в системе управления сайтом {{ $site }}.</p>

<p>
    Адрес: <a href="{{ route('moonlight.home') }}">{{ route('moonlight.home') }}</a><br>
    Логин: {{ $login }}<br>
    Пароль: {{ $password }}
</p>