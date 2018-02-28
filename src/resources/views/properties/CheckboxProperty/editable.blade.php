<td class="editable" mode="view" name="{{ $name }}">
    <div  class="view-container">
        @if ($value)Да@else<span class="grey">Нет</span>@endif
    </div>
    <div class="edit-container">
        <input type="hidden" name="editing[{{ $element->id }}][{{ $name }}]" value="{{ $value ? 1 : 0 }}" disabled>
        <div class="checkbox{{ $value ? ' checked' : '' }}" name="editing[{{ $element->id }}][{{ $name }}]"></div>
    </div>
</td>