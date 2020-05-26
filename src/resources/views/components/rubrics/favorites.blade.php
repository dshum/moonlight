@if (! empty($favorites))
    <ul>
        @foreach ($favorites as $favorite)
            <li class="{{ $currentClassId == $favorite->class_id ? 'active' : '' }}">
                <a href="{{ $favorite->browse_url }}" data-edit-url="{{ $favorite->edit_url }}" data-item-title="{{ $favorite->item_title }}">{{ $favorite->name }}</a>
            </li>
        @endforeach
    </ul>
@endif
