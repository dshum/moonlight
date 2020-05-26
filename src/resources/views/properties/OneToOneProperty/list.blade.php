<td>
    @if ($element)
        <a href="{{ route('moonlight.element.edit', $element->class_id) }}">{{ $element->name }}</a>
    @endif
</td>
