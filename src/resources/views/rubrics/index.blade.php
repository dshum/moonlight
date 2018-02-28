@if (sizeof($favoriteRubrics))
<div class="favorite-settings" title="Настроить избранное"><a href="{{ route('moonlight.favorites.edit') }}"><i class="fa fa-cog"></i></a></div>
@endif
@php $index = 0; @endphp
<div class="row">
@foreach ($favoriteRubrics as $favoriteRubric)
    <div class="favorite elements">
        <div class="h2"><span>{{ $favoriteRubric->name }}</span></div>
        @if (sizeof($favorites[$favoriteRubric->id]))
        <ul>
            @foreach ($favorites[$favoriteRubric->id] as $favorite)
            <li><a href="{{ route('moonlight.browse.element', $favorite['classId']) }}">{{ $favorite['name'] }}</a></li>
            @endforeach
        </ul>
        @else
        <ul>
            <li>Элементов не найдено.</li>
        </ul>
        @endif
    </div>
@if ($index % 3 == 2)
</div>
<div class="row">
@endif
@php $index++; @endphp
@endforeach
@foreach ($rubrics as $rubric)
    <div class="elements">
        <div class="h2"><span>{{ $rubric->getTitle() }}</span></div>
        {!! $views[$rubric->getName()] !!}
    </div>
@if ($index % 3 == 2)
</div>
<div class="row">
@endif
@php $index++; @endphp
@endforeach
</div>