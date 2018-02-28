<label>{{ $title }}:</label>
@if ($readonly && $value)
{{ $value->format('H:i:s') }}
@elseif ($readonly)
<span class="grey">Не определено</span>
@else
<script>
$(function() {
    $('span[name="{{ $name }}"][reset]').click(function() { 
        $(':text[name="{{ $name }}_hour"]').val('');
        $(':text[name="{{ $name }}_minute"]').val('');
        $(':text[name="{{ $name }}_second"]').val('');
    });
});
</script>
<input type="text" name="{{ $name }}_hour" class="time" value="{{ $value ? $value->format('H') : null }}"> : 
<input type="text" name="{{ $name }}_minute" class="time" value="{{ $value ? $value->format('i') : null }}"> : 
<input type="text" name="{{ $name }}_second" class="time" value="{{ $value ? $value->format('s') : null }}">
<span class="reset" name="{{ $name }}" reset>&#215;</span>
@endif