<ul node="{{ $parent }}">
    @foreach ($elements as $element)
    <li class="{{ $element['classId'] == $classId ? 'active' : '' }}">
        @if (isset($children[$element['classId']]))
        <a href="{{ route('moonlight.browse.element', $element['classId']) }}" item="{{ $element['itemName'] }}">{{ $element['name'] }}</a><span class="open" rubric="{{ $name }}" bind="{{ $bind }}" classId="{{ $element['classId'] }}" display="show"><i class="fa fa-angle-down"></i></span>
        {!! $children[$element['classId']] !!}
        @elseif (isset($haschildren[$element['classId']]))
        <a href="{{ route('moonlight.browse.element', $element['classId']) }}" item="{{ $element['itemName'] }}">{{ $element['name'] }}</a><span class="open" rubric="{{ $name }}" bind="{{ $bind }}" classId="{{ $element['classId'] }}" display="none"><i class="fa fa-angle-down"></i></span>
        @else
        <a href="{{ route('moonlight.browse.element', $element['classId']) }}" item="{{ $element['itemName'] }}">{{ $element['name'] }}</a>
        @endif
    </li>
    @endforeach
</ul>