@if ($readonly && $value)
<div>
    <label>{{ $title }}:</label> {{ $value->format('d.m.Y') }}, {{ $value->format('H:i:s') }}
</div>
@elseif ($readonly)
<div>
    <label>{{ $title }}:</label> Не определено
</div>
@else
<input type="hidden" name="{{ $name }}_date" property="{{ $name }}" value="{{ $value ? $value->format('Y-m-d') : '' }}" class="datetime">
<input type="hidden" name="{{ $name }}_time" property="{{ $name }}" value="{{ $value ? $value->format('H:i:s') : '' }}" class="time">
<div>
    <label>{{ $title }}:</label>
    <span class="datetime-container" property="{{ $name }}">
    @if ($value)
    <span class="datepicker" property="{{ $name }}">{{ $value->format('d.m.Y') }}</span>,
    <span class="timepicker" property="{{ $name }}">{{ $value->format('H:i:s') }}</span>
    @else
    <span class="datepicker" property="{{ $name }}">Не определено</span>
    @endif
    </span>
    <span name="{{ $name }}" class="error"></span>
</div>
<div class="timepicker-popup" property="{{ $name }}">
    <div class="block">
        <div class="title hours">Часы</div>
        <table class="hours">
            @for ($i = 0; $i < 6; $i++)<tr>@for ($j = 0; $j < 4; $j++)<td value="{{ sprintf('%02d', $i * 4 + $j) }}" class="{{ $value && $value->format('H') == $i * 4 + $j ? 'active' : '' }}">{{ sprintf('%02d', $i * 4 + $j) }}</td>@endfor</tr>@endfor
        </table>
    </div>
    <div class="block">
        <div class="title minutes">Минуты</div>
        <table class="minutes">
            @for ($i = 0; $i < 6; $i++)<tr>@for ($j = 0; $j < 10; $j++)<td value="{{ sprintf('%02d', $i * 10 + $j) }}" class="{{ $j % 5 ? 'add hide' : '' }} {{ $value && $value->format('i') == $i * 10 + $j ? 'active' : '' }}">{{ sprintf('%02d', $i * 10 + $j) }}</td>@endfor</tr>@endfor
        </table>
    </div>
    <div class="block">
        <div class="title seconds">Секунды</div>
        <table class="seconds">
            @for ($i = 0; $i < 6; $i++)<tr>@for ($j = 0; $j < 10; $j++)<td value="{{ sprintf('%02d', $i * 10 + $j) }}" class="{{ $j % 5 ? 'add hide' : '' }} {{ $value && $value->format('s') == $i * 10 + $j ? 'active' : '' }}">{{ sprintf('%02d', $i * 10 + $j) }}</td>@endfor</tr>@endfor
        </table>
    </div>
</div>
@endif