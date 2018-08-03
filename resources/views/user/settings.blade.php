@extends('layouts.app')

@section('content')
<div class="container">
    <div class="col-xs-12 col-lg-10 m-t-md">
        <div class="row">
            <div class="flash-message">
                @foreach (['danger', 'warning', 'success', 'info'] as $msg)
                    @if(Session::has('alert-' . $msg))
                        <p class="alert alert-{{ $msg }}">
                            {{ Session::get('alert-' . $msg) }}
                            <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                        </p>
                    @endif
                @endforeach
            </div>
            <div class="col-sm-3 col-xs-12 sidenav">
                <span class="my-profile m-b-lg m-l-sm">Моят профил</span>
            </div>
            <div class="col-sm-9 col-xs-12">
                <div class="filter-content">
                    <div class="col-md-12">
                        <div class="row">
                            <div class="col-sm-12">
                                <div>
                                    <ul class="nav filter-type right-border">
                                        <li><a class="p-l-none" href="{{ url('/user') }}">известия</a></li>
                                        <li><a href="{{ url('/user/datasets') }}">моите данни</a></li>
                                        <li><a href="{{ url('/user/userGroups') }}">групи</a></li>
                                        <li><a href="{{ url('/user/organisations') }}">организации</a></li>
                                        <li><a class="active" href="{{ url('/user/settings') }}">настройки</a></li>
                                        <li><a href="{{ url('/user/invite') }}">покана</a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xs-12 m-t-lg">
                    <form class="m-t-lg p-sm" method="post">
                        {{ csrf_field() }}
                        <div class="form-group row">
                            <label for="fname" class="col-sm-3 col-xs-12 col-form-label">Име:</label>
                            <div class="col-sm-9">
                                <input
                                    type="text"
                                    class="input-border-r-12 form-control"
                                    name="firstname"
                                    id="fname"
                                    value="{{ $user['firstname'] }}"
                                >
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="lname" class="col-sm-3 col-xs-12 col-form-label">Фамилия:</label>
                            <div class="col-sm-9">
                                <input
                                    type="text"
                                    class="input-border-r-12 form-control"
                                    name="lastname"
                                    id="lname"
                                    value="{{ $user['lastname'] }}"
                                >
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="email" class="col-sm-3 col-xs-12 col-form-label">e-mail:</label>
                            <div class="col-sm-9">
                                <input
                                    type="email"
                                    class="input-border-r-12 form-control"
                                    name="email"
                                    id="email"
                                    value="{{ $user['email'] }}"
                                >
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="username" class="col-sm-3 col-xs-12 col-form-label">Потребителско име:</label>
                            <div class="col-sm-9">
                                <input
                                    type="text"
                                    class="input-border-r-12 form-control"
                                    id="username"
                                    name="username"
                                    value="{{ $user['username'] }}"
                                >
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="password" class="col-sm-3 col-xs-12 col-form-label">Парола:</label>
                            <div class="col-sm-9 text-right">
                                <button
                                    type="button"
                                    class="btn btn-primary"
                                    data-toggle="modal"
                                    data-target="#change-password"
                                >промяна</button>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="description" class="col-sm-3 col-xs-12 col-form-label">Повече информация:</label>
                            <div class="col-sm-9">
                                <textarea
                                    type="text"
                                    class="input-border-r-12 form-control"
                                    name="add_info"
                                    id="description"
                                >{{ $user['add_info'] }}</textarea>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="apiKey" class="col-sm-3 col-xs-12 col-form-label">API ключ:</label>
                            <div class="col-sm-9">
                                <input type="text" class="input-border-r-12 form-control" id="apiKey" value="{{ $user['api_key'] }}" disabled>
                                <button
                                    type="button"
                                    class="col-xs-12 btn btn-primary m-t-sm"
                                    data-toggle="modal"
                                    data-target="#generateAPIkey"
                                >Генериране на нов API ключ</button>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="newsletter" class="col-sm-3 col-xs-12 col-form-label">Получаване на бюлетин:</label>
                            <div class="col-sm-4 col-xs-8">
                                <select class="input-border-r-12 form-control open-select" name="newsletter" id="newsletter" size="{{ count($digestFreq) }}">
                                    @foreach ($digestFreq as $id => $freq)
                                        <option
                                            value="{{ $id }}"
                                            {{
                                                $user['userSetting']['newsletter_digest'] == $id
                                                    ? 'selected'
                                                    : ''
                                            }}
                                        >{{ $freq }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-sm-5 col-xs-4 text-right">
                                <button type="submit" name="save" class="btn btn-primary">готово</button>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="locales" class="col-sm-3 col-xs-12 col-form-label">Език:</label>
                            <div class="col-sm-4 col-xs-8">
                                <select class="input-border-r-12 form-control open-select" name="locale" id="locales" size="{{ count($localeList) }}">
                                    @foreach($localeList as $locale)
                                        <option
                                            value="{{ $locale->locale }}"
                                            {{
                                                $user['userSetting']['locale'] == $locale->locale
                                                    ? 'selected'
                                                    : ''
                                            }}
                                        >{{ $locale->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </form>
                    <div class="form-group row p-h-lg">
                        <div class="col-xs-12">
                            <button
                                type="button"
                                class="col-xs-12 btn btn-primary"
                                data-toggle="modal"
                                data-target="#delete-confirm"
                                >Изтриване на профила</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal inmodal fade" id="change-password" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="frame">
                <div class="p-w-md">
                    <button
                        type="button"
                        class="close"
                        data-dismiss="modal"
                    >
                        <span aria-hidden="true">&times;</span>
                        <span class="sr-only">Close</span>
                    </button>
                    <h2>Промяна на парола</h2>
                </div>
                <div class="modal-body">
                    <form class="m-t-lg" method="post">
                        {{ csrf_field() }}
                        <div class="form-group row required">
                            <label for="oldPass" class="col-sm-4 col-xs-12 col-form-label">Парола:</label>
                            <div class="col-sm-8">
                                <input
                                    type="password"
                                    class="input-border-r-12 form-control"
                                    name="old_password"
                                    id="oldPass"
                                >
                            </div>
                        </div>
                        <div class="form-group row required">
                            <label for="password" class="col-sm-4 col-xs-12 col-form-label">Нова парола:</label>
                            <div class="col-sm-8">
                                <input
                                    type="password"
                                    class="input-border-r-12 form-control"
                                    name="password"
                                    id="password"
                                >
                            </div>
                        </div>
                        <div class="form-group row required">
                            <label for="confPass" class="col-sm-4 col-xs-12 col-form-label">Потвърждение на паролата:</label>
                            <div class="col-sm-8">
                                <input
                                    type="password"
                                    class="input-border-r-12 form-control"
                                    name="password_confirm"
                                    id="confPass"
                                >
                            </div>
                        </div>

                        <div class="form-group row">
                            <div class="col-sm-12 text-right">
                                <button type="submit" name="change_pass" class="m-l-md btn btn-custom">Запази</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal inmodal fade" id="delete-confirm" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="blue-highlight">
                <div class="modal-body">
                    <a type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></a>
                    <p>
                        Изтриването на акаунта, ще доведе до загуба на административния Ви достъп до качените от Вас набори от данни и ще бъде нужна наме от администратор на системата, ако желаете този достъп да бъде възстановен.
                        <p class="small-text">
                            Ако желаете да продължите натиснете бутона "Продължи".
                        </p>
                    </p>
                    <button
                        id="confirm"
                        type="submit"
                        name="continue"
                        class="m-l-md btn btn-custom pull-right conf"
                        data-toggle="modal"
                        data-target="#delete"
                    >Продължи</button>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal inmodal fade" id="delete" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="blue-highlight">
                <div class="modal-body text-center">
                    <form method="post">
                        {{ csrf_field() }}
                        <a type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></a>
                        <div class="usr-delete">
                            <p>
                                Изтриване на профила
                                <button
                                    type="submit"
                                    name="delete"
                                    class="m-l-md btn btn-custom pull-right"
                                >Изтрий</button>
                            </p>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal inmodal fade" id="generateAPIkey" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="frame">
                <form method="post">
                    {{ csrf_field() }}
                    <div class="p-w-md">
                        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                        <h2>Генериране на нов API ключ</h2>
                    </div>
                    <div class="modal-body">
                        <p class="p-sm text-center">
                            Желаете ли да генерирате нов API ключ
                            <button type="submit" name="generate_key" class="m-l-md btn btn-custom">Генерирай</button>
                        </p>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection
