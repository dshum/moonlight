<ul data-node="{{ $parentClassId }}">
    @foreach ($elements as $element)
        <li class="{{ $element->class_id == $currentClassId ? 'active' : '' }}">
            @if ($element->children)
                <span class="wrap"><a href="{{ $element->browse_url }}" data-edit-url="{{ $element->edit_url }}" data-item-title="{{ $element->item_title }}">{{ $element->name }}</a>&nbsp;<span class="open rotate" data-rubric="{{ $name }}" data-class-id="{{ $element->class_id }}" data-display="show"><i class="fa fa-angle-down"></i></span></span>
                {!! $element->children !!}
            @elseif ($element->has_children)
                <span class="wrap"><a href="{{ $element->browse_url }}" data-edit-url="{{ $element->edit_url }}" data-item-title="{{ $element->item_title }}">{{ $element->name }}</a>&nbsp;<span class="open" data-rubric="{{ $name }}" data-class-id="{{ $element->class_id }}" data-display="none"><i class="fa fa-angle-down"></i></span></span>
            @else
                <a href="{{ $element->browse_url }}" data-edit-url="{{ $element->edit_url }}" data-item-title="{{ $element->item_title }}">{{ $element->name }}</a>
            @endif
        </li>
    @endforeach
</ul>
