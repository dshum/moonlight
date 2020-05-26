@if (sizeof($favoriteRubrics))
    <div class="favorite-settings">
        <div class="container" title="Настроить избранное">
            <a href="{{ route('moonlight.favorites.edit') }}"><i class="fa fa-cog"></i></a>
        </div>
    </div>
@endif

@foreach ($favoriteRubrics as $favoriteRubric)
    <div class="elements">
        <div class="h2"><span>{{ $favoriteRubric->name }}</span></div>
        @if (! empty($favoriteMap[$favoriteRubric->id]))
            <ul>
                @foreach ($favoriteMap[$favoriteRubric->id] as $favorite)
                    <li>
                        <a href="{{ route('moonlight.browse.element', $favorite->class_id) }}">{{ $favorite->name }}</a>
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

@foreach ($rubrics as $rubric)
    <div class="elements">
        <div class="h2"><span>{{ $rubric->getTitle() }}</span></div>
        @if (sizeof($rubricElementMap[$rubric->getName()]))
            <ul>
                @foreach ($rubricElementMap[$rubric->getName()] as $element)
                    <li>
                        <a href="{{ route('moonlight.browse.element', $element->class_id) }}">{{ $element->name }}</a>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
@endforeach
