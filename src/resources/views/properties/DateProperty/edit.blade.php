@if ($readonly && $value)
<div>
    <label>{{ $title }}:</label> {{ $value->format('d.m.Y') }}
</div>
@elseif ($readonly)
<div>
    <label>{{ $title }}:</label> Не определено
</div>
@else
<input type="hidden" name="{{ $name }}" property="{{ $name }}" value="{{ $value ? $value->format('Y-m-d') : '' }}" class="date">
<div>
    <label>{{ $title }}:</label>
    <span class="datetime-container" property="{{ $name }}">
    @if ($value)
    <span class="datepicker" property="{{ $name }}">{{ $value->format('d.m.Y') }}</span>
    @else
    <span class="datepicker" property="{{ $name }}">Не определено</span>
    @endif
    </span>
    <span name="{{ $name }}" class="error"></span>
</div>
@endif