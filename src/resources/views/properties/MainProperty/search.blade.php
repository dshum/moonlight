<div class="label mainp"><i class="fa fa-flag"></i><span>ID или название</span><span class="addition unset" property="{{ $name }}">Очистить</span></div>
<input type="hidden" name="{{ $name }}" value="{{ $id }}">
<input type="text" class="one" item="{{ $relatedClass }}" property="{{ $name }}" name="{{ $name }}_autocomplete" value="{{ $text }}" placeholder="ID или название"><br>