@extends('layouts.app')

@section('content')
<div class="container">
    <div class="col-lg-10 col-md-11 col-xs-12 col-lg-offset-1 m-t-md p-l-r-none">
        <div class="row">
            <div class="col-xs-12">
                @include('partials.alerts-bar')
                @include('partials.user-nav-bar', ['view' => 'group'])
                <div>
                    <h2>Редакция на група</h2>
                    <p class='req-fields m-t-lg m-b-lg'>Всички полета маркирани с * са задължителни.</p>
                </div>
                <form method="POST" class="m-t-lg" enctype="multipart/form-data">
                    {{ csrf_field() }}
                    <div class="form-group row">
                        <label class="col-sm-3 col-xs-12 col-form-label">Изображение:</label>
                        <div class="col-sm-9">
                            <div class="fileinput-new thumbnai form-control input-border-r-12 m-r-md">
                                <img
                                    class="preview js-preview org-logo"
                                    src="{{ $model->logo }}"
                                    alt="organisation logo"
                                />
                            </div>
                            <div class="inline-block">
                                <span class="badge badge-pill"><label class="js-logo" for="logo">избери изображение</label></span>
                                <input class="hidden js-logo-input" type="file" name="logo" value="">
                                @if (isset($result->errors->logo))
                                    <span class="error">{{ $result->errors->logo[0] }}</span>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="form-group row {{ !empty($errors->uri) ? 'has-error' : '' }}">
                        <label for="uri" class="col-sm-3 col-xs-12 col-form-label">Уникален идентификатор:</label>
                        <div class="col-sm-9">
                            <input
                                type="text"
                                class="input-border-r-12 form-control"
                                name="uri"
                                value="{{ !empty($model->uri) ? $model->uri : '' }}"
                            >
                            @if (isset($result->errors->uri))
                                <span class="error">{{ $result->errors->uri[0] }}</span>
                            @endif
                        </div>
                    </div>
                    @foreach($fields as $field)
                        @if($field['view'] == 'translation')
                            @include('components.form_groups.translation_input', ['field' => $field, 'model' => $model])
                        @elseif($field['view'] == 'translation_txt')
                            @include('components.form_groups.translation_textarea', ['field' => $field, 'model' => $model])
                        @elseif($field['view'] == 'translation_custom')
                            @include('components.form_groups.translation_custom_fields', ['field' => $field, 'model' => $withModel])
                        @endif
                    @endforeach
                    <div class="form-group row">
                        <div class="col-sm-12 text-right">
                            <button type="submit" name="edit" class="m-l-md btn btn-primary">готово</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
