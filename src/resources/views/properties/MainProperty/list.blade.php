@if ($trashed)
<td class="name"><a href="{{ route('moonlight.trashed.view', $classId) }}"><i class="fa fa-pencil"></i><span>{{ $value }}</span></a></td>
@else
<td class="name"><a href="{{ route('moonlight.element.edit', $classId) }}"><i class="fa fa-pencil"></i><span>{{ $value }}</span></a></td>
@endif