<label>{{ $title }}:</label><span name="{{ $name }}" class="error"></span><br>
@if ($exists)
    <small>
        <a href="{{ $src }}" target="_blank">{{ $filename }}</a>,
        <span title="Размер изображения">{{ $width }}&#215;{{ $height }}</span> пикселов, {{ $filesize }} Кб<br/>
    </small>
    <img src="{{ $src }}" alt="{{ $filename }}"><br/>
@else
    <small>Не загружено</small>
@endif
@if (isset($resizes))
    @foreach ($resizes as $resizeName => $resize)
        @if ($resize['exists'])
            <small><a href="{{ $resize['src'] }}" target="_blank">{{ $resize['filename'] }}</a>,
                <span title="Размер изображения">{{ $resize['width'] }}&#215;{{ $resize['height'] }}</span> пикселов, {{ $resize['filesize'] }} Кб<br/></small>
            <img src="{{ $resize['src'] }}" alt="{{ $resize['filename'] }}"><br/>
        @endif
    @endforeach
@endif
@if (! $readonly)
    @foreach ($messages as $message)
        <small class="red">{{ $message }}</small><br>
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
