<label>{{ $title }}:</label><br>
@if ($readonly)
    <input type="password" name="{{ $name }}" placeholder="{{ $title }}" autocomplete="suggest-password" readonly>
@else
    <input type="password" name="{{ $name }}" placeholder="{{ $title }}" autocomplete="suggest-password">
@endif
