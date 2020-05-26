<div class="confirm" data-confirm-type="favorite" data-url="{{ route('moonlight.element.favorite', $classId) }}">
    <div class="wrapper">
        <div class="container">
            @if (sizeof($favoriteRubrics))
                <div class="favorite-settings" title="Настроить избранное">
                    <a href="{{ route('moonlight.favorites.edit') }}"><i class="fa fa-cog"></i></a>
                </div>
            @endif
            <div class="content">
                <div class="favorite-title add {{ sizeof($elementFavoriteRubrics) < sizeof($favoriteRubrics) ? '' : 'hidden' }}">Добавить в рубрику:</div>
                <div class="favorite-list add {{ sizeof($elementFavoriteRubrics) < sizeof($favoriteRubrics) ? '' : 'hidden' }}">
                    @foreach ($favoriteRubrics as $favoriteRubric)
                        @if (isset($elementFavoriteRubrics[$favoriteRubric->id]))
                            <div data-rubric="{{ $favoriteRubric->id }}" class="rubric hidden">{{ $favoriteRubric->name }}</div>
                        @else
                            <div data-rubric="{{ $favoriteRubric->id }}" class="rubric">{{ $favoriteRubric->name }}</div>
                        @endif
                    @endforeach
                </div>
                <div class="favorite-title remove {{ sizeof($elementFavoriteRubrics) ? '' : 'hidden' }}">Убрать из рубрики:</div>
                <div class="favorite-list remove {{ sizeof($elementFavoriteRubrics) ? '' : 'hidden' }}">
                    @foreach ($favoriteRubrics as $favoriteRubric)
                        @if (isset($elementFavoriteRubrics[$favoriteRubric->id]))
                            <div data-rubric="{{ $favoriteRubric->id }}" class="rubric">{{ $favoriteRubric->name }}</div>
                        @else
                            <div data-rubric="{{ $favoriteRubric->id }}" class="rubric hidden">{{ $favoriteRubric->name }}</div>
                        @endif
                    @endforeach
                </div>
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
