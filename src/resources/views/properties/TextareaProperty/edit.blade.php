<div><label>{{ $title }}:</label><span data-name="{{ $name }}" class="error"></span></div>
@if ($readonly)
    <textarea name="{{ $name }}" placeholder="{{ $title }}" rows="10" readonly>{!! $value !!}</textarea>
@else
    <textarea name="{{ $name }}" placeholder="{{ $title }}" rows="10">{!! $value !!}</textarea>
@endif
