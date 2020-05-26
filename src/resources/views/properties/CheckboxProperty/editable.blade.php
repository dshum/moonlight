<td class="editable" data-mode="view" data-name="{{ $name }}">
    <div class="view-container">
        @if ($value)
            <span>Да</span>
        @else
            <span class="grey">Нет</span>
        @endif
    </div>
    <div class="edit-container">
        <input type="hidden" name="editing[{{ $element->id }}][{{ $name }}]" value="{{ $value ? 1 : 0 }}" disabled>
        <div class="checkbox{{ $value ? ' checked' : '' }}" data-name="editing[{{ $element->id }}][{{ $name }}]"></div>
    </div>
</td>
