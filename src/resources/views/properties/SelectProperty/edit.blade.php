<div><label>{{ $title }}:</label><span data-name="{{ $name }}" class="error"></span></div>
@if ($readonly)
    <select name="{{ $name }}" data-value="{{ $value }}" disabled="disabled">
        @foreach ($list as $key => $title)
            @if ($key == $value)
                <option value="{{ $key }}" selected>{{ $title }}</option>
            @else
                <option value="{{ $key }}">{{ $title }}</option>
            @endif
        @endforeach
    </select>
@else
    <select name="{{ $name }}" data-value="{{ $value }}">
        @foreach ($list as $key => $title)
            @if ($key == $value)
                <option value="{{ $key }}" selected>{{ $title }}</option>
            @else
                <option value="{{ $key }}">{{ $title }}</option>
            @endif
        @endforeach
    </select>
@endif
