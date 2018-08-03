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
                            <label for="fname" class="col-sm-3 col-xs-12 col-form-label">Име:</label>
                            <div class="col-sm-9">
                                <input type="text" class="input-border-r-12 form-control" id="fname" placeholder="Иван">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="lname" class="col-sm-3 col-xs-12 col-form-label">Фамилия:</label>
                            <div class="col-sm-9">
                                <input type="text" class="input-border-r-12 form-control" id="lname" placeholder="Иванов">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="email" class="col-sm-3 col-xs-12 col-form-label">E-mail:</label>
                            <div class="col-sm-9">
                                <input type="email" class="input-border-r-12 form-control" id="email" placeholder="ivanov@abv.bg">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="description" class="col-sm-3 col-xs-12 col-form-label">Описание:</label>
                            <div class="col-sm-9">
                                <textarea class="input-border-r-12 form-control" id="description" placeholder="Описание"></textarea>
                            </div>
                        </div>
                        <div class="form-group row">
                            <div class="col-sm-12 text-right">
                                <button type="submit" class="m-l-md btn btn-custom">Изпрати</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="container">
    @include('partials.alerts-bar')
    @include('partials.user-nav-bar', ['view' => 'dataset'])
    <div class="col-xs-12 m-t-lg">
        <p class='req-fields'>Всички полета маркирани с * са задължителни.</p>
        <form method="POST" action="{{ url('/user/datasetCreate') }}">
            {{ csrf_field() }}
            <div class="form-group row {{ isset($errors['uri']) ? 'has-error' : '' }}">
                <label for="identifier" class="col-sm-3 col-xs-12 col-form-label">Уникален идентификатор:</label>
                <div class="col-sm-9">
                    <input
                        id="identifier"
                        class="input-border-r-12 form-control"
                        name="uri"
                        value="{{ old('uri') }}"
                        type="text"
                        placeholder="Уникален идентификатор">
                    @if (isset($errors['uri']))
                        <span class="error">{{ $errors['uri'] }}</span>
                    @endif
                </div>
            </div>
            <div class="form-group row required {{ isset($errors['category_id']) ? 'has-error' : '' }}">
                <label for="theme" class="col-sm-3 col-xs-12 col-form-label">Основна тема:</label>
                <div class="col-sm-9">
                    <select
                        id="theme"
                        class="input-border-r-12 form-control"
                        name="category_id"
                    >
                        <option value="">Изберете основна тема</option>
                        @foreach ($categories as $id => $category)
                            <option
                                value="{{ $id }}"
                                {{ $id == old('category_id') ? 'selected' : '' }}
                            >{{ $category }}</option>
                        @endforeach
                    </select>
                    @if (isset($errors['category_id']))
                        <span class="error">{{ $errors['category_id'] }}</span>
                    @endif
                </div>
            </div>

            @foreach($fields as $field)
                @if($field['view'] == 'translation')
                    @include(
                        'components.form_groups.translation_input',
                        ['field' => $field, 'result' => session('result')]
                    )
                @elseif($field['view'] == 'translation_txt')
                    @include(
                        'components.form_groups.translation_textarea',
                        ['field' => $field, 'result' => session('result')]
                    )
                @elseif($field['view'] == 'translation_tags')
                    @include(
                        'components.form_groups.translation_tags',
                        ['field' => $field, 'result' => session('result')]
                    )
                @endif
            @endforeach

            <div class="form-group row {{ isset($errors['terms_of_use_id']) ? 'has-error' : '' }}">
                <label for="termsOfuse" class="col-sm-3 col-xs-12 col-form-label">Условия за ползване:</label>
                <div class="col-sm-6">
                    <select
                        id="termsOfuse"
                        class="input-border-r-12 form-control term-use"
                        name="terms_of_use_id"
                        size="5"
                    >
                        @foreach ($termsOfUse as $id =>$term)
                            <option
                                value="{{ $id }}"
                                {{ $id == old('terms_of_use_id') ? 'selected' : '' }}
                            >{{ $term }}</option>
                        @endforeach
                    </select>
                    @if (isset($errors['terms_of_use_id']))
                        <span class="error">{{ $errors['terms_of_use_id'] }}</span>
                    @endif
                </div>
                <div class="col-sm-3 text-right add-terms">
                    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addLicense">Нови условия</button>
                </div>
            </div>
            <div class="form-group row {{ isset($errors['org_id']) ? 'has-error' : '' }}">
                <label for="organisation" class="col-sm-3 col-xs-12 col-form-label">Организация:</label>
                <div class="col-sm-9">
                    <select
                        id="organisation"
                        class="input-border-r-12 form-control"
                        name="org_id"
                    >
                        <option value="">Изберете организация</option>
                        @foreach ($organisations as $id =>$org)
                            <option
                                value="{{ $id }}"
                                {{ $id == old('org_id') ? 'selected' : '' }}
                            >{{ $org }}</option>
                        @endforeach
                    </select>
                    @if (isset($errors['org_id']))
                        <span class="error">{{ $errors['org_id'] }}</span>
                    @endif
                </div>
            </div>
            <div class="form-group row {{ isset($errors['group_id']) ? 'has-error' : '' }}">
                <label for="group" class="col-sm-3 col-xs-12 col-form-label">Група:</label>
                <div class="col-sm-9">
                    <select
                        id="group"
                        class="input-border-r-12 form-control"
                        name="group_id"
                    >
                        <option value="">Изберете група</option>
                        @foreach ($groups as $id =>$group)
                            <option
                                value="{{ $id }}"
                                {{ $id == old('group_id') ? 'selected' : '' }}
                            >{{ $group }}</option>
                        @endforeach
                    </select>
                    @if (isset($errors['group_id']))
                        <span class="error">{{ $errors['group_id'] }}</span>
                    @endif
                </div>
            </div>
            <div class="form-group row {{ isset($errors['visibility']) ? 'has-error' : '' }}">
                <label for="visibility" class="col-sm-3 col-xs-12 col-form-label">Видимост:</label>
                <div class="col-sm-9">
                    <select
                        id="visibility"
                        class="input-border-r-12 form-control"
                        name="visibility"
                    >
                        <option value="">Изберете видимост</option>
                        @foreach ($visibilityOpt as $id => $visOpt)
                            <option
                                value="{{ $id }}"
                                {{ $id == old('visibility') ? 'selected' : '' }}
                            >{{ $visOpt }}</option>
                        @endforeach
                    </select>
                    @if (isset($errors['visibility']))
                        <span class="error">{{ $errors['visibility'] }}</span>
                    @endif
                </div>
            </div>
            <div class="form-group row {{ isset($errors['source']) ? 'has-error' : '' }}">
                <label for="source" class="col-sm-3 col-xs-12 col-form-label">Източник:</label>
                <div class="col-sm-9">
                    <input
                        id="source"
                        class="input-border-r-12 form-control"
                        name="source"
                        value="{{ old('source') }}"
                        type="text"
                        placeholder="Източник">
                    @if (isset($errors['source']))
                        <span class="error">{{ $errors['source'] }}</span>
                    @endif
                </div>
            </div>
            <div class="form-group row {{ isset($errors['version']) ? 'has-error' : '' }}">
                <label for="version" class="col-sm-3 col-xs-12 col-form-label">Версия:</label>
                <div class="col-sm-9">
                    <input
                        id="version"
                        class="input-border-r-12 form-control"
                        name="version"
                        value="{{ old('version') }}"
                        type="text"
                        placeholder="Версия">
                    @if (isset($errors['version']))
                        <span class="error">{{ $errors['version'] }}</span>
                    @endif
                </div>
            </div>
            <div class="form-group row {{ isset($errors['author_name']) ? 'has-error' : '' }}">
                <label for="author" class="col-sm-3 col-xs-12 col-form-label">Автор:</label>
                <div class="col-sm-9">
                    <input
                        id="author"
                        class="input-border-r-12 form-control"
                        name="author_name"
                        value="{{ old('author_name') }}"
                        type="text"
                        placeholder="Автор">
                    @if (isset($errors['author_name']))
                        <span class="error">{{ $errors['author_name'] }}</span>
                    @endif
                </div>
            </div>
            <div class="form-group row {{ isset($errors['author_email']) ? 'has-error' : '' }}">
                <label for="author-email" class="col-sm-3 col-xs-12 col-form-label">E-mail на автора:</label>
                <div class="col-sm-9">
                    <input
                        id="author-email"
                        class="input-border-r-12 form-control"
                        name="author_email"
                        value="{{ old('author_email') }}"
                        type="email"
                        placeholder="E-mail на автора">
                    @if (isset($errors['author_email']))
                        <span class="error">{{ $errors['author_email'] }}</span>
                    @endif
                </div>
            </div>
            <div class="form-group row {{ isset($errors['support_name']) ? 'has-error' : '' }}">
                <label for="support" class="col-sm-3 col-xs-12 col-form-label">Контакт:</label>
                <div class="col-sm-9">
                    <input
                        id="support"
                        class="input-border-r-12 form-control"
                        name="support_name"
                        value="{{ old('support_name') }}"
                        type="text"
                        placeholder="Контакт">
                    @if (isset($errors['support_name']))
                        <span class="error">{{ $errors['support_name'] }}</span>
                    @endif
                </div>
            </div>
            <div class="form-group row {{ isset($errors['support_email']) ? 'has-error' : '' }}">
                <label for="support-email" class="col-sm-3 col-xs-12 col-form-label">E-mail за контакти:</label>
                <div class="col-sm-9">
                    <input
                        id="support-email"
                        class="input-border-r-12 form-control"
                        name="support_email"
                        value="{{ old('support_email') }}"
                        type="email"
                        placeholder="E-mail за контакти">
                    @if (isset($errors['support_email']))
                        <span class="error">{{ $errors['support_email'] }}</span>
                    @endif
                </div>
            </div>

            @foreach($fields as $field)
                @if($field['view'] == 'translation_custom')
                    @include(
                        'components.form_groups.translation_custom_fields',
                        ['field' => $field, 'result' => session('result')]
                    )
                @endif
            @endforeach
            <div class="form-group row">
                <div class="col-xs-12 text-right mng-btns">
                    <button type="button" class="btn btn-primary">изглед</button>
                    <button type="submit" class="btn btn-primary">запази</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
