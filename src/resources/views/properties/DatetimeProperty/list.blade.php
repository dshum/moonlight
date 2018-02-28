<td class="date">
    @if ($value)
    <div class="date">{{ $value->format('d.m.Y') }}</div>
    <div class="time">{{ $value->format('H:i:s') }}</div>
    @endif
</td>