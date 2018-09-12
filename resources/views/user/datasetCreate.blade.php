@extends('layouts.app')

@section('content')
<div class="modal inmodal fade" id="addLicense" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="frame">
                <div class="p-w-md">
                    <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">{{ __('custom.close') }}</span></button>
                    <h2>{{ __('custom.license_add_req') }}</h2>
                </div>
                <div class="modal-body">
                    <div id="js-alert-success" class="alert alert-success" role="alert" hidden>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                        <p>{{ __('custom.terms_req_success') }}</p>
                    </div>
                    <div id="js-alert-danger" class="alert alert-danger" role="alert" hidden>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                        <p>{{ __('custom.terms_req_error') }}</p>
                    </div>
                    <form id="sendTermOfUseReq" method="POST" action="{{ url('/user/sendTermsOfUseReq') }}" class="m-t-lg">
                        {{ csrf_field() }}
                        <div class="form-group row required">
                            <label for="fname" class="col-sm-3 col-xs-12 col-form-label">{{ uctrans('custom.name') }}:</label>
                            <div class="col-sm-9">
                                <input
                                    id="fname"
                                    class="input-border-r-12 form-control"
                                    name="firstname"
                                    type="text"
                                    value="{{ \Auth::user()->firstname }}"
                                >
                            </div>
                        </div>
                        <div class="form-group row required">
                            <label for="lname" class="col-sm-3 col-xs-12 col-form-label">{{ __('custom.family_name') }}:</label>
                            <div class="col-sm-9">
                                <input
                                    id="lname"
                                    class="input-border-r-12 form-control"
                                    name="lastname"
                                    type="text"
                                    value="{{ \Auth::user()->lastname }}"
                                >
                            </div>
                        </div>
                        <div class="form-group row required">
                            <label for="email" class="col-sm-3 col-xs-12 col-form-label">{{ __('custom.e_mail') }}:</label>
                            <div class="col-sm-9">
                                <input
                                    id="email"
                                    class="input-border-r-12 form-control"
                                    name="email"
                                    type="email"
                                    value="{{ \Auth::user()->email }}"
                                >
                            </div>
                        </div>
                        <div class="form-group row required">
                            <label for="description" class="col-sm-3 col-xs-12 col-form-label">{{ __('custom.description') }}:</label>
                            <div class="col-sm-9">
                                <textarea
                                    id="description"
                                    class="input-border-r-12 form-control"
                                    name="description"
                                ></textarea>
                            </div>
                        </div>
                        <div class="form-group row">
                            <div class="col-sm-12 text-right">
                                <button type="button" class="m-l-md btn btn-danger" data-dismiss="modal">{{ uctrans('custom.close') }}</button>
                                <button type="submit" class="m-l-md btn btn-custom">{{ uctrans('custom.send') }}</button>
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
        <p class='req-fields'>{{ __('custom.all_fields_required') }}</p>
        <form method="POST" action="{{ url('/user/dataset/create') }}">
            {{ csrf_field() }}
            <div class="form-group row">
                <div class="col-xs-12 text-right mng-btns">
                @if ($buttons['add'])
                    <button
                        class="btn btn-primary"
                        name="add_resource"
                    >{{ uctrans('custom.add_resource') }}</button>
                @endif
                    <button type="submit" class="btn btn-primary">{{ uctrans('custom.save') }}</button>
                </div>
            </div>
            <div class="form-group row">
                <label for="identifier" class="col-sm-3 col-xs-12 col-form-label">{{ __('custom.unique_identificator') }}:</label>
                <div class="col-sm-9">
                    <input
                        id="identifier"
                        class="input-border-r-12 form-control"
                        name="uri"
                        value="{{ old('uri') }}"
                        type="text"
                        placeholder="Уникален идентификатор">
                    <span class="error">{{ $errors->first('uri') }}</span>
                </div>
            </div>
            <div class="form-group row required">
                <label for="theme" class="col-sm-3 col-xs-12 col-form-label">{{ __('custom.main_topic') }}:</label>
                <div class="col-sm-9">
                    <select
                        id="theme"
                        class="js-select form-control"
                        name="category_id"
                        data-placeholder="{{ __('custom.select_main_topic') }}"
                    >
                        <option></option>
                        @foreach ($categories as $id => $category)
                            <option
                                value="{{ $id }}"
                                {{ $id == old('category_id') ? 'selected' : '' }}
                            >{{ $category }}</option>
                        @endforeach
                    </select>
                    <span class="error">{{ $errors->first('category_id') }}</span>
                </div>
            </div>

            @foreach ($fields as $field)
                @if ($field['view'] == 'translation')
                    @include(
                        'components.form_groups.translation_input',
                        ['field' => $field]
                    )
                @elseif ($field['view'] == 'translation_txt')
                    @include(
                        'components.form_groups.translation_textarea',
                        ['field' => $field]
                    )
                @endif
            @endforeach

            @include('components.form_groups.tags')

            <div class="form-group row">
                <label for="termsOfuse" class="col-sm-3 col-xs-12 col-form-label">{{ __('custom.terms_and_conditions') }}:</label>
                <div class="col-sm-6">
                    <select
                        id="termsOfuse"
                        class="js-select form-control"
                        name="terms_of_use_id"
                    >
                        <option value="">{{ utrans('custom.select_terms_of_use') }}</option>
                        @foreach ($termsOfUse as $id =>$term)
                            <option
                                value="{{ $id }}"
                                {{ $id == old('terms_of_use_id') ? 'selected' : '' }}
                            >{{ $term }}</option>
                        @endforeach
                    </select>
                    <span class="error">{{ $errors->first('terms_of_use_id') }}</span>
                </div>
                <div class="col-sm-3 text-right add-terms">
                    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addLicense">{{ __('custom.new_terms_and_conditions') }}</button>
                </div>
            </div>
            <div class="form-group row">
                <label for="organisation" class="col-sm-3 col-xs-12 col-form-label">{{ utrans('custom.organisations', 1) }}:</label>
                <div class="col-sm-9">
                    <select
                        id="organisation"
                        class="js-autocomplete form-control"
                        name="org_id"
                    >
                        <option value="">{{ utrans('custom.select_org') }}</option>
                        @foreach ($organisations as $id =>$org)
                            <option
                                value="{{ $id }}"
                                {{ $id == old('org_id') ? 'selected' : '' }}
                            >{{ $org }}</option>
                        @endforeach
                    </select>
                    <span class="error">{{ $errors->first('org_id') }}</span>
                </div>
            </div>
            <div class="form-group row">
                <label for="group" class="col-sm-3 col-xs-12 col-form-label">{{ utrans('custom.groups', 1) }}:</label>
                <div class="col-sm-9">
                    <select
                        id="group"
                        class="js-autocomplete form-control"
                        name="group_id[]"
                        data-placeholder="{{ utrans('custom.groups', 1) }}"
                        multiple="multiple"
                    >
                        <option></option>
                        @foreach ($groups as $id =>$group)
                            <option
                                value="{{ $id }}"
                                {{ $id == old('group_id') ? 'selected' : '' }}
                            >{{ $group }}</option>
                        @endforeach
                    </select>
                    <span class="error">{{ $errors->first('group_id') }}</span>
                </div>
            </div>
            <div class="form-group row">
                <label for="visibility" class="col-sm-3 col-xs-12 col-form-label">{{ utrans('custom.visibility') }}:</label>
                <div class="col-sm-9">
                    <select
                        id="visibility"
                        class="js-select form-control"
                        name="visibility"
                        data-placeholder="{{ utrans('custom.select_visibility') }}"
                    >
                        <option></option>
                        @foreach ($visibilityOpt as $id => $visOpt)
                            <option
                                value="{{ $id }}"
                                {{ $id == old('visibility') ? 'selected' : '' }}
                            >{{ $visOpt }}</option>
                        @endforeach
                    </select>
                    <span class="error">{{ $errors->first('visibility') }}</span>
                </div>
            </div>
            <div class="form-group row">
                <label for="source" class="col-sm-3 col-xs-12 col-form-label">{{ utrans('custom.source') }}:</label>
                <div class="col-sm-9">
                    <input
                        id="source"
                        class="input-border-r-12 form-control"
                        name="source"
                        value="{{ old('source') }}"
                        type="text"
                        placeholder="Източник"
                    >
                    <span class="error">{{ $errors->first('source') }}</span>
                </div>
            </div>
            <div class="form-group row">
                <label for="version" class="col-sm-3 col-xs-12 col-form-label">{{ utrans('custom.version') }}:</label>
                <div class="col-sm-9">
                    <input
                        id="version"
                        class="input-border-r-12 form-control"
                        name="version"
                        value="{{ old('version') }}"
                        type="text"
                        placeholder="Версия"
                    >
                    <span class="error">{{ $errors->first('version') }}</span>
                </div>
            </div>
            <div class="form-group row">
                <label for="author" class="col-sm-3 col-xs-12 col-form-label">{{ utrans('custom.author') }}:</label>
                <div class="col-sm-9">
                    <input
                        id="author"
                        class="input-border-r-12 form-control"
                        name="author_name"
                        value="{{ old('author_name') }}"
                        type="text"
                        placeholder="Автор">
                    <span class="error">{{ $errors->first('author_name') }}</span>
                </div>
            </div>
            <div class="form-group row">
                <label for="author-email" class="col-sm-3 col-xs-12 col-form-label">{{ __('custom.author_email') }}:</label>
                <div class="col-sm-9">
                    <input
                        id="author-email"
                        class="input-border-r-12 form-control"
                        name="author_email"
                        value="{{ old('author_email') }}"
                        type="email"
                        placeholder="E-mail на автора"
                    >
                    <span class="error">{{ $errors->first('author_email') }}</span>
                </div>
            </div>
            <div class="form-group row">
                <label for="support" class="col-sm-3 col-xs-12 col-form-label">{{ utrans('custom.contacts') }}:</label>
                <div class="col-sm-9">
                    <input
                        id="support"
                        class="input-border-r-12 form-control"
                        name="support_name"
                        value="{{ old('support_name') }}"
                        type="text"
                        placeholder="Контакт"
                    >
                    <span class="error">{{ $errors->first('support_name') }}</span>
                </div>
            </div>
            <div class="form-group row">
                <label for="support-email" class="col-sm-3 col-xs-12 col-form-label">{{ __('custom.contact_email') }}:</label>
                <div class="col-sm-9">
                    <input
                        id="support-email"
                        class="input-border-r-12 form-control"
                        name="support_email"
                        value="{{ old('support_email') }}"
                        type="email"
                        placeholder="E-mail за контакти"
                    >
                    <span class="error">{{ $errors->first('support_email') }}</span>
                </div>
            </div>

            @foreach ($fields as $field)
                @if ($field['view'] == 'translation_custom')
                    @include(
                        'components.form_groups.translation_custom_fields',
                        ['field' => $field]
                    )
                @endif
            @endforeach

            <div class="form-group row">
                <div class="col-xs-12 text-right mng-btns">
                @if ($buttons['add'])
                    <button
                        class="btn btn-primary"
                        name="add_resource"
                    >{{ uctrans('custom.add_resource') }}</button>
                @endif
                    <button type="submit" class="btn btn-primary">{{ uctrans('custom.save') }}</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
