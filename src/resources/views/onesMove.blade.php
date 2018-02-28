@if (sizeof($ones))
@foreach ($ones as $one)
    @if ($view = $one->getMoveView())
    <div id="{{ $one->getName() }}_one_container" property="{{ $one->getName() }}" class="row">{!! $view !!}</div>
    @endif
@endforeach
@else
Перенести элемент?
@endif