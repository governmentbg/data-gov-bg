@php $root = empty($admin) ? 'user' : 'admin'; @endphp

    <div class="articles">
        <div class="col-xs-12 p-l-r-none">
            <h2 class="{{ $resource->reported ? 'error' : '' }}">{{ $resource->name }}</h2>
            <p>
                {{ utrans('custom.version_current') }}:&nbsp;{{ $resource->version }}
            </p>
            <p>
                {{ utrans('custom.version_displayed') }}:&nbsp;{{ $versionView }}
            </p>
            @if (!empty($dataset))
                <p>
                    <strong>{{ uctrans('custom.dataset') }}:</strong>&nbsp;
                    <a href="{{ url('/'. $root .'/dataset/view/'. $dataset['uri']) }}">
                        {{ $dataset['name'] }}
                    </a>
                </p>
            @endif
            @if (!empty($supportName))
                <p><strong>{{ __('custom.contact_support_name') }}:</strong>&nbsp;{{ $supportName }}</p>
            @endif
            @if (!empty($admin))
                <p>
                    <strong>{{ __('custom.id') }}:</strong>
                    &nbsp;{{ $resource->id }}
                </p>
            @endif
            <p>
                <strong>{{ uctrans('custom.unique_identificator') }}:</strong>
                &nbsp;{{ $resource->uri }}
            </p>
            @if (!empty($resource->type))
                <p><strong>{{ uctrans('custom.type') }}:</strong>&nbsp;{{ $resource->type }}</p>
            @endif
            @if (!empty($resource->file_format))
                <p><strong>{{ uctrans('custom.format') }}:</strong>&nbsp;{{ $resource->file_format }}</p>
            @endif
            @if (!empty($resource->resource_url))
                <p><strong>{{ uctrans('custom.url') }}:</strong></p>
                <div class="m-b-sm">
                    <a href="{{ $resource->resource_url }}">{{ $resource->resource_url }}</a>
                </div>
            @endif

            @if (!empty($resource->http_rq_type))
                <p><strong>{{ uctrans('custom.request_type') }}:</strong>&nbsp;{{ $resource->http_rq_type }}</p>
            @endif
            @if (!empty($resource->post_data))
                <p><strong>{{ uctrans('custom.request') }}:</strong></p>
                <div class="m-b-sm">
                    {{ $resource->post_data }}
                </div>
            @endif
            @if (!empty($resource->http_headers))
                <p><strong>{{ uctrans('custom.headers') }}:</strong></p>
                <div class="m-b-sm">
                    {{ $resource->http_headers }}
                </div>
            @endif

            <p><strong>{{ uctrans('custom.description') }}:</strong></p>
            @if (!empty($resource->description))
                <div class="m-b-sm">
                    {!! nl2br(e($resource->description)) !!}
                </div>
            @endif
            @if (!empty($resource->schema_description))
                <p><strong>{{ uctrans('custom.schema_description') }}:</strong></p>
                <div class="m-b-sm">
                    {{ $resource->schema_description }}
                </div>
            @endif
            @if (!empty($resource->schema_url))
                <p><strong>{{ uctrans('custom.schema_url') }}:</strong></p>
                <div class="m-b-sm">
                    {{ $resource->schema_url }}
                </div>
            @endif
            @if (!empty($resource->upl_freq_type))
                <p>
                    <strong>{{ __('custom.type_upl_freq') }}:</strong>
                    &nbsp;{{ App\DataQuery::getFreqTypes()[$resource->upl_freq_type] }}
                </p>
            @endif
            @if (!empty($resource->upl_freq))
                <p>
                    <strong>{{ __('custom.value_upl_freq') }}:</strong>
                    &nbsp;{{ $resource->upl_freq }}
                </p>
            @endif
            @if (!empty($resource->custom_settings))
                <p><b>{{ __('custom.additional_fields') }}:</b></p>
                @foreach ($resource->custom_settings as $field)
                    <div class="row m-b-lg">
                        <div class="col-xs-6">{{ $field->key }}</div>
                        <div class="col-xs-6 text-left">{{ $field->value }}</div>
                    </div>
                @endforeach
            @endif
        </div>
        <div class="info-bar-sm col-sm-7 col-xs-12 p-l-r-none">
            <ul class="p-l-r-none">
                <li>{{ __('custom.created_at') }}: {{ $resource->created_at }}</li>
                <li>{{ __('custom.created_by') }}: {{ $resource->created_by }}</li>
                @if (!empty($resource->updated_by))
                    <li>{{ __('custom.updated_at') }}: {{ $resource->updated_at }}</li>
                    <li>{{ __('custom.updated_by') }}: {{ $resource->updated_by }}</li>
                @endif
            </ul>
        </div>
        @if ($resource->resource_type !== \App\Resource::TYPE_HYPERLINK)
            <div class="col-xs-12 m-t-lg p-l-r-none">
                @include('partials.resource-visualisation')
            </div>
        @endif
        <div class="col-xs-12 p-l-r-none text-left m-t-sm">
            <form method="POST">
                {{ csrf_field() }}
                @if ($resource->type != App\Resource::getTypes()[App\Resource::TYPE_HYPERLINK])
                    <button
                        type="button"
                        class="btn btn-primary js-res-uri m-b-sm"
                        data-toggle="modal"
                        data-target="#embed-resource"
                        data-uri ="{{ $resource->uri }}"
                    >{{ uctrans('custom.embed') }}</button>
                    @if (
                        ($resource->version == $versionView)
                        && (!empty($admin) || !empty($buttons['edit']))
                    )
                        <a
                            class="btn btn-primary m-b-sm"
                            @if (!empty($fromOrg))
                                href="{{ url('/'. $root .'/organisations/resource/update/'. $resource->uri) .'/'. $fromOrg->uri }}"
                            @elseif (!empty($group))
                                href="{{ url('/'. $root .'/groups/resource/update/'. $resource->uri) .'/'. $group->uri }}"
                            @else
                                href="{{ url('/'. $root .'/resource/update/'. $resource->uri) }}"
                            @endif
                        >
                            {{ uctrans('custom.update') }}
                        </a>
                    @endif
                @endif
                @if (!empty($admin) || !empty($buttons['edit']))
                <a
                    class="btn btn-primary m-b-sm"
                    @if (!empty($fromOrg))
                        href="{{ url('/'. $root .'/organisations/resource/edit/'. $resource->uri) .'/'. $fromOrg->uri }}"
                    @elseif (!empty($group))
                        href="{{ url('/'. $root .'/groups/resource/edit/'. $resource->uri) .'/'. $group->uri }}"
                    @else
                        href="{{ url('/'. $root .'/resource/edit/'. $resource->uri) }}"
                    @endif
                >
                    {{ uctrans('custom.edit') }}
                </a>
                @endif
                <a
                    @if (isset($group))
                        href="{{ url('/'. $root .'/groups/'. $group->uri .'/dataset/'. $resource->dataset_uri) }}"
                    @elseif (isset($fromOrg))
                        href="{{ url('/'. $root .'/organisations/dataset/view/'. $resource->dataset_uri) }}"
                    @else
                        href="{{ url('/'. $root .'/dataset/view/'. $resource->dataset_uri) }}"
                    @endif
                    class="btn btn-primary m-b-sm"
                >
                    {{ uctrans('custom.close') }}
                </a>
                @if (!empty($admin) || !empty($buttons['delete']))
                    <button
                        name="delete"
                        class="btn del-btn btn-primary m-b-sm"
                        data-confirm="{{ __('custom.remove_data') }}"
                    >{{ uctrans('custom.remove') }}</button>
                @endif
            </form>
        </div>

        <!-- IF there are old versions of this article -->
        @if (!empty($resource->versions_list))
            <div class="col-xs-12 pull-left m-t-md p-l-r-none">
                <div class="pull-left history">
                    @foreach ($resource->versions_list as $version)
                    <div>
                        <a
                            @if (!empty($fromOrg))
                                href="{{ url('/'. $root .'/organisations/'. $fromOrg->uri .'/resource/'. $resource->uri .'/'. $version)}}"
                            @elseif (!empty($group))
                                href="{{ url('/'. $root .'/groups/'. $group->uri .'/resource/'. $resource->uri .'/'. $version)}}"
                            @else
                                href="{{ url('/'. $root .'/resource/view/'. $resource->uri .'/'. $version) }}"
                            @endif
                        >
                            <span class="version-heading">{{ uctrans('custom.version') }}</span>
                            <span class="version">&nbsp;&#8211;&nbsp;{{ $version }}</span>
                        </a>
                    </div>
                    @endforeach
                </div>
            </div>
        @endif
        @include('components.signal-box', ['signals' => $resource->signals])
    </div>

@include('partials.resource-embed')
