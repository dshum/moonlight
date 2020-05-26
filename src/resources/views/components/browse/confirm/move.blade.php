<div class="confirm" data-confirm-type="move" data-url="{{ route('moonlight.elements.move') }}">
    <div class="wrapper">
        <div class="container">
            <div class="content">
                <div>Куда переносим?</div>
                <div class="edit">
                    <div class="row">
                        {!! $movePropertyView !!}
                    </div>
                </div>
            </div>
            <div class="bottom">
                <input type="button" value="Перенести" class="btn move">
                <input type="button" value="Отмена" class="btn cancel">
            </div>
        </div>
    </div>
</div>
