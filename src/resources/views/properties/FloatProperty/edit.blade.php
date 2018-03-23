<label>{{ $title }}:</label><span name="{{ $name }}" class="error"></span><br>
@if ($readonly)
<input type="text" name="{{ $name }}" value="{{ $value }}" class="number" placeholder="{{ $title }}" readonly>
@else
<input type="text" name="{{ $name }}" value="{{ $value }}" class="number" placeholder="{{ $title }}">
@endif