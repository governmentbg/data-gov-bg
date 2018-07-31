@extends('layouts.app')

@section('content')
<div class="container">
    <div class="col-lg-10 col-md-11 col-xs-12 col-lg-offset-1 m-t-md p-l-r-none">
        <div class="row">
            <div class="col-xs-12">
                @if (!empty($_GET['message']))
                    <div class="alert alert-success">
                        {{ $_GET['message'] }}
                    </div>
                @endif
                <div>
                    <h2>Регистрация на организация</h2>
                    <p class='req-fields m-t-lg m-b-lg'>Всички полета маркирани с * са задължителни.</p>
                </div>
                <form method="POST" class="m-t-lg" enctype="multipart/form-data">
                    {{ csrf_field() }}
                    <div class="form-group row">
                        <label class="col-sm-3 col-xs-12 col-form-label">Изображение:</label>
                        <div class="col-sm-9">
                            <div class="fileinput-new thumbnai form-control input-border-r-12 m-r-md">
                                <img class="preview js-preview hidden" src="#" alt="organisation logo" />
                            </div>
                            <div class="inline-block">
                                <span class="badge badge-pill"><label class="js-logo" for="logo">избери изображение</label></span>
                                <input class="hidden js-logo-input" type="file" name="logo">
                                @if (!empty($error->logo))
                                    <span class="error">{{ $error->logo[0] }}</span>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="baseOrg" class="col-sm-3 col-xs-12 col-form-label">Основна организация:</label>
                        <div class="col-sm-9">
                            <input type="text" class="input-border-r-12 form-control" name="parent_org_id" placeholder="">
                            @if (!empty($error->parent_org_id))
                                <span class="error">{{ $error->parent_org_id[0] }}</span>
                            @endif
                        </div>
                    </div>
                    <div class="form-group row required">
                        <label for="name" class="col-sm-3 col-xs-12 col-form-label">Наименование:</label>
                        <div class="col-sm-9">
                            <input type="text" class="input-border-r-12 form-control" name="name" placeholder="Тест ЕООД">
                            @if (!empty($error->name))
                                <span class="error">{{ $error->name[0] }}</span>
                            @endif
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="uri" class="col-sm-3 col-xs-12 col-form-label">Уникален идентификатор:</label>
                        <div class="col-sm-9">
                            <input type="text" class="input-border-r-12 form-control" name="uri" placeholder="Тест432593">
                            @if (!empty($error->uri))
                                <span class="error">{{ $error->uri[0] }}</span>
                            @endif
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="description" class="col-sm-3 col-xs-12 col-form-label">Описание:</label>
                        <div class="col-sm-9">
                            <textarea type="text" class="input-border-r-12 form-control" name="description" placeholder="Описание"></textarea>
                            @if (!empty($error->description))
                                <span class="error">{{ $error->description[0] }}</span>
                            @endif
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="activity" class="col-sm-3 col-xs-12 col-form-label">Дейност:</label>
                        <div class="col-sm-9">
                            <textarea type="text" class="input-border-r-12 form-control" name="activity_info" placeholder="Дейност"></textarea>
                            @if (!empty($error->activity_info))
                                <span class="error">{{ $error->activity_info[0] }}</span>
                            @endif
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="conatct" class="col-sm-3 col-xs-12 col-form-label">Контакти:</label>
                        <div class="col-sm-9">
                            <textarea type="text" class="input-border-r-12 form-control" name="conatcts" placeholder="ул. Иван Вазов 35"></textarea>
                            @if (!empty($error->conatcts))
                                <span class="error">{{ $error->conatcts[0] }}</span>
                            @endif
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="type" class="col-lg-3 col-sm-3 col-xs-12 col-form-label">Тип:</label>
                        @foreach ($orgTypes as $id => $name)
                            <div class="col-lg-4 col-md-4 col-xs-12 m-b-md">
                                <label class="radio-label">
                                    {{ $name }}
                                    <div class="js-check">
                                        <input type="radio" name="type" value="{{ $id }}">
                                    </div>
                                </label>
                            </div>
                        @endforeach
                    </div>
                    <div class="form-group row">
                        <label for="active" class="col-sm-3 col-xs-12 col-form-label">Активнa:</label>
                        <div class="col-lg-9 col-md-9 col-sm-9 col-xs-12">
                            <div class="js-check">
                                <input type="checkbox" name="active" value="1" checked>
                            </div>
                        </div>
                    </div>
                    <div class="form-group row">
                        @for ($i = 1; $i <= 3; $i++)
                        <div class="col-xs-12">Допълнително поле:</div>
                            <div class="col-lg-12">
                                <div class="col-sm-12 col-xs-12 p-r-none">
                                    <div class="row">
                                        <div class="col-md-6 col-sm-6 col-xs-12">
                                            <label for="sla" class="col-lg-4 col-md-6 col-xs-12 col-form-label">Заглавие: </label>
                                            <div class="col-lg-8 col-md-6 col-sm-6 col-sm-12 cust-val">
                                                <input
                                                    type="text"
                                                    class="input-border-r-12 form-control"
                                                    name="custom_field[{{ $i }}][label]"
                                                    placeholder="Lorem ipsum"
                                                >
                                                @if (!empty($error->custom_field[$i]['label']))
                                                    <span class="error">{{ $error->custom_field[$i]['label'][0] }}</span>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="col-md-6 col-sm-6 col-xs-12">
                                            <label for="sla" class="col-lg-4 col-md-6 col-xs-12 col-form-label">Стойност: </label>
                                            <div class="col-lg-8 col-md-6 col-sm-4 col-sm-12 cust-val">
                                                <input
                                                    type="text"
                                                    class="input-border-r-12 form-control"
                                                    name="custom_field[{{ $i }}][value]"
                                                    placeholder="Lorem ipsum dolor sit amet, consectetur adipiscing elit"
                                                >
                                                @if (!empty($error->custom_field[$i]['value']))
                                                    <span class="error">{{ $error->custom_field[$i]['value'][0] }}</span>
                                                @endif
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
@endsection
