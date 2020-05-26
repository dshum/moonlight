<div><label>{{ $title }}:</label><span data-name="{{ $name }}" class="error"></span></div>
@if ($readonly)
    <div class="richtext">{!! $value ? $value : '<span class="grey">Не определено</span>' !!}</div>
@else
    <div style="width: 60rem; height: 30rem;">
        <textarea name="{{ $name }}" data-codemirror="true">{!! $value !!}</textarea>
    </div>
@endif
