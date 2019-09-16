<label>{{ $title }}:</label><span name="{{ $name }}" class="error"></span><br>
@if ($exists)
    <a href="{{ $path }}" target="_blank">{{ $filename }}</a> <small>{{ $filesize }} Кб<br/></small>
@else
    <small>Не загружено</small>
@endif
@if (! $readonly)
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
