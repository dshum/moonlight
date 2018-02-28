<label>{{ $title }}:</label><span name="{{ $name }}" class="error"></span><br>
@if ($readonly)
<input type="text" name="{{ $name }}" value="{{ $value }}" placeholder="{{ $title }}" readonly>
@else
<input type="text" name="{{ $name }}" value="{{ $value }}" placeholder="{{ $title }}">
@endif