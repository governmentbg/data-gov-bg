@extends('layouts.app')

@section('content')
<div class="container admin">
    @include('partials.alerts-bar')
    @include('partials.admin-nav-bar', ['view' => 'help'])
    <div class="row">
        <form method="POST" class="form-horizontal">
            {{ csrf_field() }}
            <div class="col-lg-2"></div>
            <div class="col-lg-8 frame section-edit">
                <h3 class="text-center">{{ empty($section->parent_id) ? uctrans('custom.edit_help_sections') : uctrans('custom.edit_help_subsections') }}</h3>
                <div class="form-group row m-t-md required">
                    <label for="name" class="col-sm-3 col-xs-12 col-form-label">{{ uctrans('custom.unique_identificator') }}:</label>
                    <div class="col-lg-9 col-md-9 col-sm-9 col-xs-12">
                        <input
                            class="input-border-r-12 form-control"
                            name="name"
                            id="name"
                            value="{{ $section->name }}"
                        >
                        <span class="error">{{ $errors->first('name') }}</span>
                    </div>
                </div>
                <div class="m-t-lg">
                    @foreach ($fields as $field)
                        @if ($field['view'] == 'translation')
                            @include(
                                'components.form_groups.translation_input',
                                ['field' => $field, 'model' => $section]
                            )
                        @endif
                    @endforeach
                </div>
                <div class="form-group row m-b-md m-t-md">
                    <label for="parent" class="col-sm-3 col-xs-12 col-form-label">{{ uctrans('custom.parent_section') }}:</label>
                    <div class="col-lg-9 col-md-9 col-sm-9 col-xs-12">
                            <select
                                class="js-select"
                                name="parent"
                                id="parent"
                                data-placeholder="{{ __('custom.select') }}"
                            >
                                <option><option>
                                <option value="0"><option>
                                @foreach ($parents as $parent)
                                    <option
                                        value="{{ $parent->id }}"
                                        {{ $section->parent_id == $parent->id ? 'selected' : '' }}
                                    >{{ $parent->name }}</option>
                                @endforeach
                            </select>
                            <span class="error">{{ $errors->first('parent') }}</span>
                    </div>
                </div>
                <div class="form-group row m-b-md m-t-md">
                    <label for="active" class="col-sm-3 col-xs-12 col-form-label">{{ uctrans('custom.active') }}:</label>
                    <div class="col-lg-9 col-md-9 col-sm-9 col-xs-12">
                        <div class="js-check">
                            <input
                                type="checkbox"
                                name="active"
                                id="active"
                                value="1"
                                {{ $section->active ? 'checked' : '' }}
                            >
                            <span class="error">{{ $errors->first('active') }}</span>
                        </div>
                    </div>
                </div>
                <div class="form-group row m-b-md m-t-md required">
                    <label for="ordering" class="col-sm-3 col-xs-12 col-form-label">{{ uctrans('custom.ordering') }}:</label>
                    <div class="col-lg-9 col-md-9 col-sm-9 col-xs-12">
                        <select
                            class="js-select"
                            id="ordering"
                            name="ordering"
                        >
                            @foreach($ordering as $id => $name)
                                <option
                                    value="{{ $id }}"
                                    {{
                                        (!empty(old('ordering')) && old('ordering') == $id) || $section->ordering == $id
                                            ? 'selected'
                                            : ''
                                    }}
                                >{{ $name }}</option>
                            @endforeach
                        </select>
                        <span class="error">{{ $errors->first('ordering') }}</span>
                    </div>
                </div>
                <div class="text-center m-b-lg terms-hr">
                    <hr>
                </div>
                <div class="form-group row m-b-lg m-t-md">
                    <label class="col-sm-6 col-xs-12 col-form-label">{{ __('custom.created_by') }}:</label>
                    <div class="col-sm-6 col-xs-12">
                        <div>{{ $section->created_by }}</div>
                    </div>
                </div>
                <div class="form-group row m-b-lg m-t-md">
                    <label class="col-sm-6 col-xs-12 col-form-label">{{ __('custom.created_at') }}:</label>
                    <div class="col-sm-6 col-xs-12">
                        <div>{{ $section->created_at }}</div>
                    </div>
                </div>
                @if (!empty($section->updated_by))
                    <div class="form-group row m-b-lg m-t-md">
                        <label class="col-sm-6 col-xs-12 col-form-label">{{ __('custom.updated_by') }}:</label>
                        <div class="col-sm-6 col-xs-12">
                            <div>{{ $section->updated_by }}</div>
                        </div>
                    </div>
                    <div class="form-group row m-b-lg m-t-md">
                        <label class="col-sm-6 col-xs-12 col-form-label">{{ __('custom.updated_at') }}:</label>
                        <div class="col-sm-6 col-xs-12">
                            <div>{{ $section->updated_at }}</div>
                        </div>
                    </div>
                @endif
                <div class="form-group row">
                    <div class="col-sm-12 text-right">
                        <button
                            type="submit"
                            name="save"
                            class="btn btn-primary"
                        >{{ uctrans('custom.save') }}</button>
                        <a
                            href="{{ url('/admin/help/section/delete/'. $section->id) }}"
                            class="m-l-md btn btn-custom del-btn"
                        >{{ __('custom.delete') }}</a>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
