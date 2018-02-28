<td>
@if ($value)
<a href="{{ route('moonlight.element.edit', $value['classId']) }}">{{ $value['name'] }}</a>
@endif
</td>