<label>{{ $title }}:</label><br>
@if ($readonly)
<input type="text" name="{{ $name }}" value="{{ $value }}" class="number" placeholder="{{ $title }}" readonly>
@else
<input type="text" name="{{ $name }}" value="{{ $value }}" class="number" placeholder="{{ $title }}">
@endif