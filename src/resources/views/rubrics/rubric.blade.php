@if (sizeof($favorites))
<ul>
    @foreach ($favorites as $favorite)
    <li><a href="{{ route('moonlight.browse.element', $favorite['classId']) }}">{{ $favorite['name'] }}</a></li>
    @endforeach
</ul>
@elseif ($view)
    {!! $view !!}
@else
<ul>
    <li>Элементов не найдено.</li>
</ul>
@endif