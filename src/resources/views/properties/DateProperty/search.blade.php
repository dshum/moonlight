<div class="label date"><i class="fa fa-calendar"></i><span>{{ $title }}</span></div>
<div>
    <input type="text" name="{{ $name }}_from" value="{{ $from ? $from->format('Y-m-d') : '' }}" class="date" placeholder="От">
    <input type="text" name="{{ $name }}_to" value="{{ $to ? $to->format('Y-m-d') : '' }}" class="date" placeholder="До">
</div>