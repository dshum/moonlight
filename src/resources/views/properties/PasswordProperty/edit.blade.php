<label>{{ $title }}:</label><br>
@if ($readonly)
<input type="password" name="{{ $name }}" placeholder="{{ $title }}" readonly>
@else
<input type="password" name="{{ $name }}" placeholder="{{ $title }}">
@endif