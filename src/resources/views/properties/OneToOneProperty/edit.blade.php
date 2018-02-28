@if ($readonly)
    <label>{{ $title }}:</label>
    @if ($value)
        <a href="{{ route('moonlight.element.edit', $value['classId']) }}">{{ $value['name'] }}</a>
    @else
        Не определено
    @endif
@else
    @if ($itemPlace)
        <label>{{ $title }}:</label>
        <span name="{{ $name }}" container>
            @if ($value)
                <a href="{{ route('moonlight.element.edit', $value['classId']) }}">{{ $value['name'] }}</a>
            @else
                Не определено
            @endif
        </span>
        <span name="{{ $name }}" class="error"></span>
        @if (! $readonly)
        <div>
            <input type="hidden" name="{{ $name }}" property="{{ $name }}" value="{{ $value ? $value['id'] : null }}">
            <input type="text" class="one" item="{{ $relatedClass }}" property="{{ $name }}" name="{{ $name }}_autocomplete" value="" placeholder="ID или название">
            @if (! $required)
            <span class="addition unset" property="{{ $name }}">Очистить</span>
            @endif
        </div>
        @endif
    @elseif ($countPlaces > 1)
        @if ($rootPlace)
            <label>{{ $title }}:</label>
            <span name="{{ $name }}" class="error"></span>
            <p>
                <input type="radio" name="{{ $name }}" property="{{ $name }}" id="{{ $name }}_null" value="" {{ ! $value ? 'checked' : '' }}>
                <label for="{{ $name }}_null">Корень сайта</label>
            </p>
        @endif
        @foreach ($elementPlaces as $element)
        <p>
            <input type="radio" name="{{ $name }}" property="{{ $name }}" id="{{ $name }}_{{ $element['id'] }}" value="{{ $element['id'] }}" {{ $value && $value['id'] == $element['id'] ? 'checked' : '' }}>
            <label for="{{ $name }}_{{ $element['id'] }}">{{ $element['name'] }}</label>
        </p>
        @endforeach
    @elseif (sizeof($elementPlaces) == 1)
        <label>{{ $title }}:</label>
        <span name="{{ $name }}" class="error"></span>
        @foreach ($elementPlaces as $element)
        <input type="hidden" name="{{ $name }}" property="{{ $name }}" value="{{ $element['id'] }}">
        <a href="{{ route('moonlight.element.edit', $element['classId']) }}">{{ $element['name'] }}</a>
        @endforeach
    @else
        <label>{{ $title }}:</label>
        <span name="{{ $name }}" container>
            @if ($value)
                <a href="{{ route('moonlight.element.edit', $value['classId']) }}">{{ $value['name'] }}</a>
            @else
                Не определено
            @endif
        </span>
        <span name="{{ $name }}" class="error"></span>
        <div>
            <input type="hidden" name="{{ $name }}" property="{{ $name }}" value="{{ $value ? $value['id'] : null }}">
            <input type="text" class="one" item="{{ $relatedClass }}" property="{{ $name }}" name="{{ $name }}_autocomplete" value="" placeholder="ID или название">
            @if (! $required)
                <span class="addition unset" property="{{ $name }}">Очистить</span>
            @endif
        </div>
    @endif
@endif