<div><label>{{ $relatedItem->getTitle() }}:</label><span data-name="{{ $name }}" class="element-container"></span></div>
<input type="hidden" name="{{ $name }}" data-property="{{ $name }}" value="">
<input type="text" class="one" data-item="{{ $relatedItem->getName() }}" data-property="{{ $name }}" name="{{ $name }}_autocomplete" value="" placeholder="ID или название">
