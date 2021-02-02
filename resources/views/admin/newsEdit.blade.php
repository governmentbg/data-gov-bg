@extends('layouts.app')

@section('content')
    <div class="container">
        @include('partials.alerts-bar')
        @include('partials.admin-nav-bar', ['view' => 'news'])

        <div class="row m-t-lg">
            <div class="col-md-2 col-sm-1"></div>
            <div class="col-md-8 col-sm-10">
                <div class="frame add-terms">
                    <div class="p-w-md text-center m-b-lg m-t-lg">
                        <h2>{{ __('custom.news_edit') }}</h2>
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
                            <div class="form-group row m-b-lg m-t-md">
                                <label for="valid" class="col-sm-3 col-xs-12 col-form-label">{{ __('custom.valid') }}:</label>
                                <div class="col-sm-9 col-xs-12 m-b-sm">
                                    <div class=" row">
                                        <div class="col-sm-6 m-b-sm">
                                            <div class="row">
                                                <div class="col-xs-3">{{ __('custom.from') .': ' }}</div>
                                                <div class="col-xs-9 text-left search-field admin">
                                                    <input class="datepicker input-border-r-12 form-control" name="valid_from" value="{{ !empty($model->valid_from) ? $model->valid_from : '' }}">
                                                </div>
                                                @if (isset($errors) && $errors->has('valid_from'))
                                                    <span class="error">{{ $errors->first('valid_from') }}</span>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="col-sm-6 m-b-sm">
                                            <div class="row">
                                                <div class="col-xs-3">{{ __('custom.to') .': ' }}</div>
                                                <div class="col-xs-9 text-left search-field admin">
                                                    <input class="datepicker input-border-r-12 form-control" name="valid_to" value="{{ !empty($model->valid_to) ? $model->valid_to : '' }}">
                                                </div>
                                                @if (isset($errors) && $errors->has('valid_to'))
                                                    <span class="error">{{ $errors->first('valid_to') }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row m-b-lg m-t-md">
                                <label for="active" class="col-lg-3 col-sm-3 col-xs-3 col-form-label">{{ utrans('custom.activef') }}:</label>
                                <div class="col-lg-2 col-sm-9 col-xs-9">
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
                            <div class="form-group row m-b-lg m-t-md">
                                <label for="home_page" class="col-lg-3 col-sm-3 col-xs-3 col-form-label">{{ utrans('custom.admin_latest_news') }}:</label>
                                <div class="col-lg-2 col-sm-9 col-xs-9">
                                    <div class="js-check">
                                        <input
                                            type="checkbox"
                                            name="home_page"
                                            value="1"
                                            {{ !empty($model->home_page) ? 'checked' : '' }}
                                        >
                                        @if (isset($errors) && $errors->has('home_page'))
                                            <span class="error">{{ $errors->first('home_page') }}</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="col-sm-12 text-right">
                                    <a
                                        href="{{ url('admin/news/list') }}"
                                        class="btn btn-primary"
                                    >
                                        {{ uctrans('custom.close') }}
                                    </a>
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
