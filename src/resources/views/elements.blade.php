@if ($total && $itemPluginView)
    {!! $itemPluginView !!}
@endif
@if ($total || isset($hasBrowseFilter))
<div class="item active">
    <ul class="header">
        <li class="h2" display="show"><span>{{ $currentItem->getTitle() }}</span></li>
        <li class="total">
            <span>Всего {{ $total }} {{ Moonlight\Utils\RussianText::selectCaseForNumber($total, ['элемент', 'элемента', 'элементов']) }}.</span>
            @if ($orders && $total)
                @if ($hasOrderProperty)
                <span class="sort-toggler">Отсортировано по {!! $orders !!}.</span>
                @else
                Отсортировано по {!! $orders !!}.
                @endif
            @endif
        </li>
        @isset($columns)
        <li class="column-toggler" display="hide">
            <span>Поля таблицы</span><i class="fa fa-angle-down"></i>
            <div class="dropdown">
                <div class="container">
                    <ul>
                        @foreach ($columns as $column)
                        <li name="{{ $column['name'] }}" show="{{ $column['show'] ? 'true' : 'false' }}"><span class="eye"></span>{{ $column['title'] }}</li>
                        @endforeach
                        <li class="reload"><input type="button" value="Обновить" class="btn small"></li>
                    </ul>
                </div>
            </div>
        </li>
        @endisset
        @if ($mode == 'browse' && $lastPage > 1)
        <li class="search-link"><a href="{{ route('moonlight.search.item', ['item' => $currentItem->getNameId(), 'action' => 'search']) }}">Поиск</a></li>
        @endif
    </ul>
    <div list>
        @if (isset($browseFilterView) && $browseFilterView)
        <div class="plugin">
            {!! $browseFilterView !!}
        </div>
        @endif
        @if (sizeof($elements))
        <div class="buttons">
            @if ($mode == 'trash')
            <div class="button restore"><i class="fa fa-arrow-left"></i>Восстановить</div>
            <div class="button delete"><i class="fa fa-trash-o"></i>Удалить</div>
            @else
            <div class="button save"><i class="fa fa-floppy-o"></i>Сохранить</div>
            <div class="button copy{{ $copyPropertyView ? '' : ' disabled' }}"><i class="fa fa-clone"></i>Копировать</div>
            <div class="button move{{ $movePropertyView ? '' : ' disabled' }}"><i class="fa fa-arrow-right"></i>Перенести</div>
            @if ($bindPropertyViews)
            <div class="button bind"><i class="fa fa-link"></i>Привязать</div>
            @endif
            @if ($unbindPropertyViews)
            <div class="button unbind"><i class="fa fa-chain-broken"></i>Отвязать</div>
            @endif
            <div class="button favorite"><i class="fa fa-bookmark-o"></i>Избранное</div>
            <div class="button delete"><i class="fa fa-trash-o"></i>Удалить</div>
            @endif
        </div>
        <form name="save" save="true" action="{{ route('moonlight.elements.save') }}" method="POST">
            <input type="hidden" name="item" value="{{ $currentItem->getNameId() }}">
            <table class="elements">
                <thead>
                    <tr>
                        <th class="browse"><span resetorder="true" title="Сортировать по умолчанию"><i class="fa fa-sort"></i></span></th>
                        @foreach ($properties as $property)
                        <th>
                            @if (isset($orderByList[$property->getName()]))
                                @if ($orderByList[$property->getName()] == 'desc')
                                <span order="{{ $property->getName() }}" direction="asc">{{ $property->getTitle() }}</span>
                                <i class="fa fa-sort-desc"></i>
                                @else
                                <span order="{{ $property->getName() }}" direction="desc">{{ $property->getTitle() }}</span>
                                <i class="fa fa-sort-asc"></i>
                                @endif
                            @elseif ($property->isSortable())
                            <span order="{{ $property->getName() }}" direction="asc">{{ $property->getTitle() }}</span>
                            @else
                            <span>{{ $property->getTitle() }}</span>
                            @endif
                        </th>
                        @endforeach
                        <th class="check"><div class="check"></div></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($elements as $element)
                    <tr elementId="{{ $element->id }}" rubrics="{{ $elementFavoriteRubrics[$element->id] }}">
                        @if ($mode == 'browse')
                        <td class="browse">
                            <a href="{{ route('moonlight.browse.element', \Moonlight\Main\Element::getClassId($element)) }}"><i class="fa fa-angle-right"></i></a>
                            <span class="drag"><i class="fa fa-arrows-alt"></i></span>
                        </td>
                        @elseif ($mode == 'search')
                        <td class="browse"><a href="{{ route('moonlight.browse.element', \Moonlight\Main\Element::getClassId($element)) }}"><i class="fa fa-angle-right"></i></a></td>
                        @else
                        <td class="browse"><i class="fa fa-angle-right"></i></td>
                        @endif
                        @if (isset($views[Moonlight\Main\Element::getClassId($element)]))
                            @foreach ($views[Moonlight\Main\Element::getClassId($element)] as $view)
                                {!! $view !!}
                            @endforeach
                        @endif
                        <td class="check"><div class="check"></div></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            <input type="submit">
        </form>
        @if ($lastPage > 1)
        <ul class="pager" classId="{{ isset($classId) ? $classId : ''}}" item="{{ $currentItem->getNameId() }}" page="{{ $currentPage }}" last="{{ $lastPage }}">
            <li prev class="arrow {{ $currentPage > 1 ? 'active' : '' }}"><i class="fa fa-arrow-left"></i></li>
            <li first class="arrow {{ $currentPage > 1 ? 'active' : '' }}">1</li>
            <li class="page"><input type="text" value="{{ $currentPage }}"></li>
            <li last class="arrow {{ $currentPage < $lastPage ? 'active' : '' }}">{{ $lastPage }}</li>
            <li next class="arrow {{ $currentPage < $lastPage ? 'active' : '' }}"><i class="fa fa-arrow-right"></i></li>
        </ul>
        @endif
        @else
        <div class="empty">Элементов не найдено.</div>
        @endif
    </div>
</div>
@if ($mode == 'trash')
<div class="confirm" id="{{ $currentItem->getNameId() }}_restore">
    <div class="wrapper">
        <div class="container">
            <div class="content">
                Восстановить выбранные элементы?
            </div>
            <div class="bottom">
                <input type="button" value="Восстановить" class="btn restore">
                <input type="button" value="Отмена" class="btn cancel">
            </div>
        </div>
    </div>
</div>
@endif
@if ($copyPropertyView)
<div class="confirm" id="{{ $currentItem->getNameId() }}_copy">
    <div class="wrapper">
        <div class="container">
            <div class="content">
                <div>Куда копируем?</div>
                <div class="edit">
                    <div class="row">
                    {!! $copyPropertyView !!}
                    </div>
                </div>
            </div>
            <div class="bottom">
                <input type="button" value="Скопировать" class="btn copy">
                <input type="button" value="Отмена" class="btn cancel">
            </div>
        </div>
    </div>
</div>
@endif
@if ($movePropertyView)
<div class="confirm" id="{{ $currentItem->getNameId() }}_move">
    <div class="wrapper">
        <div class="container">
            <div class="content">
                <div>Куда переносим?</div>
                <div class="edit">
                    <div class="row">
                        {!! $movePropertyView !!}
                    </div>
                </div>
            </div>
            <div class="bottom">
                <input type="button" value="Перенести" class="btn move">
                <input type="button" value="Отмена" class="btn cancel">
            </div>
        </div>
    </div>
</div>
@endif
@if ($bindPropertyViews)
<div class="confirm" id="{{ $currentItem->getNameId() }}_bind">
    <div class="wrapper">
        <div class="container">
            <div class="content">
                <div>Выберите элемент,<br>который вы хотите привязать:</div>
                <div class="edit">
                @foreach ($bindPropertyViews as $bindPropertyView)
                <div class="row">
                    {!! $bindPropertyView !!}
                </div>
                @endforeach
                </div>
            </div>
            <div class="bottom">
                <input type="button" value="Привязать" class="btn bind">
                <input type="button" value="Отмена" class="btn cancel">
            </div>
        </div>
    </div>
</div>
@endif
@if ($unbindPropertyViews)
<div class="confirm" id="{{ $currentItem->getNameId() }}_unbind">
    <div class="wrapper">
        <div class="container">
            <div class="content">
            <div>Выберите элемент,<br>который вы хотите отвязать:</div>
                <div class="edit">
                    @foreach ($unbindPropertyViews as $unbindPropertyView)
                    <div class="row">
                        {!! $unbindPropertyView !!}
                    </div>
                    @endforeach
                </div>
            </div>
            <div class="bottom">
                <input type="button" value="Отвязать" class="btn unbind">
                <input type="button" value="Отмена" class="btn cancel">
            </div>
        </div>
    </div>
</div>
@endif
@if ($mode != 'trash')
<div class="confirm" id="{{ $currentItem->getNameId() }}_favorite" url="{{ route('moonlight.elements.favorite') }}">
    <div class="wrapper">
        <div class="container">
            @if (sizeof($favoriteRubrics))
            <div class="favorite-settings" title="Настроить избранное"><a href="{{ route('moonlight.favorites.edit') }}"><i class="fa fa-cog"></i></a></div>
            @endif
            <div class="content">
                @if (sizeof($favoriteRubrics))
                <div name="add">Добавить в рубрику:</div>
                <div class="favorite-list add">
                    @foreach ($favoriteRubrics as $favoriteRubric)                
                    <div rubric="{{ $favoriteRubric->id }}">{{ $favoriteRubric->name }}</div>
                    @endforeach
                </div>
                <div name="remove">Убрать из рубрики:</div>
                <div class="favorite-list remove">
                    @foreach ($favoriteRubrics as $favoriteRubric)                
                    <div rubric="{{ $favoriteRubric->id }}">{{ $favoriteRubric->name }}</div>
                    @endforeach
                </div>
                @endif
                <div class="favorite-new">
                    <input type="text" name="favorite_rubric_new" value="" placeholder="Новая рубрика">
                </div>
            </div>
            <div class="bottom">
            <input type="button" value="Добавить" class="btn favorite">
                <input type="button" value="Отмена" class="btn cancel">
            </div>
        </div>
    </div>
</div>
@endif
<div class="confirm" id="{{ $currentItem->getNameId() }}_delete">
    <div class="wrapper">
        <div class="container">
            <div class="content">
                Удалить выбранные элементы?
            </div>
            <div class="bottom">
                <input type="button" value="Удалить" class="btn remove">
                <input type="button" value="Отмена" class="btn cancel">
            </div>
        </div>
    </div>
</div>
@elseif ($mode != 'browse')
<div class="empty">Элементов не найдено.</div>
@endif