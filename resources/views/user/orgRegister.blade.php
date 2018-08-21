@extends('layouts.app')

@section('content')
<div class="container">
    @include('partials.alerts-bar')
    @include('partials.user-nav-bar', ['view' => 'organisation'])
    <div class="col-xs-12 m-t-md">
        <div>
            <h2>{{ __('custom.org_registration') }}</h2>
            <p class='req-fields m-t-lg m-b-lg'>{{ __('custom.all_fields_required') }}</p>
        </div>
        <form method="POST" class="m-t-lg" enctype="multipart/form-data">
            {{ csrf_field() }}
            <div class="form-group row {{ isset(session('result')->errors->logo) ? 'has-error' : '' }}">
                <label class="col-sm-3 col-xs-12 col-form-label">{{ __('custom.image') }}:</label>
                <div class="col-sm-9">
                    <div class="fileinput-new thumbnai form-control input-border-r-12 m-r-md">
                        <img class="preview js-preview hidden" src="#" alt="organisation logo" />
                    </div>
                    <div class="inline-block">
                        <span class="badge badge-pill"><label class="js-logo" for="logo">{{ __('custom.select_image') }}</label></span>
                        <input class="hidden js-logo-input" type="file" name="logo">
                    </div>
                    <div class="error">{{ $errors->first('logo') }}</div>
                </div>
            </div>
            <div class="form-group row {{ isset(session('result')->errors->parent_org_id) ? 'has-error' : '' }}">
                <label for="baseOrg" class="col-sm-3 col-xs-12 col-form-label">{{ __('custom.main_organisation') }}:</label>
                <div class="col-sm-9">
                    <select
                        class="input-border-r-12 form-control js-autocomplete"
                        name="parent_org_id"
                        id="filter"
                        data-live-search="true"
                    >
                        @if (isset($parentOrgs[0]))
                            <option value="">&nbsp;</option>
                            @foreach ($parentOrgs as $parent)
                                <option
                                    value="{{ $parent->id }}"
                                    {{ $parent->id == old('parent_org_id')
                                        ? ' selected'
                                        : ''
                                    }}
                                >{{ $parent->name }}</option>
                            @endforeach
                        @else
                            <option value="" selected >{{ __('custom.no_info') }}</option>
                        @endif
                    </select>
                    @if (isset($errors) && $errors->has('parent_org_id'))
                        <span class="error">{{ $errors->first('parent_org_id') }}</span>
                    @endif
                </div>
            </div>
            <div class="form-group row {{ isset(session('result')->errors->uri) ? 'has-error' : '' }}">
                <label for="uri" class="col-sm-3 col-xs-12 col-form-label">{{ __('custom.unique_identificator') }}:</label>
                <div class="col-sm-9">
                    <input
                        type="text"
                        class="input-border-r-12 form-control"
                        name="uri"
                        value="{{ old('uri') }}"
                    >
                    @if (isset($errors) && $errors->has('uri'))
                        <span class="error">{{ $errors->first('uri') }}</span>
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
                @elseif($field['view'] == 'translation_custom')
                    @include(
                        'components.form_groups.translation_custom_fields',
                        ['field' => $field, 'result' => session('result')]
                    )
                @endif
            @endforeach
            <div class="form-group row {{ !empty($errors->type) ? 'has-error' : '' }} required">
                <label for="type" class="col-lg-3 col-sm-3 col-xs-12 col-form-label">{{ __('custom.type') }}:</label>
                @foreach (\App\Organisation::getPublicTypes() as $id => $name)
                    <div class="col-md-4 col-xs-12 m-b-md">
                        <label class="radio-label">
                            {{ utrans($name) }}
                            <div class="js-check">
                                <input
                                    type="radio"
                                    name="type"
                                    value="{{ $id }}"
                                    @if (!empty(old('type')) && old('type') == $id)
                                        {{ 'checked' }}
                                    @elseif (empty(old('type')) && $id == \App\Organisation::TYPE_CIVILIAN)
                                        {{ 'checked' }}
                                    @endif
                                >
                            </div>
                        </label>
                    </div>
                @endforeach
                @if (isset($errors) && $errors->has('type'))
                    <div class="row">
                        <div class="col-xs-12 m-l-md">
                            <span class="error">{{ $errors->first('type') }}</span>
                        </div>
                    </div>
                @endif
            </div>
            <div class="form-group row">
                <label for="active" class="col-sm-3 col-xs-12 col-form-label">{{ __('custom.active') }}:</label>
                <div class="col-lg-9 col-md-9 col-sm-9 col-xs-12">
                    <div class="js-check">
                        <input
                            type="checkbox"
                            name="active"
                            value="{{ old('active') != null ? old('active') : '1' }}"
                            {{ !empty(old('active')) ? 'checked' : '' }}
                        >
                    </div>
                </div>
            </div>
            <div class="form-group row">
                <div class="col-sm-12 text-right">
                    <button type="submit" class="m-l-md btn btn-primary">{{ __('custom.save') }}</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
