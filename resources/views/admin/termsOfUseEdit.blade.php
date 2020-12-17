@extends('layouts.app')

@section('content')
    <div class="container">
        @include('partials.alerts-bar')
        @include('partials.admin-nav-bar', ['view' => 'termsConditions'])

        <div class="row">
            <div class="col-md-2 col-sm-1"></div>
            <div class="col-md-8 col-sm-10">
                <div class="frame add-terms">
                    <div class="p-w-md text-center m-b-lg m-t-lg">
                        <h2>{{ __('custom.edit_terms') }}</h2>
                    </div>
                    <div class="body">
                        <form method="POST" class="form-horisontal">
                            {{ csrf_field() }}

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
                                @endif
                            @endforeach

                            <div class="form-group row m-b-lg m-t-md required">
                                <label for="active" class="col-lg-3 col-sm-4 col-xs-7 col-form-label">{{ utrans('custom.active') }}:</label>
                                <div class="col-lg-2 col-sm-8 col-xs-5">
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
                                <label for="default" class="col-lg-3 col-sm-4 col-xs-7 col-form-label">{{ uctrans('custom.by_default') }}:</label>
                                <div class="col-lg-2 col-sm-8 col-xs-5">
                                    <div class="js-check">
                                        <input
                                            type="checkbox"
                                            name="default"
                                            value="1"
                                            {{ !empty($model->is_default) ? 'checked' : '' }}
                                        >
                                        @if (isset($errors) && $errors->has('is_default'))
                                            <span class="error">{{ $errors->first('is_default') }}</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row m-b-lg m-t-md">
                                <label for="order" class="col-lg-3 col-sm-4 col-form-label">{{ __('custom.ordering') }}:</label>
                                <div class="col-lg-2 col-sm-4">
                                    <input
                                        id="order"
                                        name="order"
                                        type="number"
                                        min="1"
                                        class="input-border-r-12 form-control"
                                        value="{{ !empty($model->ordering) ? $model->ordering : '' }}"
                                    >
                                    @if (isset($errors) && $errors->has('ordering'))
                                        <span class="error">{{ $errors->first('ordering') }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="col-sm-12 text-right">
                                    <a
                                        href="{{ url('admin/terms-of-use/list') }}"
                                        class="btn btn-primary"
                                    >
                                        {{ uctrans('custom.close') }}
                                    </a>
                                    <button type="submit" name="edit" value="1" class="m-l-md btn btn-custom">{{ utrans('custom.save') }}</button>
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
