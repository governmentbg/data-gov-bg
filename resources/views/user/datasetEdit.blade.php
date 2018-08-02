@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-xs-12 p-sm">
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
                                            <li><a class="p-l-none" href="{{ url('/user') }}">известия</a></li>
                                            <li><a class="active" href="{{ url('/user/datasets') }}">моите данни</a></li>
                                            <li><a href="{{ url('/user/groups') }}">групи</a></li>
                                            <li><a href="{{ url('/user/organisations') }}">организации</a></li>
                                            <li><a href="{{ url('/user/settings') }}">настройки</a></li>
                                            <li><a href="{{ url('/user/invite') }}">покана</a></li>
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
                                <input type="email" class="input-border-r-12 form-control" id="title" value="Lorem ipsum BG">
                            </div>
                        </div>
                        <div class="form-group row required has-error">
                            <label for="titleTR" class="col-sm-3 col-xs-12 col-form-label">Title:</label>
                            <div class="col-sm-9">
                                <input type="email" class="input-border-r-12 form-control" id="titleTR" value="Lorem ipsum EN">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="identifier" class="col-sm-3 col-xs-12 col-form-label">Уникален идентификатор:</label>
                            <div class="col-sm-9">
                                <input type="text" class="input-border-r-12 form-control" id="identifier" value="256266233">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="description" class="col-sm-3 col-xs-12 col-form-label">Описание:</label>
                            <div class="col-sm-9">
                                <textarea type="text" class="input-border-r-12 form-control" id="description">Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut.</textarea>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="descriptionTR" class="col-sm-3 col-xs-12 col-form-label">Description:</label>
                            <div class="col-sm-9">
                                <textarea type="text" class="input-border-r-12 form-control" id="descriptionTR">Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut.</textarea>
                            </div>
                        </div>
                        <div class="form-group row required">
                            <label for="theme" class="col-sm-3 col-xs-12 col-form-label">Основна тема:</label>
                            <div class="col-sm-9">
                                <input type="text" class="input-border-r-12 form-control" id="theme" value="Основна тема">
                            </div>
                        </div>
                        <div class="form-group row tagsBG">
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
                        <div class="form-group row">
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
                        <div class="form-group row">
                            <label for="organisation" class="col-sm-3 col-xs-12 col-form-label">Организация:</label>
                            <div class="col-sm-9">
                                <select class="input-border-r-12 form-control" id="organisation">
                                    <option value="">Изберете организация</option>
                                    <option value="idOrg">име организация</option>
                                    <option value="idOrg">име организация</option>
                                    <option value="idOrg">име организация</option>
                                    <option value="idOrg">име организация</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="group" class="col-sm-3 col-xs-12 col-form-label">Група:</label>
                            <div class="col-sm-9">
                                <select class="input-border-r-12 form-control" id="group">
                                    <option value="">Изберете група</option>
                                    <option value="idOrg">име група</option>
                                    <option value="idOrg">име група</option>
                                    <option value="idOrg">име група</option>
                                    <option value="idOrg">име група</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="visibility" class="col-sm-3 col-xs-12 col-form-label">Видимост:</label>
                            <div class="col-sm-9">
                                <select class="input-border-r-12 form-control" id="visibility">
                                    <option value="">Изберете видимост</option>
                                    <option value="idVisibility">видим за всички</option>
                                    <option value="idVisibility">видим за всички</option>
                                    <option value="idVisibility">видим за всички</option>
                                    <option value="idVisibility">видим за всички</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="source" class="col-sm-3 col-xs-12 col-form-label">Източник:</label>
                            <div class="col-sm-9">
                                <input type="text" class="input-border-r-12 form-control" id="source" value="Източник">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="version" class="col-sm-3 col-xs-12 col-form-label">Версия:</label>
                            <div class="col-sm-9">
                                <input type="text" class="input-border-r-12 form-control" id="version" value="1.0">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="author" class="col-sm-3 col-xs-12 col-form-label">Автор:</label>
                            <div class="col-sm-9">
                                <input type="text" class="input-border-r-12 form-control" id="author" value="Иван Иванов">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="author-email" class="col-sm-3 col-xs-12 col-form-label">
                                e-mail на автора:
                                <span class="info-icon"><i class="fa fa-info"></i></span>
                            </label>

                            <div class="col-sm-9">
                                <input type="email" class="input-border-r-12 form-control" id="author-email" value="ivanov@abv.bg">
                            </div>
                        </div>
                        <div class="form-group row">
                            <!--Service Level Agreement (SLA) -->
                            <label for="sla" class="col-sm-3 col-xs-12 col-form-label">
                                Споразумение за ниво на обсужване:
                                <span class="info-icon"><i class="fa fa-info"></i></span>
                            </label>
                            <div class="col-sm-9">
                                <textarea type="text" class="input-border-r-12 form-control" id="sla" placeholder="Service Level Agreement"></textarea>
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
                            <div class="col-sm-12 pull right text-right">
                                <div class="row">
                                    <button type="button" class="btn btn-primary">добавяне на ресурс</button>
                                    <button type="button" class="btn btn-primary">изглед</button>
                                    <button type="button" class="btn btn-primary">публикуване</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
