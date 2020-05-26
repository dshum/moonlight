<div class="confirm" data-confirm-type="delete" data-url="{{ route('moonlight.element.delete', $classId) }}">
    <div class="wrapper">
        <div class="container">
            <div class="content">
                Удалить элемент &laquo;{{ $element->$mainProperty }}&raquo;?
            </div>
            <div class="bottom">
                <input type="button" value="Удалить" class="btn remove">
                <input type="button" value="Отмена" class="btn cancel">
            </div>
        </div>
    </div>
</div>
