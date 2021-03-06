@if (sizeof($userActions))
    @if ($currentPage == 1)
        <div class="result">Всего {{ $total }} {{ Moonlight\Utils\RussianText::selectCaseForNumber($total, ['операция', 'операции', 'операций']) }}. Отсортировано по дате.</div>
    @endif
    <div class="leaf">
        @if ($currentPage > 1)
            <div class="page">Страница {{ $currentPage }}</div>
        @endif
        <ul class="log">
            @foreach ($userActions as $userAction)
                <li>
                    @if ($userAction->user && $userAction->user->photoExists())
                        <div class="avatar"><img src="{{ $userAction->user->getPhotoSrc() }}"/></div>
                    @elseif ($userAction->user)
                        <div class="avatar">
                            <div class="round-letter" style="background-color: {{ $userAction->user->hex_color }}">{{ $userAction->user->initials }}</div>
                        </div>
                    @else
                        <div class="avatar">
                            <div class="round-letter">?</div>
                        </div>
                    @endif
                    <div class="date">{{ $userAction->created_at->format('d.m.Y') }}<br>
                        <span class="time">{{ $userAction->created_at->format('H:i:s') }}</span></div>
                    @if ($userAction->user)
                        <span class="user">{{ $userAction->user->login }}</span>
                        <small>{{ $userAction->user->first_name }} {{ $userAction->user->last_name }}</small><br>
                    @else
                        <span class="user">User.{{ $userAction->user_id }}</span>
                        <small>Удаленный пользователь</small><br>
                    @endif
                    <span class="title">{{ $userAction->getActionTypeName() }}</span>
                    <span>{{ $userAction->comments }}</span>
                </li>
            @endforeach
        </ul>
    </div>
    @if ($hasMorePages)
        <div class="next" page="{{ $currentPage + 1 }}">
            Дальше<i class="fa fa-arrow-right"></i>
        </div>
    @endif
@else
    <div class="empty">Операций не найдено.</div>
@endif
