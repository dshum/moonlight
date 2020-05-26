<div class="confirm" data-confirm-type="delete" data-url="{{ $mode == 'trash' ? route('moonlight.elements.delete.force') : route('moonlight.elements.delete') }}">
    <div class="wrapper">
        <div class="container">
            <div class="content">
                Удалить выбранные элементы?
            </div>
            <div class="bottom">
                <input type="button" value="Удалить" class="btn remove">
                <input type="button" value="Отмена" class="btn cancel">
            </div>
        </div>
    </div>
</div>
