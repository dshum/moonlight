<div class="label many">
    <i class="fa fa-tags"></i><span>{{ $title }}</span><span class="addition unset" data-property="{{ $name }}">Очистить</span>
</div>
<div>
    <input type="hidden" name="{{ $name }}" value="{{ $value ? $value->id : null }}">
    <input type="text" class="one" data-item="{{ $relatedItem->getName() }}" data-property="{{ $name }}" name="{{ $name }}_autocomplete" value="{{ $value ? $value->name : null }}" placeholder="ID или название">
</div>
