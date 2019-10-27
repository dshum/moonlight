<label>{{ $title }}:</label><span name="{{ $name }}" class="error"></span><br>
@if ($readonly)
    <select name="{{ $name }}" value="{{ $value }}" disabled="disabled">
        @foreach ($list as $key => $title)
            @if ($key == $value)
                <option value="{{ $key }}" selected>{{ $title }}</option>
            @else
                <option value="{{ $key }}">{{ $title }}</option>
            @endif
        @endforeach
    </select>
@else
    <select name="{{ $name }}" value="{{ $value }}">
        @foreach ($list as $key => $title)
            @if ($key == $value)
                <option value="{{ $key }}" selected>{{ $title }}</option>
            @else
                <option value="{{ $key }}">{{ $title }}</option>
            @endif
        @endforeach
    </select>
@endif
