@if ($readonly && $value)
<div>
    <label>{{ $title }}:</label> {{ $value->format('d.m.Y') }}, {{ $value->format('H:i:s') }}
</div>
@elseif ($readonly)
<div>
    <label>{{ $title }}:</label> Не определено
</div>
@else
<div>
    <label>{{ $title }}:</label><span name="{{ $name }}" class="error"></span><br>
    <input type="text" name="{{ $name }}_date" value="{{ $value ? $value->format('Y-m-d') : '' }}" class="date" placeholder="">,&nbsp;
    <input type="text" name="{{ $name }}_hours" value="{{ $value ? $value->format('H') : '' }}" class="time"> :
    <input type="text" name="{{ $name }}_minutes" value="{{ $value ? $value->format('i') : '' }}" class="time"> :
    <input type="text" name="{{ $name }}_seconds" value="{{ $value ? $value->format('s') : '' }}" class="time">
</div>
@endif