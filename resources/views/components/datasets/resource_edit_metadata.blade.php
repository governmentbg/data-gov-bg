<div class="{{ !empty($parent) ? 'col-sm-9' : 'col-sm-12' }} sidenav m-t-lg">
    <span class="my-profile m-l-sm">{{uctrans('custom.edit_resource')}}</span>
</div>
<div class="{{ !empty($parent) ? 'col-sm-9' : 'col-sm-12' }} m-t-lg">
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

        @if ($resource->resource_type == App\Resource::TYPE_HYPERLINK)
            <div class="form-group row required">
                <label for="type" class="col-sm-3 col-xs-12 col-form-label">{{ utrans('custom.type', 1) }}:</label>
                <div class="col-sm-9">
                    <select
                        id="type"
                        class="js-select js-ress-type input-border-r-12 form-control"
                        name="type"
                        disabled
                    >
                        <option value=""> {{ utrans('custom.type') }}</option>
                        @foreach ($types as $id => $type)
                            <option
                                value="{{ $id }}"
                                {{ $id == $resource->resource_type ? 'selected' : '' }}
                            >{{ $type }}</option>
                        @endforeach
                    </select>
                    <span class="error">{{ $errors->first('type') }}</span>
                </div>
            </div>
            <div class="form-group row required">
            <label for="url" class="col-sm-3 col-xs-12 col-form-label">{{ utrans('custom.url') }}:</label>
            <div class="col-sm-9">
                <input
                    id="url"
                    class="input-border-r-12 form-control"
                    name="resource_url"
                    type="text"
                    value="{{ $resource->resource_url }}"
                >
                <span class="error">{{ $errors->first('resource_url') }}</span>
            </div>
        </div>
        @endif
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

        @foreach ($fields as $field)
            @if ($field['view'] == 'translation_custom')
                @include(
                    'components.form_groups.translation_custom_fields',
                    ['field' => $field, 'model' => $custFields, 'result' => session('result')]
                )
            @endif
        @endforeach

        <div class="col-xs-12 text-right mng-btns p-l-r-none">
            @php
                $root = empty($admin) ? 'user' : 'admin';

                if (empty($parent)):
                    $url = url('/'. $root .'/resource/view/'. $resource->uri);
                    $href = route($root .'DatasetView', ['uri' => $dataseUri]);
                else:
                    if ($parent->type == App\Organisation::TYPE_GROUP):
                        $url = url('/'. $root .'/groups/'. $parent->uri .'/resource/'. $resource->uri);
                        $href = route($root .'GroupDatasetView', ['uri' => $dataseUri, 'grpUri' => $parent->uri]);
                    else:
                        $url = url('/'. $root .'/organisations/'. $parent->uri .'/resource/'. $resource->uri);
                        $href = route($root .'OrgDatasetView', ['uri' => $dataseUri]);
                    endif;
                endif;
            @endphp
            <a
                href="{{ $href }}"
                class="btn btn-primary"
            >{{ uctrans('custom.close') }}</a>
            <a
                type="button"
                class="btn btn-primary"
                href="{{ url($url) }}"
            >{{ uctrans('custom.preview') }}</a>
            <button name="ready_metadata" type="submit" class="btn btn-custom">{{ uctrans('custom.save') }}</button>
        </div>
    </form>
</div>
