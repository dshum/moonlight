<div class="confirm" data-confirm-type="copy" data-url="{{ route('moonlight.elements.copy') }}">
    <div class="wrapper">
        <div class="container">
            <div class="content">
                <div>Куда копируем?</div>
                <div class="edit">
                    <div class="row">
                        {!! $copyPropertyView !!}
                    </div>
                </div>
            </div>
            <div class="bottom">
                <input type="button" value="Скопировать" class="btn copy">
                <input type="button" value="Отмена" class="btn cancel">
            </div>
        </div>
    </div>
</div>
