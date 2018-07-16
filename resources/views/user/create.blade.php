@extends('layouts.app')

@section('content')
<div class="modal inmodal fade" id="addLicense" tabindex="-1" role="dialog"  aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="frame">
                <div class="p-w-md">
                    <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                    <h2>Заявка за добавяне на лиценз</h2>
                </div>
                <div class="modal-body">
                    <form class="m-t-lg">
                        <div class="form-group row">
                            <label class="col-sm-3 col-xs-12 col-form-label" for="date">Дата:</label>
                            <div class="col-sm-9">
                                <input type="text" class="datepicker input-border-r-12 form-control">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="description" class="col-sm-3 col-xs-12 col-form-label">Описание:</label>
                            <div class="col-sm-9">
                                <input type="text" class="input-border-r-12 form-control" id="description" placeholder="Описание">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="name" class="col-sm-3 col-xs-12 col-form-label">Подател:</label>
                            <div class="col-sm-9">
                                <input type="text" class="input-border-r-12 form-control" id="name" placeholder="Иван Иванов">
                            </div>
                        </div>
                        <div class="form-group row">
                            <div class="col-sm-12 text-right">
                                <button type="submit" class="m-l-md btn btn-custom">изпрати</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="container">
    <div class="row">
        <div class="col-xs-12 m-t-md p-sm">
            <div class="row">
                <div class="col-sm-3 col-xs-12 sidenav">
                    <span class="my-profile m-b-lg m-l-sm">Моят профил</span>
                </div>
                <div class="col-sm-9 col-xs-12">
                    <div class="filter-content">
                        <div class="col-md-12">
                            <div class="row">
                                <div class="col-sm-12 p-l-none">
                                    <div>
                                        <ul class="nav filter-type right-border">
                                            <li><a class="p-l-none" href="{{ url('/user') }}">нюзфийд</a></li>
                                            <li><a href="{{ url('/user/datasets') }}">моите данни</a></li>
                                            <li><a class="active" href="{{ url('/user/create') }}">нов набор</a></li>
                                            <li><a href="{{ url('/user/settings') }}">настройки</a></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xs-12 m-t-lg">
                    <p class='req-fields'>Всички полета маркирани с * са задължителни.</p>
                    <p class='req-fields has-error'>Всички полета маркирани с * са задължителни.</p>
                    <form>
                        <div class="form-group row required">
                            <label for="title" class="col-sm-3 col-xs-12 col-form-label">Заглавие:</label>
                            <div class="col-sm-9">
                                <input type="text" class="input-border-r-12 form-control" id="title" placeholder="Заглавие">
                            </div>
                        </div>
                        <div class="form-group row required">
                            <label for="identifier" class="col-sm-3 col-xs-12 col-form-label">Уникален идентификатор:</label>
                            <div class="col-sm-9">
                                <input type="text" class="input-border-r-12 form-control" id="identifier" placeholder="Уникален идентификатор">
                            </div>
                        </div>
                        <div class="form-group row required has-error">
                            <label for="description" class="col-sm-3 col-xs-12 col-form-label">Описание:</label>
                            <div class="col-sm-9">
                                <textarea type="text" class="input-border-r-12 form-control" id="description" placeholder="Описание"></textarea>
                            </div>
                        </div>
                        <div class="form-group row required">
                            <label for="theme" class="col-sm-3 col-xs-12 col-form-label">Основна тема:</label>
                            <div class="col-sm-9">
                                <input type="text" class="input-border-r-12 form-control" id="theme" placeholder="Основна тема">
                            </div>
                        </div>
                        <div class="form-group row required tagsBG">
                            <label for="tagsBG" class="col-sm-3 col-xs-12 col-form-label">Етикет БГ:</label>
                            <div class="col-sm-9 example ">
                                <input type="text" class="input-border-r-12 form-control"  id="tagsBG" value="образование,наредба" data-role="tagsinput">
                            </div>
                        </div>
                        <div class="form-group row tagsEN">
                            <label for="tagsEN" class="col-sm-3 col-xs-12 col-form-label">Етикет EN:</label>
                            <div class="col-sm-9">
                                <input type="text" class="input-border-r-12 form-control"  id="tagsEN" value="education,regulation" data-role="tagsinput">
                            </div>
                        </div>
                        <div class="form-group row required">
                            <label for="termsOfuse" class="col-sm-3 col-xs-12 col-form-label">Условия за ползвне:</label>
                            <div class="col-sm-6">
                                <select class="input-border-r-12 form-control term-use"  id="termsOfuse" size="5">
                                    @for ($i = 0; $i < 5; $i++)
                                    <option>Lorem ipsum</option>
                                    @endfor
                                </select>
                            </div>
                            <div class="col-sm-3 text-right add-terms">
                                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addLicense">Нови условия</button>
                            </div>
                        </div>
                        <div class="form-group row required">
                            <label for="organisation" class="col-sm-3 col-xs-12 col-form-label">Организация:</label>
                            <div class="col-sm-9">
                                <input type="text" class="input-border-r-12 form-control" id="organisation" placeholder="Организация">
                            </div>
                        </div>
                        <div class="form-group row required">
                            <label for="visibility" class="col-sm-3 col-xs-12 col-form-label">Видимост:</label>
                            <div class="col-sm-9">
                                <input type="text" class="input-border-r-12 form-control" id="visibility" placeholder="Видимост">
                            </div>
                        </div>
                        <div class="form-group row required">
                            <label for="source" class="col-sm-3 col-xs-12 col-form-label">Източник:</label>
                            <div class="col-sm-9">
                                <input type="text" class="input-border-r-12 form-control" id="source" placeholder="Източник">
                            </div>
                        </div>
                        <div class="form-group row required">
                            <label for="version" class="col-sm-3 col-xs-12 col-form-label">Версия:</label>
                            <div class="col-sm-9">
                                <input type="text" class="input-border-r-12 form-control" id="version" placeholder="Версия">
                            </div>
                        </div>
                        <div class="form-group row required">
                            <label for="author" class="col-sm-3 col-xs-12 col-form-label">Автор:</label>
                            <div class="col-sm-9">
                                <input type="text" class="input-border-r-12 form-control" id="author" placeholder="Автор">
                            </div>
                        </div>
                        <div class="form-group row required">
                            <label for="author-email" class="col-sm-3 col-xs-12 col-form-label">e-mail на автора:</label>
                            <div class="col-sm-9">
                                <input type="email" class="input-border-r-12 form-control" id="author-email" placeholder="e-mail на автора">
                            </div>
                        </div>
                        <div class="form-group row required">
                            <label for="sla" class="col-sm-3 col-xs-12 col-form-label">Service Level Agreement:</label>
                            <div class="col-sm-9">
                                <input type="text" class="input-border-r-12 form-control" id="sla" placeholder="Service Level Agreement">
                            </div>
                        </div>
                        <div class="form-group row">
                            <div class="col-xs-12 text-right mng-btns">
                                <button type="button" class="btn btn-primary">качване</button>
                                <button type="button" class="btn btn-primary">изглед</button>
                                <button type="button" class="btn btn-primary">публикуване</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
