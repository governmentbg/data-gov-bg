@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-lg-10 col-md-11 col-xs-12 col-lg-offset-2 col-md-offset-1 m-t-md">
            <div class="row">
                <div class="col-xs-12">
                    <div>
                        <h2>Регистрация на потребител</h2>
                        <p class='req-fields m-t-lg m-b-lg'>Всички полета маркирани с * са задължителни.</p>
                    </div>
                    <form class="m-t-lg p-sm">
                        <div class="form-group row required">
                            <label for="fname" class="col-sm-3 col-xs-12 col-form-label">Име:</label>
                            <div class="col-sm-9">
                                <input type="text" class="input-border-r-12 form-control" id="fname" placeholder="Иван">
                            </div>
                        </div>
                        <div class="form-group row required">
                            <label for="lname" class="col-sm-3 col-xs-12 col-form-label">Фамилия:</label>
                            <div class="col-sm-9">
                                <input type="text" class="input-border-r-12 form-control" id="lname" placeholder="Иванов">
                            </div>
                        </div>
                        <div class="form-group row required">
                            <label for="email" class="col-sm-3 col-xs-12 col-form-label">e-mail:</label>
                            <div class="col-sm-9">
                                <input type="email" class="input-border-r-12 form-control" id="email" placeholder="ivanov@abv.bg">
                            </div>
                        </div>
                        <div class="form-group row required">
                            <label for="username" class="col-sm-3 col-xs-12 col-form-label">Потребителско име:</label>
                            <div class="col-sm-9">
                                <input type="text" class="input-border-r-12 form-control" id="username" placeholder="Иванов">
                            </div>
                        </div>
                        <div class="form-group row required">
                            <label for="password" class="col-sm-3 col-xs-12 col-form-label">Парола:</label>
                            <div class="col-sm-9">
                                <input type="password" class="input-border-r-12 form-control" id="password">
                            </div>
                        </div>
                        <div class="form-group row required">
                            <label for="password-confirm" class="col-sm-3 col-xs-12 col-form-label">Потвърждение на паролата:</label>
                            <div class="col-sm-9">
                                <input type="password" class="input-border-r-12 form-control" id="password-confirm">
                            </div>
                        </div>
                        <div class="form-group row required">
                            <label for="description" class="col-sm-3 col-xs-12 col-form-label">Повече информация:</label>
                            <div class="col-sm-9">
                                <textarea type="text" class="input-border-r-12 form-control" id="description" placeholder=""></textarea>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="newsLetter" class="col-sm-3 col-xs-12 col-form-label">Получаване на бюлетин:</label>
                            <div class="col-sm-3 col-xs-6 p-r-none">
                                <!-- size=" count($options" -->
                                <select class="input-border-r-12 form-control open-select"  id="newsLetter" size="5">
                                    <option>Не желая</option>
                                    <option>При публикуване</option>
                                    <option>Веднъж дневно</option>
                                    <option>Веднъж седмично</option>
                                    <option>Веднъж месечно</option>
                                </select>
                            </div>
                            <div class="col-sm-6 col-xs-6 text-right p-l-none reg-btns">
                                <a href="{{ url('/user/orgRegistration') }}" type="button" class="btn btn-primary m-b-sm add-org">добави организация</a>
                                <button type="submit" class="m-l-md btn btn-primary m-b-sm">готово</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
