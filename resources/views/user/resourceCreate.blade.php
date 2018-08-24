@extends('layouts.app')

@section('content')
<div class="container">
    @include('partials.alerts-bar')
    @include('partials.user-nav-bar', ['view' => Request::segment(2)])
    <div class="col-xs-12 m-t-lg">
        <p class="req-fields">{{ __('custom.all_fields_required') }}</p>
        <form method="POST" class="m-t-lg" enctype="multipart/form-data">
            {{ csrf_field() }}
            @foreach ($fields as $field)
                @if ($field['view'] == 'translation')
                    @include(
                        'components.form_groups.translation_input',
                        ['field' => $field, 'result' => session('result')]
                    )
                @elseif ($field['view'] == 'translation_txt')
                    @include(
                        'components.form_groups.translation_textarea',
                        ['field' => $field, 'result' => session('result')]
                    )
                @elseif ($field['view'] == 'translation_tags')
                    @include(
                        'components.form_groups.translation_tags',
                        ['field' => $field, 'result' => session('result')]
                    )
                @endif
            @endforeach
            <div class="form-group row required">
                <label for="type" class="col-sm-3 col-xs-12 col-form-label">{{ utrans('custom.type', 1) }}:</label>
                <div class="col-sm-9">
                    <select
                        id="type"
                        class="js-select js-ress-type input-border-r-12 form-control"
                        name="type"
                    >
                        <option value=""> {{ utrans('custom.type') }}</option>
                        @foreach ($types as $id => $type)
                            <option
                                value="{{ $id }}"
                                {{ $id == old('type') ? 'selected' : '' }}
                            >{{ $type }}</option>
                        @endforeach
                    </select>
                    <span class="error">{{ $errors->first('type') }}</span>
                </div>
            </div>

            <div class="form-group row required js-ress-file" hidden>
                <label for="file" class="col-sm-3 col-xs-12 col-form-label">{{ utrans('custom.file', 1) }}:</label>
                <div class="col-sm-9">
                    <input
                        id="file"
                        class="input-border-r-12 form-control"
                        name="file"
                        type="file"
                    >
                </div>
            </div>

            <div class="form-group row required js-ress-url js-ress-api" hidden>
                <label for="url" class="col-sm-3 col-xs-12 col-form-label">{{ utrans('custom.url') }}:</label>
                <div class="col-sm-9">
                    <input
                        id="url"
                        class="input-border-r-12 form-control"
                        name="resource_url"
                        type="text"
                        value="{{ old('url') }}"
                    >
                    <span class="error">{{ $errors->first('url') }}</span>
                </div>
            </div>

            <div class="js-ress-api" hidden>
                <div class="form-group row required">
                    <label for="rqtype" class="col-sm-3 col-xs-12 col-form-label">{{ uctrans('custom.request_type') }}:</label>
                    <div class="col-sm-9">
                        <select
                            id="rqtype"
                            class="js-select input-border-r-12 form-control"
                            name="rqtype"
                        >
                            <option value=""> {{ uctrans('custom.request_type') }}</option>
                            @foreach ($reqTypes as $id => $rqtype)
                                <option
                                    value="{{ $id }}"
                                    {{ $id == old('rqtype') ? 'selected' : '' }}
                                >{{ $rqtype }}</option>
                            @endforeach
                        </select>
                        <span class="error">{{ $errors->first('rqtype') }}</span>
                    </div>
                </div>
                <div class="form-group row required">
                    <label for="headers" class="col-sm-3 col-xs-12 col-form-label">{{ utrans('custom.headers') }}:</label>
                    <div class="col-sm-9">
                        <textarea
                            id="headers"
                            class="input-border-r-12 form-control"
                            name="headers"
                        >{{ old('headers') }}</textarea>
                        <span class="error">{{ $errors->first('headers') }}</span>
                    </div>
                </div>
                <div class="form-group row">
                    <label for="response_format" class="col-sm-3 col-xs-12 col-form-label">{{ uctrans('custom.response_format') }}:</label>
                    <div class="col-sm-9">
                        <input
                            id="response_format"
                            class="input-border-r-12 form-control"
                            name="response_format"
                            type="text"
                            value="{{ old('response_format') }}"
                        >
                        <span class="error">{{ $errors->first('authentication') }}</span>
                    </div>
                </div>
                <div class="form-group row required">
                    <label for="authentication" class="col-sm-3 col-xs-12 col-form-label">{{ utrans('custom.authentication') }}:</label>
                    <div class="col-sm-9">
                        <input
                            id="authentication"
                            class="input-border-r-12 form-control"
                            name="authentication"
                            type="text"
                            value="{{ old('authentication') }}"
                        >
                        <span class="error">{{ $errors->first('authentication') }}</span>
                    </div>
                </div>
                <div class="form-group row">
                    <label for="request" class="col-sm-3 col-xs-12 col-form-label">{{ utrans('custom.request') }}:</label>
                    <div class="col-sm-9">
                        <textarea
                            id="request"
                            class="input-border-r-12 form-control"
                            name="headers"
                        >{{ old('headers') }}</textarea>
                        <span class="error">{{ $errors->first('request') }}</span>
                    </div>
                </div>
            </div>

            <div class="form-group row">
                <label for="schema_desc" class="col-sm-3 col-xs-12 col-form-label">{{ utrans('custom.schema_description') }}:</label>
                <div class="col-sm-9">
                    <textarea
                        id="schema_desc"
                        class="input-border-r-12 form-control"
                        name="schema_description"
                    >{{ old('schema_description') }}</textarea>
                    <span class="error">{{ $errors->first('schema_description') }}</span>
                </div>
            </div>
            <div class="form-group row ">
                <label for="schema_url" class="col-sm-3 col-xs-12 col-form-label">{{ uctrans('custom.schema_url') }}:</label>
                <div class="col-sm-9">
                    <input
                        id="schema_url"
                        class="input-border-r-12 form-control"
                        name="schema_url"
                        type="text"
                        value="{{ old('schema_url') }}"
                    >
                    <span class="error">{{ $errors->first('schema_url') }}</span>
                </div>
            </div>
            <div class="form-group row">
                <div class="col-sm-12 text-right">
                    <button name="ready_metadata" type="submit" class="m-l-md btn btn-custom">{{ uctrans('custom.save') }}</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
