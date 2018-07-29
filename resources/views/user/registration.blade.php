@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-lg-10 col-md-11 col-xs-12 col-lg-offset-1 m-t-md">
            <div class="row">
                <div class="col-xs-12">
                    <div>
                        <h2>Регистрация на потребител</h2>
                        <p class='req-fields m-t-lg m-b-lg'>Всички полета маркирани с * са задължителни.</p>
                    </div>
                    <form method="POST" class="m-t-lg p-sm">
                        {{ csrf_field() }}

                        <div class="form-group row required">
                            <label for="fname" class="col-sm-3 col-xs-12 col-form-label">Име:</label>
                            <div class="col-sm-9">
                                <input type="text" class="input-border-r-12 form-control" name="firstname" placeholder="Иван">
                                @if (!empty($error->firstname))
                                    <span class="error">{{ $error->firstname[0] }}</span>
                                @endif
                            </div>
                        </div>
                        <div class="form-group row required">
                            <label for="lname" class="col-sm-3 col-xs-12 col-form-label">Фамилия:</label>
                            <div class="col-sm-9">
                                <input type="text" class="input-border-r-12 form-control" name="lastname" placeholder="Иванов">
                                @if (!empty($error->lastname))
                                    <span class="error">{{ $error->lastname[0] }}</span>
                                @endif
                            </div>
                        </div>
                        <div class="form-group row required">
                            <label for="email" class="col-sm-3 col-xs-12 col-form-label">e-mail:</label>
                            <div class="col-sm-9">
                                <input type="email" class="input-border-r-12 form-control" name="email" placeholder="ivanov@abv.bg">
                                @if (!empty($error->email))
                                    <span class="error">{{ $error->email[0] }}</span>
                                @endif
                            </div>
                        </div>
                        <div class="form-group row required">
                            <label for="username" class="col-sm-3 col-xs-12 col-form-label">Потребителско име:</label>
                            <div class="col-sm-9">
                                <input type="text" class="input-border-r-12 form-control" name="username" placeholder="Иванов">
                                @if (!empty($error->username))
                                    <span class="error">{{ $error->username[0] }}</span>
                                @endif
                            </div>
                        </div>
                        <div class="form-group row required">
                            <label for="password" class="col-sm-3 col-xs-12 col-form-label">Парола:</label>
                            <div class="col-sm-9">
                                <input type="password" class="input-border-r-12 form-control" name="password">
                                @if (!empty($error->password))
                                    <span class="error">{{ $error->password[0] }}</span>
                                @endif
                            </div>
                        </div>
                        <div class="form-group row required">
                            <label for="password-confirm" class="col-sm-3 col-xs-12 col-form-label">Потвърждение на паролата:</label>
                            <div class="col-sm-9">
                                <input type="password" class="input-border-r-12 form-control" name="password_confirm">
                                @if (!empty($error->password_confirm))
                                    <span class="error">{{ $error->password_confirm[0] }}</span>
                                @endif
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="description" class="col-sm-3 col-xs-12 col-form-label">Повече информация:</label>
                            <div class="col-sm-9">
                                <textarea type="text" class="input-border-r-12 form-control" name="description" placeholder=""></textarea>
                                @if (!empty($error->description))
                                    <span class="error">{{ $error->description[0] }}</span>
                                @endif
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="newsLetter" class="col-sm-3 col-xs-12 col-form-label">Получаване на бюлетин:</label>
                            <div class="col-sm-3 col-xs-6 p-r-none">
                                <select class="input-border-r-12 form-control open-select" name="user_settings[newsletter_digest]" size="5">
                                    @foreach ($digestFreq as $id => $freq)
                                        <option value="{{ $id }}">{{ $freq }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-sm-6 col-xs-6 text-right p-l-none reg-btns">
                                <button
                                    class="btn btn-primary m-b-sm add-org"
                                    name="add_org"
                                >Добави организация</button>
                                <button
                                    type="submit"
                                    class="m-l-md btn btn-primary m-b-sm"
                                >Готово</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
