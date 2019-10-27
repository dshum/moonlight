<td class="editable" mode="view" name="{{ $name }}">
    <div class="view-container">
        {{ isset($list[$value]) ? $list[$value] : $value }}
    </div>
    <div class="edit-container">
        <select name="editing[{{ $element->id }}][{{ $name }}]" value="{{ $value }}" disabled="disabled">
            @foreach ($list as $key => $title)
                @if ($key == $value)
                    <option value="{{ $key }}" selected>{{ $title }}</option>
                @else
                    <option value="{{ $key }}">{{ $title }}</option>
                @endif
            @endforeach
        </select>
    </div>
</td>
