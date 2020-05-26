<div class="label mainp">
    <i class="fa fa-flag"></i><span>ID или название</span><span class="addition unset" data-property="{{ $name }}">Очистить</span>
</div>
<div>
    <input type="hidden" name="{{ $name }}" value="{{ $id }}">
    <input type="text" class="one" data-item="{{ $relatedItem->getName() }}" data-property="{{ $name }}" name="{{ $name }}_autocomplete" value="{{ $text }}" placeholder="ID или название">
</div>
