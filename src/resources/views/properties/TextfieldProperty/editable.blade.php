<td class="editable" mode="view" name="{{ $name }}">
    <div  class="view-container">
        {{ $value }}
    </div>
    <div class="edit-container">
        <input type="text" name="editing[{{ $element->id }}][{{ $name }}]" value="{{ $value }}" placeholder="{{ $title }}" disabled>
    </div>
</td>