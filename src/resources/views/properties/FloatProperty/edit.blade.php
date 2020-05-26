<div><label>{{ $title }}:</label><span data-name="{{ $name }}" class="error"></span></div>
@if ($readonly)
    <input type="text" name="{{ $name }}" value="{{ $value }}" class="number" placeholder="{{ $title }}" readonly>
@else
    <input type="text" name="{{ $name }}" value="{{ $value }}" class="number" placeholder="{{ $title }}">
@endif
