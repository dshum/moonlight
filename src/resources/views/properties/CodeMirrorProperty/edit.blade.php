<label>{{ $title }}:</label><span name="{{ $name }}" class="error"></span><br>
@if ($readonly)
    <div class="richtext">{!! $value ? $value : '<span class="grey">Не определено</span>' !!}</div>
@else
    <div style="width: 60rem; height: 30rem;">
        <textarea name="{{ $name }}" codemirror="true">{!! $value !!}</textarea>
    </div>
@endif
