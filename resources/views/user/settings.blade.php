@extends('layouts.app')

@section('content')
<div class="modal inmodal fade" id="changePassword" tabindex="-1" role="dialog"  aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="frame">
                <div class="p-w-md">
                    <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                    <h2>Промяна на парола</h2>
                </div>
                <div class="modal-body">
                    <form class="m-t-lg">
                        <div class="form-group row required">
                            <label for="oldPass" class="col-sm-4 col-xs-12 col-form-label">Парола:</label>
                            <div class="col-sm-8">
                                <input type="password" class="input-border-r-12 form-control" id="oldPass">
                            </div>
                        </div>
                        <div class="form-group row required">
                            <label for="password" class="col-sm-4 col-xs-12 col-form-label">Нова парола:</label>
                            <div class="col-sm-8">
                                <input type="password" class="input-border-r-12 form-control" id="password">
                            </div>
                        </div>
                        <div class="form-group row required">
                            <label for="confPass" class="col-sm-4 col-xs-12 col-form-label">Потвърждение на паролата:</label>
                            <div class="col-sm-8">
                                <input type="password" class="input-border-r-12 form-control" id="confPass">
                            </div>
                        </div>

                        <div class="form-group row">
                            <div class="col-sm-12 text-right">
                                <button type="submit" class="m-l-md btn btn-custom">Запази</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal inmodal fade" id="generateAPIkey" tabindex="-1" role="dialog"  aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="frame">
                <div class="p-w-md">
                    <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                    <h2>Генериране на нов API ключ</h2>
                </div>
                <div class="modal-body">
                    <p class="p-sm text-center">
                        Желаете ли да генерирате нов API ключ
                        <a href="#" class="m-l-md btn btn-custom">Генерирай</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="container">
    <div class="row">
        <div class="col-xs-12 m-t-md">
            <div class="row">
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
                                            <li><a href="{{ url('/user/groups') }}">групи</a></li>
                                            <li><a href="{{ url('/user/organisations') }}">организации</a></li>
                                            <li><a class="active" href="{{ url('/user/settings') }}">настройки</a></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xs-12 m-t-lg">
                    <form class="m-t-lg p-sm">
                        <div class="form-group row">
                            <label for="fname" class="col-sm-3 col-xs-12 col-form-label">Име:</label>
                            <div class="col-sm-9">
                                <input type="text" class="input-border-r-12 form-control" id="fname" value="Иван">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="lname" class="col-sm-3 col-xs-12 col-form-label">Фамилия:</label>
                            <div class="col-sm-9">
                                <input type="text" class="input-border-r-12 form-control" id="lname" value="Иванов">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="email" class="col-sm-3 col-xs-12 col-form-label">e-mail:</label>
                            <div class="col-sm-9">
                                <input type="email" class="input-border-r-12 form-control" id="email" value="ivanov@abv.bg">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="username" class="col-sm-3 col-xs-12 col-form-label">Потребителско име:</label>
                            <div class="col-sm-9">
                                <input type="text" class="input-border-r-12 form-control" id="username" value="Иванов">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="password" class="col-sm-3 col-xs-12 col-form-label">Парола:</label>
                            <div class="col-sm-9 text-right">
                                <button
                                    type="button"
                                    class="btn btn-primary"
                                    data-toggle="modal"
                                    data-target="#changePassword"
                                >промяна</button>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="description" class="col-sm-3 col-xs-12 col-form-label">Повече информация:</label>
                            <div class="col-sm-9">
                                <textarea type="text" class="input-border-r-12 form-control" id="description" placeholder="Описание"></textarea>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="apiKey" class="col-sm-3 col-xs-12 col-form-label">API ключ:</label>
                            <div class="col-sm-9">
                                <input type="text" class="input-border-r-12 form-control" id="apiKey" value="a47ac75e-4239-4659-b179-4a4841fac738">
                                <button
                                    type="button"
                                    class="col-xs-12 btn btn-primary m-t-sm"
                                    data-toggle="modal"
                                    data-target="#generateAPIkey"
                                >Генериране на нов API ключ</button>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="newsLetter" class="col-sm-3 col-xs-12 col-form-label">Получаване на бюлетин:</label>
                            <div class="col-sm-4 col-xs-8">
                                <!-- size=" count($options" -->
                                <select class="input-border-r-12 form-control open-select"  id="newsLetter" size="5">
                                    <option>Не желая</option>
                                    <option>При публикуване</option>
                                    <option>Веднъж дневно</option>
                                    <option>Веднъж седмично</option>
                                    <option>Веднъж месечно</option>
                                </select>
                            </div>
                            <div class="col-sm-5 col-xs-4 text-right">
                                <button type="submit" class="btn btn-primary">готово</button>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="newsLetter" class="col-sm-3 col-xs-12 col-form-label">Език:</label>
                            <div class="col-sm-4 col-xs-8">
                                <!-- size=" count($options" -->
                                <select class="input-border-r-12 form-control open-select" size="5">
                                    <option>Български</option>
                                    <option>Английски</option>
                                    <option>..........</option>
                                    <option>..........</option>
                                    <option>..........</option>
                                </select>
                            </div>
                        </div>
                    </form>
                    <div class="form-group row p-h-lg">
                        <div class="col-xs-12">
                            <button
                                type="button"
                                class="col-xs-12 btn btn-primary"
                                onclick="return confirm('Изтриване на профила?');"
                                >Изтриване на профила</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
