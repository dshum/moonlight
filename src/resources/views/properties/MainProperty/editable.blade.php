<td class="name editable" mode="view">
    <div  class="view-container">
        <a href="{{ route('moonlight.element.edit', $classId) }}"><i class="fa fa-pencil"></i><span>{{ $value }}</span></a>
    </div>
    <div class="edit-container">
        <input type="text" name="editing[{{ $element->id }}][{{ $name }}]" value="{{ $value }}" placeholder="{{ $title }}" disabled>
    </div>
</td>