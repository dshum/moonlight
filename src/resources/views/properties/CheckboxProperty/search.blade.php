<div class="label checkbox"><i class="fa fa-check-square"></i><span>{{ $title }}</span></div>
<div>
    <select>
        <option value="">Не важно</option>
        <option value="true"{{ $value === 'true' ? ' selected' : '' }}>Да</option>
        <option value="false"{{ $value === 'false' ? ' selected' : '' }}>Нет</option>
    </select>
</div>