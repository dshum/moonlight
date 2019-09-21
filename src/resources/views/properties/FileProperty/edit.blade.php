<label>{{ $title }}:</label><span name="{{ $name }}" class="error"></span>
@if ($exists)
    <div class="grey"><a href="{{ $path }}" target="_blank">{{ $filename }}</a>, {{ $filesize }} Кб<br/></div>
@else
    <div class="grey">Не загружено</div>
@endif
@if (! $readonly)
    @foreach ($captions as $caption)
        <div><small class="caption">{{ $caption }}</small></div>
    @endforeach
    <div class="loadfile">
        <div class="file" name="{{ $name }}">Выберите файл</div>
        <span class="reset" name="{{ $name }}" file>&#215;</span>
        <div class="file-hidden"><input type="file" name="{{ $name }}"></div>
        <p>
            <input type="checkbox" name="{{ $name }}_drop" id="{{ $name }}_drop_checkbox" value="1">
            <label for="{{ $name }}_drop_checkbox">Удалить</label>
        </p>
    </div>
@endif
