@if ($readonly && $value)
    <div>
        <label>{{ $title }}:</label> {{ $value->format('d.m.Y') }}, {{ $value->format('H:i:s') }}
    </div>
@elseif ($readonly)
    <div>
        <label>{{ $title }}:</label> Не определено
    </div>
@else
    <input type="hidden" name="{{ $name }}_date" data-property="{{ $name }}" value="{{ $value ? $value->format('Y-m-d') : '' }}" class="datetime">
    <input type="hidden" name="{{ $name }}_time" data-property="{{ $name }}" value="{{ $value ? $value->format('H:i:s') : '' }}" class="time">
    <div>
        <label>{{ $title }}:</label>
        @if ($value)
            <span class="datetime-container" data-property="{{ $name }}">
                <span class="datepicker" data-property="{{ $name }}">{{ $value->format('d.m.Y') }}</span>,
                <span class="timepicker" data-property="{{ $name }}">{{ $value->format('H:i:s') }}</span>
            </span>
            <span data-name="{{ $name }}" class="error"></span>
        @else
            <span class="datepicker" data-property="{{ $name }}">Не определено</span>
            <span data-name="{{ $name }}" class="error"></span>
       @endif
        </div>
    <div class="timepicker-popup" data-property="{{ $name }}">
        <div class="block">
            <div class="title hours">Часы</div>
            <table class="hours">
                @for ($i = 0; $i < 6; $i++)
                    <tr>
                        @for ($j = 0; $j < 4; $j++)
                            <td value="{{ sprintf('%02d', $i * 4 + $j) }}" class="{{ $value && $value->format('H') == $i * 4 + $j ? 'active' : '' }}">{{ sprintf('%02d', $i * 4 + $j) }}</td>
                        @endfor
                    </tr>
                @endfor
            </table>
        </div>
        <div class="block">
            <div class="title minutes">Минуты</div>
            <table class="minutes">
                @for ($i = 0; $i < 6; $i++)
                    <tr>
                        @for ($j = 0; $j < 10; $j++)
                            <td data-value="{{ sprintf('%02d', $i * 10 + $j) }}" class="{{ $j % 5 ? 'add hide' : '' }} {{ $value && $value->format('i') == $i * 10 + $j ? 'active' : '' }}">{{ sprintf('%02d', $i * 10 + $j) }}</td>
                        @endfor
                    </tr>
                @endfor
            </table>
        </div>
        <div class="block">
            <div class="title seconds">Секунды</div>
            <table class="seconds">
                @for ($i = 0; $i < 6; $i++)
                    <tr>
                        @for ($j = 0; $j < 10; $j++)
                            <td data-value="{{ sprintf('%02d', $i * 10 + $j) }}" class="{{ $j % 5 ? 'add hide' : '' }} {{ $value && $value->format('s') == $i * 10 + $j ? 'active' : '' }}">{{ sprintf('%02d', $i * 10 + $j) }}</td>
                        @endfor
                    </tr>
                @endfor
            </table>
        </div>
    </div>
@endif
