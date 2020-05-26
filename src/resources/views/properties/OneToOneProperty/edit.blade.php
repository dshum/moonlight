@if ($readonly)
    <label>{{ $title }}:</label>
    @if ($value)
        <a href="{{ route('moonlight.element.edit', $value->class_id) }}">{{ $value->name }}</a>
    @else
        Не определено
    @endif
@else
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
            <label>{{ $title }}:</label>
            <span data-name="{{ $name }}" class="error"></span>
            <p>
                <input type="radio" name="{{ $name }}" data-property="{{ $name }}" id="{{ $name }}_null" value="" {{ ! $value ? 'checked' : '' }}>
                <label for="{{ $name }}_null">Корень сайта</label>
            </p>
        @endif
        @foreach ($elementPlaces as $element)
            <p>
                <input type="radio" name="{{ $name }}" data-property="{{ $name }}" id="{{ $name }}_{{ $element->id }}" value="{{ $element->id }}" {{ $value && $value->id == $element->id ? 'checked' : '' }}>
                <label for="{{ $name }}_{{ $element->id }}">{{ $element->name }}</label>
            </p>
        @endforeach
    @elseif (sizeof($elementPlaces) == 1)
        <label>{{ $title }}:</label>
        <span data-name="{{ $name }}" class="error"></span>
        @foreach ($elementPlaces as $element)
            <input type="hidden" name="{{ $name }}" data-property="{{ $name }}" value="{{ $element->id }}">
            <a href="{{ route('moonlight.element.edit', $element->class_id) }}">{{ $element->name }}</a>
        @endforeach
    @else
        <label>{{ $title }}:</label>
        <span data-name="{{ $name }}" class="element-container">
            @if ($value)
                <a href="{{ route('moonlight.element.edit', $value->class_id) }}">{{ $value->name }}</a>
            @else
                Не определено
            @endif
        </span>
        <span data-name="{{ $name }}" class="error"></span>
        <div>
            <input type="hidden" name="{{ $name }}" data-property="{{ $name }}" value="{{ $value ? $value->id : null }}">
            <input type="text" class="one" data-item="{{ $relatedItem->getName() }}" data-property="{{ $name }}" name="{{ $name }}_autocomplete" value="" placeholder="ID или название">
            @if (! $required)
                <span class="addition unset" data-property="{{ $name }}">Очистить</span>
            @endif
        </div>
    @endif
@endif
