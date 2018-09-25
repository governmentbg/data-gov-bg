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
            @elseif($field['view'] == 'translation_custom')
                @include(
                    'components.form_groups.translation_custom_fields',
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

        <div
            class="form-group row required js-ress-file"
            {{ old('type') == \App\Resource::TYPE_FILE ? null : 'hidden' }}
        >
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

        <div
            class="form-group row required js-ress-url js-ress-api"
            {{ old('type') == \App\Resource::TYPE_HYPERLINK ? null : 'hidden' }}
        >
            <label for="url" class="col-sm-3 col-xs-12 col-form-label">{{ utrans('custom.url') }}:</label>
            <div class="col-sm-9">
                <input
                    id="url"
                    class="input-border-r-12 form-control"
                    name="resource_url"
                    type="text"
                    value="{{ old('resource_url') }}"
                >
                <span class="error">{{ $errors->first('resource_url') }}</span>
            </div>
        </div>

        <div class="js-ress-api " hidden>
            <div class="js-ress-api form-group row required">
                <label
                    for="rqtype"
                    class="col-sm-3 col-xs-12 col-form-label"
                >{{ uctrans('custom.request_type') }}:</label>
                <div class="col-sm-9">
                    <select
                        id="rqtype"
                        class="js-select input-border-r-12 form-control"
                        name="http_rq_type"
                    >
                        <option value=""> {{ uctrans('custom.request_type') }}</option>
                        @foreach ($reqTypes as $id => $rqType)
                            <option
                                value="{{ $rqType }}"
                                {{ $rqType == old('http_rq_type') ? 'selected' : '' }}
                            >{{ $rqType }}</option>
                        @endforeach
                    </select>
                    <span class="error">{{ $errors->first('http_rq_type') }}</span>
                </div>
            </div>
            <div class="js-ress-api form-group row">
                <label for="headers" class="col-sm-3 col-xs-12 col-form-label">{{ utrans('custom.headers') }}:</label>
                <div class="col-sm-9">
                    <textarea
                        id="headers"
                        class="input-border-r-12 form-control"
                        name="http_headers"
                    >{{ old('http_headers') }}</textarea>
                    <span class="error">{{ $errors->first('http_headers') }}</span>
                </div>
            </div>
            <div class="js-ress-api form-group row">
                <label for="request" class="col-sm-3 col-xs-12 col-form-label">{{ utrans('custom.request') }}:</label>
                <div class="col-sm-9">
                    <textarea
                        id="request"
                        class="input-border-r-12 form-control"
                        name="post_data"
                    >{{ old('post_data') }}</textarea>
                    <span class="error">{{ $errors->first('post_data') }}</span>
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
