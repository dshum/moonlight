<input type="text" id="filter" placeholder="Название">
<div class="sort" data-active-item="{{ $currentItem ? $currentItem->getName() : '' }}">
    @if ($sort == 'rate')
        Отсортировано по частоте.<br>
        Сортировать по <span data-sort="date">дате</span>, <span data-sort="name">названию</span>,
        <span data-sort="default">умолчанию</span>.
    @elseif ($sort == 'date')
        Отсортировано по дате.<br>
        Сортировать по <span data-sort="rate">частоте</span>, <span data-sort="name">названию</span>,
        <span data-sort="default">умолчанию</span>.
    @elseif ($sort == 'name')
        Отсортировано по названию.<br>
        Сортировать по <span data-sort="rate">частоте</span>, <span data-sort="date">дате</span>,
        <span data-sort="default">умолчанию</span>.
    @else
        Отсортировано по умолчанию.<br>
        Сортировать по <span data-sort="rate">частоте</span>, <span data-sort="date">дате</span>,
        <span data-sort="name">названию</span>.
    @endif
</div>
<ul class="items">
    @foreach ($items as $item)
        <li class="{{ $currentItem && $item->getName() == $currentItem->getName() ? 'active' : '' }}">
            <a href="{{ route('moonlight.search.item', $item->getName()) }}">{{ $item->getTitle() }}</a><br>
            <small>{{ $item->getClassBaseName() }}</small>
        </li>
    @endforeach
</ul>
