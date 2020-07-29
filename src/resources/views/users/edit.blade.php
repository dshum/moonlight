@extends('moonlight::layouts.admin')

@section('title', $user ? $user->login : 'Новый пользователь')

@push('scripts')
    <script src="{{ asset('packages/moonlight/js/users.min.js') }}"></script>
@endpush

@section('body')
    <div class="main">
        <div class="container">
            <div class="path">
                <div class="part"><a href="{{ route('moonlight.users.index') }}">Пользователи</a></div>
                <div class="divider">/</div>
                <div class="part"><span>{{ $user ? $user->login : 'Новый пользователь' }}</span></div>
            </div>
            <form data-save="true"
                  action="{{ $user ? route('moonlight.users.update', [$user->id, '_method' => 'PUT']) : route('moonlight.users.store') }}"
                  method="POST" autocomplete="off">
                <div class="edit">
                    <div class="row">
                        <label>Логин:</label><span data-name="login" class="error"></span><br>
                        <input type="text" name="login" value="{{ $user ? $user->login : '' }}" placeholder="Логин">
                    </div>
                    <div class="row">
                        <label>Имя:</label><span data-name="first_name" class="error"></span><br>
                        <input type="text" name="first_name" value="{{ $user ? $user->first_name : '' }}" placeholder="Имя">
                    </div>
                    <div class="row">
                        <label>Фамилия:</label><span data-name="last_name" class="error"></span><br>
                        <input type="text" name="last_name" value="{{ $user ? $user->last_name : '' }}" placeholder="Фамилия">
                    </div>
                    <div class="row">
                        <label>E-mail:</label><span data-name="email" class="error"></span><br>
                        @if (! $user)
                            <div><small class="red">На указанный адрес будет отправлено письмо<br> с параметрами доступа</small></div>
                        @endif
                        <input type="text" name="email" value="{{ $user ? $user->email : '' }}" placeholder="E-mail">
                    </div>
                    <div class="row">
                        <p>
                            <input type="checkbox" name="banned" id="banned" value="1"{{ $user && $user->banned ? ' checked' : '' }}>
                            <label for="banned">Заблокирован</label>
                        </p>
                    </div>
                    <div class="row">
                        <label>Группы:</label><span data-name="groups" class="error"></span><br>
                        @foreach ($groups as $group)
                            <p>
                                <input type="checkbox" name="groups[]" id="group_{{ $group->id }}" value="{{ $group->id }}"{{ $user && $user->groups->contains($group->id) ? ' checked' : '' }}>
                                <label for="group_{{ $group->id }}">{{ $group->name }}</label>
                            </p>
                        @endforeach
                    </div>
                    @if ($user)
                        <div class="row">
                            @if ($user->isSuperUser())
                                <div><b>Суперпользователь</b></div>
                            @endif
                            @if ($user->created_at)
                                <div>Дата создания: {{$user->created_at->format('d.m.Y')}}
                                    <small>{{$user->created_at->format('H:i:s')}}</small>
                                </div>
                            @endif
                            @if ($user->last_login)
                                <div>Последний логин: {{$user->last_login->format('d.m.Y')}}
                                    <small>{{$user->last_login->format('H:i:s')}}</small>
                                </div>
                            @endif
                        </div>
                    @endif
                    <div class="row submit">
                        <input type="submit" value="Сохранить" class="btn">
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('sidebar')
    @include('moonlight::components.sidebar.admin', ['active' => 'users'])
@endsection
