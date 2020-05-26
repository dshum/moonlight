<div class="confirm" data-confirm-type="favorite" data-url="{{ route('moonlight.elements.favorite') }}">
    <div class="wrapper">
        <div class="container">
            @if (sizeof($favoriteRubrics))
                <div class="favorite-settings" title="Настроить избранное">
                    <a href="{{ route('moonlight.favorites.edit') }}"><i class="fa fa-cog"></i></a>
                </div>
            @endif
            <div class="content">
                @if (sizeof($favoriteRubrics))
                    <div class="favorite-title add hidden">Добавить в рубрику:</div>
                    <div class="favorite-list add hidden">
                        @foreach ($favoriteRubrics as $favoriteRubric)
                            <div data-rubric="{{ $favoriteRubric->id }}" class="rubric hidden">{{ $favoriteRubric->name }}</div>
                        @endforeach
                    </div>
                    <div class="favorite-title remove hidden">Убрать из рубрики:</div>
                    <div class="favorite-list remove hidden">
                        @foreach ($favoriteRubrics as $favoriteRubric)
                            <div data-rubric="{{ $favoriteRubric->id }}" class="rubric hidden">{{ $favoriteRubric->name }}</div>
                        @endforeach
                    </div>
                @endif
                <div class="favorite-new">
                    <input type="text" name="favorite_rubric_new" value="" placeholder="Новая рубрика">
                </div>
            </div>
            <div class="bottom">
                <input type="button" value="Добавить" class="btn favorite">
                <input type="button" value="Отмена" class="btn cancel">
            </div>
        </div>
    </div>
</div>
