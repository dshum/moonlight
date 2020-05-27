<div><label>{{ $title }}:</label><span data-name="{{ $name }}" class="error"></span></div>
@if ($readonly)
    <div class="richtext">{!! $value ? $value : '<span class="grey">Не определено</span>' !!}</div>
@else
    <textarea name="{{ $name }}" data-tinymce="true" data-toolbar="{{ $toolbar }}" class="tinymce">{!! $value !!}</textarea>
@endif
