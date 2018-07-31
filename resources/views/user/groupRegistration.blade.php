@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-lg-10 col-md-11 col-xs-12 col-lg-offset-2 col-md-offset-1 m-t-md p-l-r-none">
            <div class="row">
                <div class="col-xs-12">
                    <div>
                        <h2>Регистрация на група</h2>
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
                        <div class="form-group row required">
                            <label for="name" class="col-sm-3 col-xs-12 col-form-label">Наименование:</label>
                            <div class="col-sm-9">
                                <input type="text" class="input-border-r-12 form-control" id="name" placeholder="Тест ЕООД">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="uri" class="col-sm-3 col-xs-12 col-form-label">Уникален идентификатор:</label>
                            <div class="col-sm-9">
                                <input type="text" class="input-border-r-12 form-control" id="uri" placeholder="Тест432593">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="description" class="col-sm-3 col-xs-12 col-form-label">Описание:</label>
                            <div class="col-sm-9">
                                <textarea type="text" class="input-border-r-12 form-control" id="description" placeholder="Описание"></textarea>
                            </div>
                        </div>
                        <div class="form-group row">
                            @for($i = 1; $i <= 3; $i++)
                                <div class="m-t-sm col-sm-12 p-l-r-none">
                                    <div class="col-xs-12 m-t-sm">Допълнително поле:</div>
                                    <div class="col-sm-12 col-xs-12 p-r-none">
                                        <div class="row">
                                            <div class="col-sm-6 col-xs-12">
                                                <label for="sla" class="col-sm-2 col-xs-12 col-form-label p-h-xs">Заглавие:</label>
                                                <div class="col-sm-10">
                                                    <input type="email" class="input-border-r-12 form-control" id="author-email" placeholder="Lorem ipsum">
                                                </div>
                                            </div>
                                            <div class="col-sm-6 col-xs-12">
                                                <label for="sla" class="col-sm-2 col-xs-12 col-form-label p-h-xs">Стойност:</label>
                                                <div class="col-sm-10">
                                                    <input type="email" class="input-border-r-12 form-control" id="author-email" placeholder="Lorem ipsum dolor sit amet, consectetur adipiscing elit">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endfor
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
