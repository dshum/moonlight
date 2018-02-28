@if (sizeof($ones))
@foreach ($ones as $one)
    @if ($view = $one->getCopyView())
    <div id="{{ $one->getName() }}_one_container" property="{{ $one->getName() }}" class="row">{!! $view !!}</div>
    @endif
@endforeach
@else
Копировать элемент?
@endif