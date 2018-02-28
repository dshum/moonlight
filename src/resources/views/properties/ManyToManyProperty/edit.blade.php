@if ($readonly)
    <label>{{ $title }}:</label><br>
    @foreach ($elements as $element)
    <div><a href="{{ route('moonlight.element.edit', $element['classId']) }}">{{ $element['name'] }}</a></div>
    @endforeach
@else
    <label>{{ $title }}:</label>
    <span name="{{ $name }}" container></span>
    <span name="{{ $name }}" class="error"></span><br>
    <input type="text" class="many" item="{{ $relatedClass }}" property="{{ $name }}" name="{{ $name }}_autocomplete" value="" placeholder="ID или название">
    <span class="addition unset" property="{{ $name }}">Очистить</span>
    <span class="addition add" property="{{ $name }}">Добавить</span>
    <div class="many elements" name="{{ $name }}">
        @foreach ($elements as $element)
        <p><input type="checkbox" name="{{ $name }}[]" id="{{ $element['classId'] }}" checked value="{{ $element['id'] }}"><label for="{{ $element['classId'] }}">{{ $element['name'] }}</label></p>
        @endforeach
    </div>
@endif