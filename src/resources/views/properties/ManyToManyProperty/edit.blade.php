@if ($readonly)
    <label>{{ $title }}:</label><br>
    @foreach ($elements as $element)
        <div><a href="{{ route('moonlight.element.edit', $element->class_id) }}">{{ $element->name }}</a></div>
    @endforeach
@else
    <div>
        <label>{{ $title }}:</label>
        <span data-name="{{ $name }}" class="element-container"></span>
        <span data-name="{{ $name }}" class="error"></span>
    </div>
    <input type="text" class="many" data-item="{{ $relatedItem->getName() }}" data-property="{{ $name }}" name="{{ $name }}_autocomplete" value="" placeholder="ID или название">
    <span class="addition unset" data-property="{{ $name }}">Очистить</span>
    <span class="addition add" data-property="{{ $name }}">Добавить</span>
    <div class="many elements" data-name="{{ $name }}">
        @foreach ($elements as $element)
            <p>
                <input type="checkbox" name="{{ $name }}[]" id="{{ $element->class_id }}" checked value="{{ $element->id }}"><label for="{{ $element->class_id }}">{{ $element->name }}</label>
            </p>
        @endforeach
    </div>
@endif
