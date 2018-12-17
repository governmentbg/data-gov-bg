<div class="{{ !empty($parent) ? 'col-sm-9' : 'col-sm-12' }} sidenav m-t-lg">
    <span class="my-profile m-l-sm">{{uctrans('custom.update_resource')}}</span>
</div>
<div class="{{ !empty($parent) ? 'col-sm-9' : 'col-sm-12' }} m-t-lg">
    <p class="req-fields">{{ __('custom.all_fields_required') }}</p>
    <form method="POST" class="m-t-lg" enctype="multipart/form-data">
        {{ csrf_field() }}
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

        <div
            class="form-group row required js-ress-file"
            {{
                ($resource->resource_type == \App\Resource::TYPE_FILE)
                || ($resource->resource_type == \App\Resource::TYPE_AUTO)
                ? null : 'hidden'
            }}
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
            {{
                ($resource->resource_type == \App\Resource::TYPE_HYPERLINK)
                || ($resource->resource_type == \App\Resource::TYPE_API)
                ? null : 'hidden'
            }}
        >
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

        <div
            class="js-ress-api"
            {{ ($resource->resource_type == \App\Resource::TYPE_API) ? null : 'hidden' }}
        >
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
                                {{ $id == $resource->http_rq_type ? 'selected' : '' }}
                            >{{ $rqType }}</option>
                        @endforeach
                    </select>
                    <span class="error">{{ $errors->first('http_rq_type') }}</span>
                </div>
            </div>

            <div class="js-ress-api form-group row">
                <label
                    class="col-sm-3 col-xs-12 col-form-label"
                >{{ __('custom.type_upl_freq') }}:</label>
                <div class="col-sm-9">
                    <select
                        class="js-select input-border-r-12 form-control"
                        name="upl_freq_type"
                    >
                        <option value="">{{ __('custom.type_upl_freq') }}</option>
                        @foreach (App\DataQuery::getFreqTypes() as $id => $freqType)
                            <option
                                value="{{ $id }}"
                                {{ $id == $resource->upl_freq_type ? 'selected' : '' }}
                            >{{ $freqType }}</option>
                        @endforeach
                    </select>
                    <span class="error">{{ $errors->first('upl_freq_type') }}</span>
                </div>
            </div>

            <div class="form-group row js-ress-api">
                <label class="col-sm-3 col-xs-12 col-form-label">{{ __('custom.value_upl_freq') }}:</label>
                <div class="col-sm-9">
                    <input
                        class="input-border-r-12 form-control"
                        name="upl_freq"
                        type="number"
                        value="{{ $resource->upl_freq }}"
                    >
                    <span class="error">{{ $errors->first('upl_freq') }}</span>
                </div>
            </div>

            <div class="js-ress-api form-group row">
                <label for="headers" class="col-sm-3 col-xs-12 col-form-label">{{ utrans('custom.headers') }}:</label>
                <div class="col-sm-9">
                    <textarea
                        id="headers"
                        class="input-border-r-12 form-control"
                        name="http_headers"
                    >{{ $resource->http_headers }}</textarea>
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
                    >{{ $resource->post_data }}</textarea>
                    <span class="error">{{ $errors->first('post_data') }}</span>
                </div>
            </div>
        </div>

        <div class="form-group row">
            <div class="col-sm-12 text-right">
                @php
                    $root = empty($admin) ? 'user' : 'admin';

                    if (empty($parent)):
                        $url = '/'. $root .'/resource/view/'. $resource->uri;
                        $href = route($root .'DatasetView', ['uri' => $dataseUri]);
                    else:
                        if ($parent->type == App\Organisation::TYPE_GROUP):
                            $url = '/'. $root .'/groups/'. $parent->uri .'/resource/'. $resource->uri;
                            $href = route($root .'GroupDatasetView', ['uri' => $dataseUri, 'grpUri' => $parent->uri]);
                        else:
                            $url = '/'. $root .'/organisations/'. $parent->uri .'/resource/'. $resource->uri;
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
                <button name="ready_metadata" type="submit" class="m-l-md btn btn-custom">{{ uctrans('custom.save') }}</button>
            </div>
        </div>
    </form>
</div>
