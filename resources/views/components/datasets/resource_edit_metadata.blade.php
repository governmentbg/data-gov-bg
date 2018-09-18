@php $root = empty($admin) ? 'user' : 'admin'; @endphp
<div class="col-xs-12 m-t-lg">
    <p class="req-fields">{{ __('custom.all_fields_required') }}</p>
    <form method="POST" class="m-t-lg">
        {{ csrf_field() }}
        @foreach ($fields as $field)
            @if ($field['view'] == 'translation')
                @include(
                    'components.form_groups.translation_input',
                    ['field' => $field, 'model' => $resource]
                )
            @elseif ($field['view'] == 'translation_txt')
                @include(
                    'components.form_groups.translation_textarea',
                    ['field' => $field, 'model' => $resource]
                )
            @endif
        @endforeach

        <div class="form-group row">
            <label for="schema_desc" class="col-sm-3 col-xs-12 col-form-label">{{ uctrans('custom.schema_description') }}:</label>
            <div class="col-sm-9">
                <textarea
                    id="schema_desc"
                    class="input-border-r-12 form-control"
                    name="schema_description"
                >{{ $resource->schema_descript }}</textarea>
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
                    value="{{ $resource->schema_url }}"
                >
                <span class="error">{{ $errors->first('schema_url') }}</span>
            </div>
        </div>
        <div class="form-group row">
            <label for="active" class="col-sm-3 col-xs-12 col-form-label">{{ uctrans('custom.reported') }}:</label>
            <div class="col-lg-9 col-md-9 col-sm-9 col-xs-12">
                <div class="js-check">
                    <input
                        type="checkbox"
                        name="reported"
                        value="1"
                        {{ $resource->is_reported ? 'checked' : '' }}
                    >
                </div>
            </div>
        </div>
        <div class="col-xs-12 text-right mng-btns">
            <a
                type="button"
                class="btn btn-primary"
                href="{{ url('/'. $root .'/resource/view/'. $resource->uri) }}"
            >{{ uctrans('custom.preview') }}</a>
            <button name="ready_metadata" type="submit" class="btn btn-custom">{{ uctrans('custom.save') }}</button>
        </div>
    </form>
</div>
