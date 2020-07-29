<div class="sidebar">
    <div class="container">
        <ul class="menu">
            <li class="{{ $active == 'groups' ? 'active' : null }}"><a href="{{ route('moonlight.groups.index') }}"><i class="fa fa-folder-open"></i>Группы</a></li>
            <li class="{{ $active == 'users' ? 'active' : null }}"><a href="{{ route('moonlight.users.index') }}"><i class="fa fa-user"></i>Пользователи</a></li>
            <li class="{{ $active == 'log' ? 'active' : null }}"><a href="{{ route('moonlight.log') }}"><i class="fa fa-clock-o"></i>Журнал</a></li>
            <li class="{{ $active == 'profile' ? 'active' : null }}"><a href="{{ route('moonlight.profile') }}"><i class="fa fa-pencil"></i>Редактировать профиль</a></li>
            <li class="{{ $active == 'password' ? 'active' : null }}"><a href="{{ route('moonlight.password') }}"><i class="fa fa-lock"></i>Сменить пароль</a></li>
            <li><a href="{{ route('moonlight.logout') }}"><i class="fa fa-sign-out"></i>Выход</a></li>
        </ul>
    </div>
</div>
