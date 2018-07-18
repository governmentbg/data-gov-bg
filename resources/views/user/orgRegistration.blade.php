@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-lg-10 col-md-11 col-xs-12 col-lg-offset-2 col-md-offset-1 m-t-md p-l-r-none">
            <div class="row">
                <div class="col-xs-12">
                    <div>
                        <h2>Регистрация на организация</h2>
                        <p class='req-fields m-t-lg m-b-lg'>Всички полета маркирани с * са задължителни.</p>
                    </div>
                    <form class="m-t-lg">
                        <div class="form-group row">
                            <label class="col-sm-3 col-xs-12 col-form-label">Изображение:</label>
                            <div class="col-sm-9">
                                <div class="fileinput-new thumbnai form-control input-border-r-12 m-r-md"></div>
                                <div class="inline-block">
                                    <span class="badge badge-pill"><label for="org-img">избери изображение</label></span>
                                    <input type="file" id="org-img">
                                </div>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="baseOrg" class="col-sm-3 col-xs-12 col-form-label">Основна организация:</label>
                            <div class="col-sm-9">
                                <input type="text" class="input-border-r-12 form-control" id="baseOrg" placeholder="">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="name" class="col-sm-3 col-xs-12 col-form-label">*Наименование:</label>
                            <div class="col-sm-9">
                                <input type="text" class="input-border-r-12 form-control" id="name" placeholder="Тест ЕООД">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="description" class="col-sm-3 col-xs-12 col-form-label">Описание:</label>
                            <div class="col-sm-9">
                                <textarea type="text" class="input-border-r-12 form-control" id="description" placeholder="Описание"></textarea>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="activity" class="col-sm-3 col-xs-12 col-form-label">Дейност:</label>
                            <div class="col-sm-9">
                                <textarea type="text" class="input-border-r-12 form-control" id="activity" placeholder="Дейност"></textarea>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="conatct" class="col-sm-3 col-xs-12 col-form-label">Контакти:</label>
                            <div class="col-sm-9">
                                <textarea type="text" class="input-border-r-12 form-control" id="conatct" placeholder="ул. Иван Вазов 35"></textarea>
                            </div>
                        </div>
                        <div class="form-group row">
                            <div class="col-sm-12 text-right">
                                <button type="submit" class="m-l-md btn btn-primary">готово</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
