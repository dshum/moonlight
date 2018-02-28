<label>{{ $title }}:</label><span name="{{ $name }}" class="error"></span><br>
@if ($readonly)
<div class="richtext">{!! $value ? $value : '<span class="grey">Не определено</span>' !!}</div>
@else
<textarea name="{{ $name }}" tinymce="true">{!! $value !!}</textarea>
@endif