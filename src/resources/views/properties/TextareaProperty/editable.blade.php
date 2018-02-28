<td class="editable" mode="view" name="{{ $name }}">
    <div  class="view-container">
        {{ $value }}
    </div>
    <div class="edit-container">
        <textarea name="editing[{{ $element->id }}][{{ $name }}]" placeholder="{{ $title }}" disabled>{{ $value }}</textarea>
    </div>
</td>