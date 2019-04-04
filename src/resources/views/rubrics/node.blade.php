<ul node="{{ $parent }}">
    @foreach ($elements as $element)
    <li class="{{ $element['classId'] == $classId ? 'active' : '' }}">
        @if (isset($children[$element['classId']]))
        <span class="wrap"><a href="{{ route('moonlight.browse.element', $element['classId']) }}" item="{{ $element['itemName'] }}">{{ $element['name'] }}</a>&nbsp;<span class="open" rubric="{{ $name }}" bind="{{ $bind }}" classId="{{ $element['classId'] }}" display="show"><i class="fa fa-angle-down"></i></span></span>
        {!! $children[$element['classId']] !!}
        @elseif (isset($haschildren[$element['classId']]))
        <span class="wrap"><a href="{{ route('moonlight.browse.element', $element['classId']) }}" item="{{ $element['itemName'] }}">{{ $element['name'] }}</a>&nbsp;<span class="open" rubric="{{ $name }}" bind="{{ $bind }}" classId="{{ $element['classId'] }}" display="none"><i class="fa fa-angle-down"></i></span></span>
        @else
        <a href="{{ route('moonlight.browse.element', $element['classId']) }}" item="{{ $element['itemName'] }}">{{ $element['name'] }}</a>
        @endif
    </li>
    @endforeach
</ul>