@if ($total || isset($hasBrowseFilter))
<div class="item active">
    <ul class="header">
        <li class="h2" display="none"><span>{{ $currentItem->getTitle() }}</span></li>
        <li class="total">
            <span class="order-toggler">Всего {{ $total }} {{ Moonlight\Utils\RussianText::selectCaseForNumber($total, ['элемент', 'элемента', 'элементов']) }}.</span>
        </li>
    </ul>
</div>
@endif