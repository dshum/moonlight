<div class="confirm" data-confirm-type="unbind" data-url="{{ route('moonlight.elements.unbind') }}">
    <div class="wrapper">
        <div class="container">
            <div class="content">
                <div>Выберите элемент,<br>который вы хотите отвязать:</div>
                <div class="edit">
                    @foreach ($unbindPropertyViews as $unbindPropertyView)
                        <div class="row">
                            {!! $unbindPropertyView !!}
                        </div>
                    @endforeach
                </div>
            </div>
            <div class="bottom">
                <input type="button" value="Отвязать" class="btn unbind">
                <input type="button" value="Отмена" class="btn cancel">
            </div>
        </div>
    </div>
</div>
