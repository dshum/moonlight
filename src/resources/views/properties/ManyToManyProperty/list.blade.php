<td class="many">
    @foreach ($elements as $element)
        <div>
            <a href="{{ route('moonlight.element.edit', $element->class_id) }}">{{ $element->name }}</a>
        </div>
    @endforeach
</td>
