@foreach ($favoriteRubrics as $favoriteRubric)
<div class="favorite elements" rubric="{{ $favoriteRubric->id }}" display="{{ isset($favorites[$favoriteRubric->id]) && sizeof($favorites[$favoriteRubric->id]) ? 'show' : 'none' }}">
    <div class="h2"><span>{{ $favoriteRubric->name }}</span></div>
    @if (isset($favorites[$favoriteRubric->id]) && sizeof($favorites[$favoriteRubric->id]))
    <ul>
        @foreach ($favorites[$favoriteRubric->id] as $favorite)
        <li class="{{ $classId == $favorite['classId'] ? 'active' : '' }}"><a href="{{ route('moonlight.browse.element', $favorite['classId']) }}" item="{{ $favorite['itemName'] }}">{{ $favorite['name'] }}</a></li>
        @endforeach
    </ul>
    @endif
</div>
@endforeach
@foreach ($rubrics as $rubric)
<div class="elements" rubric="{{ $rubric->getName() }}" display="{{ isset($views[$rubric->getName()]) ? 'show' : 'none' }}">
    <div class="h2"><span>{{ $rubric->getTitle() }}</span></div>
    @if (isset($views[$rubric->getName()]))
        @foreach ($views[$rubric->getName()] as $view)
        {!! $view !!}
        @endforeach
    @endif
</div>
@endforeach
<div class="contextmenu">
    <ul>
        <li class="title"><span></span><br><small></small></li>
        <li class="edit"><a href="">Редактировать</a></li>
        <li class="browse"><a href="">Открыть</a></li>
    </ul>
</div>