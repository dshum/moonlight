<div class="label select"><i class="fa fa-align-left"></i><span>{{ $title }}</span></div>
<div>
    <select name="{{ $name }}" value="{{ $value }}">
        @if ($value === null)
            <option value="" selected>Не важно</option>
        @else
            <option value="">Не важно</option>
        @endif
        @foreach ($list as $key => $title)
            @if ($value !== null && $key == $value)
                <option value="{{ $key }}" selected>{{ $title }}</option>
            @else
                <option value="{{ $key }}">{{ $title }}</option>
            @endif
        @endforeach
    </select>
</div>
