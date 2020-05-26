@foreach ($favoriteRubrics as $favoriteRubric)
    <div class="favorite elements" data-rubric="{{ $favoriteRubric->id }}" data-display="{{ empty($views[$favoriteRubric->id]) ? 'none' : 'show' }}">
        <div class="h2"><span>{{ $favoriteRubric->name }}</span></div>
        @if (isset($views[$favoriteRubric->id]))
            {!! $views[$favoriteRubric->id] !!}
        @endif
    </div>
@endforeach
@foreach ($rubrics as $rubric)
    <div class="elements" data-rubric="{{ $rubric->getName() }}" data-display="{{ isset($views[$rubric->getName()]) ? 'show' : 'none' }}">
        <div class="h2"><span>{{ $rubric->getTitle() }}</span></div>
        @if (isset($views[$rubric->getName()]))
            {!! $views[$rubric->getName()] !!}
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
