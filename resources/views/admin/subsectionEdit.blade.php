@extends('layouts.app')

@section('content')
    <div class="container">
        @include('partials.alerts-bar')
        @include('partials.admin-nav-bar', ['view' => 'sections'])

        <div class="row m-t-lg">
            @if (!is_null($model))
                <div class="col-md-2 col-sm-1"></div>
                <div class="col-md-8 col-sm-10">
                    <div class="frame add-terms">
                        <div class="p-w-md text-center m-b-lg m-t-lg">
                            <h2>{{ __('custom.subsection_edit') }}</h2>
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
                                    @endif
                                @endforeach
                                <div class="form-group row">
                                    <label for="parent_id" class="col-sm-3 col-xs-12 col-form-label">{{ __('custom.main_topic') }}:</label>
                                    <div class="col-sm-9">
                                        <select
                                            id="parent_id"
                                            name="parent_id"
                                            class="js-select form-control"
                                            data-placeholder="{{ __('custom.select_main_topic') }}"
                                        >
                                            <option></option>
                                            @foreach ($sections as $id => $section)
                                                <option
                                                    value="{{ $section->id }}"
                                                    {{ $section->id == $model->parent_id ? 'selected' : '' }}
                                                >{{ isset($section->name) ? $section->name : '' }}</option>
                                            @endforeach
                                        </select>
                                        <span class="error">{{ $errors->first('parent_id') }}</span>
                                    </div>
                                </div>
                                <div class="form-group row m-b-lg m-t-md">
                                    <label for="active" class="col-sm-3 col-xs-12 col-form-label">{{ uctrans('custom.active') }}:</label>
                                    <div class="col-lg-9 col-md-9 col-sm-9 col-xs-12">
                                        <div class="js-check">
                                            <input
                                                type="checkbox"
                                                name="active"
                                                value="1"
                                                {{ !empty($model->active) ? 'checked' : '' }}
                                            >
                                            <span class="error">{{ $errors->first('active') }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group row m-b-lg m-t-md hidden">
                                    <label for="read_only" class="col-sm-3 col-xs-12 col-form-label">{{ __('custom.read_only') }}:</label>
                                    <div class="col-lg-9 col-md-9 col-sm-9 col-xs-12">
                                        <div class="js-check">
                                            <input
                                                type="checkbox"
                                                name="read_only"
                                                value="1"
                                                {{ !empty($model->read_only) ? 'checked' : '' }}
                                            >
                                            <span class="error">{{ $errors->first('read_only') }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group row m-b-lg m-t-md">
                                    <label for="forum_link" class="col-sm-3 col-xs-12 col-form-label">{{ __('custom.forum_link') }}:</label>
                                    <div class="col-sm-9">
                                        <input
                                            name="forum_link"
                                            class="input-border-r-12 form-control"
                                            value="{{ $model->forum_link }}"
                                        >
                                        @if (isset($errors) && $errors->has('forum_link'))
                                            <span class="error">{{ $errors->first('forum_link') }}</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="form-group row m-b-lg m-t-md">
                                    <label for="order" class="col-lg-3 col-form-label">{{ uctrans('custom.ordering') }}:</label>
                                    <div class="col-lg-2">
                                        <input
                                            id="order"
                                            name="ordering"
                                            type="number"
                                            min="1"
                                            class="input-border-r-12 form-control"
                                            value="{{ $model->ordering }}"
                                        >
                                        <span class="error">{{ $errors->first('ordering') }}</span>
                                    </div>
                                </div>
                                <div class="text-center m-b-lg terms-hr">
                                    <hr>
                                </div>
                                <div class="form-group row m-b-lg m-t-md">
                                    <label class="col-sm-6 col-xs-12 col-form-label">{{ __('custom.created_by') }}:</label>
                                    <div class="col-sm-6 col-xs-12">
                                        <div>{{ $model->created_by }}</div>
                                    </div>
                                </div>
                                <div class="form-group row m-b-lg m-t-md">
                                    <label class="col-sm-6 col-xs-12 col-form-label">{{ __('custom.created_at') }}:</label>
                                    <div class="col-sm-6 col-xs-12">
                                        <div>{{ $model->created_at }}</div>
                                    </div>
                                </div>
                                @if (!empty($model->updated_by))
                                    <div class="form-group row m-b-lg m-t-md">
                                        <label class="col-sm-6 col-xs-12 col-form-label">{{ __('custom.updated_by') }}:</label>
                                        <div class="col-sm-6 col-xs-12">
                                            <div>{{ $model->updated_by }}</div>
                                        </div>
                                    </div>
                                    <div class="form-group row m-b-lg m-t-md">
                                        <label class="col-sm-6 col-xs-12 col-form-label">{{ __('custom.updated_at') }}:</label>
                                        <div class="col-sm-6 col-xs-12">
                                            <div>{{ $model->updated_at }}</div>
                                        </div>
                                    </div>
                                @endif
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
            @else
                <div class="col-sm-12 m-t-xl text-center no-info">
                    {{ __('custom.no_info') }}
                </div>
            @endif
        </div>
    </div>
@endsection
