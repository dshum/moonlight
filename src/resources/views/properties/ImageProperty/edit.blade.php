<label>{{ $title }}:</label><span data-name="{{ $name }}" class="error"></span>
@if ($exists)
    <div class="grey">
        <a href="{{ $src }}" target="_blank">{{ $filename }}</a>,
        @if ($width && $height)
            <span title="Размер изображения">{{ $width }}&#215;{{ $height }}</span> пикселов,
        @endif
        {{ $filesize }} Кб
    </div>
    <div>
        <img src="{{ $src }}" alt="{{ $filename }}">
    </div>
@else
    <div class="grey">Не загружено</div>
@endif
@if (isset($resizes))
    @foreach ($resizes as $resizeName => $resize)
        @if ($resize['exists'])
            <div class="grey">
                <a href="{{ $resize['src'] }}" target="_blank">{{ $resize['filename'] }}</a>,
                @if ($resize['width'] && $resize['height'])
                    <span title="Размер изображения">{{ $resize['width'] }}&#215;{{ $resize['height'] }}</span> пикселов,
                @endif
                {{ $resize['filesize'] }} Кб
            </div>
            <div>
                <img src="{{ $resize['src'] }}" alt="{{ $resize['filename'] }}">
            </div>
        @endif
    @endforeach
@endif
@if (! $readonly)
    @foreach ($captions as $caption)
        <div><small class="caption">{{ $caption }}</small></div>
    @endforeach
    <div class="loadfile">
        <div class="file" data-name="{{ $name }}">Выберите файл</div>
        <span class="reset" data-name="{{ $name }}" data-file>&#215;</span>
        <div class="file-hidden"><input type="file" name="{{ $name }}"></div>
        <p>
            <input type="checkbox" name="{{ $name }}_drop" id="{{ $name }}_drop_checkbox" value="1">
            <label for="{{ $name }}_drop_checkbox">Удалить</label>
        </p>
    </div>
@endif
