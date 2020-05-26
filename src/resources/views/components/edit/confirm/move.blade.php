<div class="confirm" data-confirm-type="move" data-url="{{ route('moonlight.element.move', $classId) }}">
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
                <input type="button" value="Перенести" class="btn move" url="{{ route('moonlight.element.move', $classId) }}">
                <input type="button" value="Отмена" class="btn cancel">
            </div>
        </div>
    </div>
</div>
