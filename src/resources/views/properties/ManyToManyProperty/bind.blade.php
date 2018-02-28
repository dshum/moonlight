<label>{{ $relatedItem->getTitle() }}:</label>
<span name="{{ $name }}" container></span><br>
<input type="hidden" name="{{ $name }}" property="{{ $name }}" value="">
<input type="text" class="one" item="{{ $relatedClass }}" property="{{ $name }}" name="{{ $name }}_autocomplete" value="" placeholder="ID или название">