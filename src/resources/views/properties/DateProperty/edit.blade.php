@if ($readonly && $value)
    <div>
        <label>{{ $title }}:</label> {{ $value->format('d.m.Y') }}
    </div>
@elseif ($readonly)
    <div>
        <label>{{ $title }}:</label> Не определено
    </div>
@else
    <input type="hidden" name="{{ $name }}" data-property="{{ $name }}" value="{{ $value ? $value->format('Y-m-d') : '' }}" class="date">
    <div>
        <label>{{ $title }}:</label>
        @if ($value)
            <span class="datetime-container" data-property="{{ $name }}">
                <span class="datepicker" data-property="{{ $name }}">{{ $value->format('d.m.Y') }}</span>
            </span>
        @else
            <span class="datetime-container" data-property="{{ $name }}">
                <span class="datepicker" data-property="{{ $name }}">Не определено</span>
            </span>
        @endif
        <span data-name="{{ $name }}" class="error"></span>
    </div>
@endif
