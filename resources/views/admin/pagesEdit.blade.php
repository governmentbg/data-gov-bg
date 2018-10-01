@extends('layouts.app')

@section('content')
    <div class="container">
        @include('partials.alerts-bar')
        @include('partials.admin-nav-bar', ['view' => 'pages'])

        <div class="row m-t-lg">
            <div class="col-md-2 col-sm-1"></div>
            <div class="col-md-8 col-sm-10">
                <div class="frame add-terms">
                    <div class="p-w-md text-center m-b-lg m-t-lg">
                        <h2>{{ __('custom.page_edit') }}</h2>
                    </div>
                    <div class="body">
                        <form method="POST" enctype="multipart/form-data" class="form-horisontal">
                            {{ csrf_field() }}

                            @foreach($fields as $field)
                                @if($field['view'] == 'translation')
                                    @include(
                                        'components.form_groups.translation_input',
                                        ['field' => $field]
                                    )
                                @elseif($field['view'] == 'translation_txt')
                                    @include(
                                        'components.form_groups.translation_textarea',
                                        ['field' => $field]
                                    )
                                @endif
                            @endforeach
                            <div class="form-group row required">
                                <label for="section" class="col-sm-3 col-xs-12 col-form-label">{{ __('custom.section') }}:</label>
                                <div class="col-sm-9">
                                    <select
                                        id="section"
                                        name="section_id"
                                        class="js-select form-control"
                                        data-placeholder="{{ __('custom.select_section') }}"
                                    >
                                        <option></option>
                                        @foreach ($sections as $id => $name)
                                            <option
                                                value="{{ $id }}"
                                                {{ $id == $model->section_id ? 'selected' : '' }}
                                            >{{ $name }}</option>
                                        @endforeach
                                    </select>
                                    <span class="error">{{ $errors->first('section_id') }}</span>
                                </div>
                            </div>
                            <div class="form-group row m-b-lg m-t-md">
                                <label for="forum_link" class="col-sm-3 col-xs-12 col-form-label">{{ __('custom.forum_link') }}:</label>
                                <div class="col-sm-9">
                                    <input
                                        name="forum_link"
                                        class="input-border-r-12 form-control"
                                        value="{{ !empty($model->forum_link) ? $model->forum_link : '' }}"
                                    >
                                    @if (isset($errors) && $errors->has('forum_link'))
                                        <span class="error">{{ $errors->first('forum_link') }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="help_page" class="col-sm-3 col-xs-12 col-form-label">{{ __('custom.help_page') }}:</label>
                                <div class="col-sm-9">
                                    <select
                                        id="help_page"
                                        name="help_page"
                                        class="js-select form-control"
                                        data-placeholder="{{ __('custom.select') }}"
                                    >
                                        <option></option>
                                        @foreach ($helpPages as $page)
                                            <option
                                                value="{{ $page->name }}"
                                                {{ $page->name == $model->help_page ? 'selected' : '' }}
                                            >{{ $page->name }}</option>
                                        @endforeach
                                    </select>
                                    <span class="error">{{ $errors->first('help_page') }}</span>
                                </div>
                            </div>
                            <div class="form-group row m-b-lg m-t-md">
                                <label for="valid" class="col-sm-3 col-xs-12 col-form-label">{{ __('custom.valid') }}:</label>
                                <div class="col-sm-4 m-b-sm">
                                    <div class="col-xs-3">{{ __('custom.from') .': ' }}</div>
                                    <div class="col-xs-9 text-left search-field admin">
                                        <input class="datepicker input-border-r-12 form-control" name="valid_from" value="{{ !empty($model->valid_from) ? $model->valid_from : '' }}">
                                    </div>
                                    @if (isset($errors) && $errors->has('valid_from'))
                                        <span class="error">{{ $errors->first('valid_from') }}</span>
                                    @endif
                                </div>
                                <div class="col-sm-4 m-b-sm">
                                    <div class="col-xs-3">{{ __('custom.to') .': ' }}</div>
                                    <div class="col-xs-9 text-left search-field admin">
                                        <input class="datepicker input-border-r-12 form-control" name="valid_to" value="{{ !empty($model->valid_to) ? $model->valid_to : '' }}">
                                    </div>
                                    @if (isset($errors) && $errors->has('valid_to'))
                                        <span class="error">{{ $errors->first('valid_to') }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="form-group row m-b-lg m-t-md">
                                <label for="active" class="col-sm-3 col-xs-12 col-form-label">{{ utrans('custom.activef') }}:</label>
                                <div class="col-lg-9 col-md-9 col-sm-9 col-xs-12">
                                    <div class="js-check">
                                        <input
                                            type="checkbox"
                                            name="active"
                                            value="1"
                                            {{ !empty($model->active) ? 'checked' : '' }}
                                        >
                                        @if (isset($errors) && $errors->has('active'))
                                            <span class="error">{{ $errors->first('active') }}</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="col-sm-12 text-right">
                                    <button type="submit" name="edit" value="1" class="m-l-md btn btn-custom">{{ uctrans('custom.edit') }}</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-md-2 col-sm-1"></div>
        </div>
    </div>
@endsection
