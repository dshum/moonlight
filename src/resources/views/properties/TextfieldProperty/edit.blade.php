<div><label>{{ $title }}:</label><span data-name="{{ $name }}" class="error"></span></div>
@if ($readonly)
    <input type="text" name="{{ $name }}" value="{{ $value }}" placeholder="{{ $title }}" readonly>
@else
    <input type="text" name="{{ $name }}" value="{{ $value }}" placeholder="{{ $title }}">
@endif
