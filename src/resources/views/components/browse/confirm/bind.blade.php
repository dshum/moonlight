<div class="confirm" data-confirm-type="bind" data-url="{{ route('moonlight.elements.bind') }}">
    <div class="wrapper">
        <div class="container">
            <div class="content">
                <div>Выберите элемент,<br>который вы хотите привязать:</div>
                <div class="edit">
                    @foreach ($bindPropertyViews as $bindPropertyView)
                        <div class="row">
                            {!! $bindPropertyView !!}
                        </div>
                    @endforeach
                </div>
            </div>
            <div class="bottom">
                <input type="button" value="Привязать" class="btn bind">
                <input type="button" value="Отмена" class="btn cancel">
            </div>
        </div>
    </div>
</div>
