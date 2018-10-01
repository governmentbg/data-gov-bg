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
                        <h2>{{ __('custom.news_add') }}</h2>
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
                                        value="{{ old('forum_link') }}"
                                    >
                                    @if (isset($errors) && $errors->has('forum_link'))
                                        <span class="error">{{ $errors->first('forum_link') }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="form-group row m-b-lg m-t-md">
                                <label for="valid" class="col-sm-3 col-xs-12 col-form-label">{{ __('custom.valid') }}:</label>
                                <div class="col-sm-4 m-b-sm">
                                    <div class="col-xs-3">{{ __('custom.from') .': ' }}</div>
                                    <div class="col-xs-9 text-left search-field admin">
                                        <input class="datepicker input-border-r-12 form-control" name="valid_from" value="{{ old('valid_from') }}">
                                    </div>
                                    @if (isset($errors) && $errors->has('valid_from'))
                                        <span class="error">{{ $errors->first('valid_from') }}</span>
                                    @endif
                                </div>
                                <div class="col-sm-4 m-b-sm">
                                    <div class="col-xs-3">{{ __('custom.to') .': ' }}</div>
                                    <div class="col-xs-9 text-left search-field admin">
                                        <input class="datepicker input-border-r-12 form-control" name="valid_to" value="{{ old('valid_to') }}">
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
                                            {{ !empty(old('active')) ? 'checked' : '' }}
                                        >
                                        @if (isset($errors) && $errors->has('active'))
                                            <span class="error">{{ $errors->first('active') }}</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="col-sm-12 text-right">
                                    <button
                                        name="back"
                                        class="btn btn-primary"
                                    >{{ uctrans('custom.close') }}</button>
                                    <button type="submit" name="create" value="1" class="m-l-md btn btn-custom">{{ __('custom.add') }}</button>
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
