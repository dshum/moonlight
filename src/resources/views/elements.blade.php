@stack('styles')
@stack('scripts')
@if (isset($itemComponentView) && $itemComponentView)
    {!! $itemComponentView !!}
@endif
@if ($total)
    <ul class="header">
        <li class="h2" data-display="show">
            <span>{{ $currentItem->getTitle() }}</span></li>
        <li class="total">
            <span>Всего {{ $total }} {{ Moonlight\Utils\RussianText::selectCaseForNumber($total, ['элемент', 'элемента', 'элементов']) }}.</span>
            @if ($orders && $hasOrderProperty)
                <span class="sort-toggler">Отсортировано по {!! $orders !!}.</span>
            @elseif ($orders)
                <span>Отсортировано по {!! $orders !!}.</span>
            @endif
        </li>
        <li class="column-toggler" data-display="hide">
            <span>Поля таблицы</span><i class="fa fa-angle-down"></i>
            <div class="dropdown">
                <div class="container">
                    @if (! $hasOrderProperty)
                        <div class="perpage">
                            <div class="title">Элементов на странице:</div>
                            <div class="input"><input type="text" name="per_page" value="{{ $perPage }}"></div>
                        </div>
                    @endif
                    @if (sizeof($columns))
                        <ul style="columns: {{ $columnsCount }};">
                            @foreach ($columns as $column)
                                @if ($column['show'])
                                    <li data-name="{{ $column['name'] }}" data-show="true" class="checked">
                                        <span class="eye"></span>{{ $column['title'] }}
                                    </li>
                                @else
                                    <li data-name="{{ $column['name'] }}" data-show="false">
                                        <span class="eye"></span>{{ $column['title'] }}
                                    </li>
                                @endif
                            @endforeach
                        </ul>
                    @endif
                    <div class="reload">
                        <input type="button" value="Обновить" class="btn small">
                    </div>
                </div>
            </div>
        </li>
        @if ($mode == 'browse' && $lastPage > 1)
            <li class="search-link">
                <a href="{{ route('moonlight.search.item', ['item' => $currentItem->getName(), 'action' => 'search']) }}">Поиск</a>
            </li>
        @endif
    </ul>
    @if (isset($filterComponentView) && $filterComponentView)
        {!! $filterComponentView !!}
    @endif
    <div class="list-container">
        @if (sizeof($elements))
            <div class="buttons">
                @if ($mode == 'trash')
                    <div class="button restore"><i class="fa fa-arrow-left"></i>Восстановить</div>
                    <div class="button delete"><i class="fa fa-ban"></i>Удалить</div>
                @else
                    <div class="button save"><i class="fa fa-floppy-o"></i>Сохранить</div>
                    <div class="button copy{{ $copyPropertyView ? '' : ' disabled' }}"><i class="fa fa-clone"></i>Копировать
                    </div>
                    <div class="button move{{ $movePropertyView ? '' : ' disabled' }}">
                        <i class="fa fa-arrow-right"></i>Перенести
                    </div>
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
            <form name="save" data-save="true" action="{{ route('moonlight.elements.save') }}" method="POST">
                <input type="hidden" name="item" value="{{ $currentItem->getName() }}">
                <table class="elements">
                    <thead>
                    <tr>
                        <th class="browse">
                            <span class="reset-column-order" data-reset-order="true" title="Сортировать по умолчанию"><i class="fa fa-sort"></i></span>
                        </th>
                        @foreach ($properties as $property)
                            <th>
                                @if (isset($orderByList[$property->getName()]))
                                    @if ($orderByList[$property->getName()] == 'desc')
                                        <span class="column-order" data-order="{{ $property->getName() }}" data-direction="asc">{{ $property->getTitle() }}</span>
                                        <i class="fa fa-sort-desc"></i>
                                    @else
                                        <span class="column-order" data-order="{{ $property->getName() }}" data-direction="desc">{{ $property->getTitle() }}</span>
                                        <i class="fa fa-sort-asc"></i>
                                    @endif
                                @elseif ($property->isSortable())
                                    <span class="column-order" data-order="{{ $property->getName() }}" data-direction="asc">{{ $property->getTitle() }}</span>
                                @else
                                    <span>{{ $property->getTitle() }}</span>
                                @endif
                            </th>
                        @endforeach
                        <th class="check">
                            <div class="check"></div>
                        </th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($elements as $element)
                        <tr data-element-id="{{ $element->id }}" data-rubrics="{{ $elementFavoriteRubrics[$element->id] }}">
                            @if ($mode == 'browse')
                                <td class="browse">
                                    <a href="{{ route('moonlight.browse.element', class_id($element)) }}"><i class="fa fa-angle-right"></i></a>
                                    <span class="drag"><i class="fa fa-arrows-alt"></i></span>
                                </td>
                            @elseif ($mode == 'search')
                                <td class="browse">
                                    <a href="{{ route('moonlight.browse.element', class_id($element)) }}"><i class="fa fa-angle-right"></i></a>
                                </td>
                            @else
                                <td class="browse"><i class="fa fa-angle-right"></i></td>
                            @endif
                            @if (isset($views[$element->id]))
                                @foreach ($views[$element->id] as $view)
                                    {!! $view !!}
                                @endforeach
                            @endif
                            <td class="check">
                                <div class="check"></div>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
                <input type="submit" class="dnone">
            </form>
            @if ($lastPage > 1)
                <ul class="pager" data-page="{{ $currentPage }}" data-last="{{ $lastPage }}">
                    <li data-link="prev" class="arrow {{ $currentPage > 1 ? 'active' : '' }}">
                        <i class="fa fa-arrow-left"></i>
                    </li>
                    <li data-link="first" class="arrow {{ $currentPage > 1 ? 'active' : '' }}">1</li>
                    <li class="page"><input type="text" value="{{ $currentPage }}"></li>
                    <li data-link="last" class="arrow {{ $currentPage < $lastPage ? 'active' : '' }}">{{ $lastPage }}</li>
                    <li data-link="next" class="arrow {{ $currentPage < $lastPage ? 'active' : '' }}">
                        <i class="fa fa-arrow-right"></i>
                    </li>
                </ul>
            @endif
        @else
            <div class="empty">Элементов не найдено.</div>
        @endif
    </div>
    @includeWhen($copyPropertyView, 'moonlight::components.browse.confirm.copy')
    @includeWhen($movePropertyView, 'moonlight::components.browse.confirm.move')
    @includeWhen(($mode === 'trash'), 'moonlight::components.browse.confirm.restore')
    @includeWhen($bindPropertyViews, 'moonlight::components.browse.confirm.bind')
    @includeWhen($unbindPropertyViews, 'moonlight::components.browse.confirm.unbind')
    @includeWhen(($mode !== 'trash'), 'moonlight::components.browse.confirm.favorite')
    @include('moonlight::components.browse.confirm.delete')
@elseif ($mode !== 'browse')
    <div class="empty">Элементов не найдено.</div>
@endif
