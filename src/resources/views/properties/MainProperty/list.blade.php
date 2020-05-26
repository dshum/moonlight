<td class="name">
    @if ($trashed)
        <a href="{{ route('moonlight.trashed.view', $class_id) }}"><span>{{ $value }}</span></a>
    @else
        <a href="{{ route('moonlight.element.edit', $class_id) }}"><i class="fa fa-pencil"></i><span>{{ $value }}</span></a>
    @endif
</td>
