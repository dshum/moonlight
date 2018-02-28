<input type="text" id="filter" placeholder="Название">
<div class="sort" activeItem="{{ $currentItem ? $currentItem->getNameId() : '' }}">
    @if ($sort == 'rate')
    Отсортировано по частоте.<br>
    Сортировать по <span sort="date">дате</span>, <span sort="name">названию</span>, <span sort="default">умолчанию</span>.
    @elseif ($sort == 'date')
    Отсортировано по дате.<br>
    Сортировать по <span sort="rate">частоте</span>, <span sort="name">названию</span>, <span sort="default">умолчанию</span>.
    @elseif ($sort == 'name')
    Отсортировано по названию.<br>
    Сортировать по <span sort="rate">частоте</span>, <span sort="date">дате</span>, <span sort="default">умолчанию</span>.
    @else
    Отсортировано по умолчанию.<br>
    Сортировать по <span sort="rate">частоте</span>, <span sort="date">дате</span>, <span sort="name">названию</span>.
    @endif
</div>
<ul class="items">
    @foreach ($items as $item)
    <li class="{{ $currentItem && $item->getNameId() == $currentItem->getNameId() ? 'active' : '' }}"><a href="{{ route('moonlight.search.item', $item->getNameId()) }}">{{ $item->getTitle() }}</a><br><small>{{ $item->getNameId() }}</small></li>
    @endforeach
</ul>