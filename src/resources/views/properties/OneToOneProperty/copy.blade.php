@if ($itemPlace)
    <label>{{ $title }}:</label>
    <span data-name="{{ $name }}" class="element-container">
        @if ($value)
            <a href="{{ route('moonlight.element.edit', $value->class_id) }}">{{ $value->name }}</a>
        @else
            Не определено
        @endif
    </span>
    <span data-name="{{ $name }}" class="error"></span>
    @if (! $readonly)
        <div>
            <input type="hidden" name="{{ $name }}" data-property="{{ $name }}" value="{{ $value ? $value->id : null }}">
            <input type="text" class="one" data-item="{{ $relatedItem->getName() }}" data-property="{{ $name }}" name="{{ $name }}_autocomplete" value="" placeholder="ID или название">
            @if (! $required)
                <span class="addition unset" data-property="{{ $name }}">Очистить</span>
            @endif
        </div>
    @endif
@elseif ($countPlaces > 1)
    @if ($rootPlace)
        <p>
            <input type="radio" name="{{ $name }}_copy" data-property="{{ $name }}" id="{{ $name }}_copy_null" value="" {{ ! $value ? 'checked' : '' }}>
            <label for="{{ $name }}_copy_null">Корень сайта</label>
        </p>
    @endif
    @foreach ($elementPlaces as $element)
        <p>
            <input type="radio" name="{{ $name }}_copy" data-property="{{ $name }}" id="{{ $name }}_copy_{{ $element->id }}" value="{{ $element->id }}" {{ $value && $value->id == $element->id ? 'checked' : '' }}>
            <label for="{{ $name }}_copy_{{ $element->id }}">{{ $element->name }}</label>
        </p>
    @endforeach
@elseif (sizeof($elementPlaces) == 1)
    @foreach ($elementPlaces as $element)
        {{ $element->name }}
    @endforeach
@elseif ($rootPlace)
    Корень сайта
@endif
