@php $root = empty($admin) ? 'user' : 'admin'; @endphp
<div class="col-xs-12 m-t-md">
    <div class="articles">
        <div class="col-sm-12 p-l-none">
            <h2 class="{{ $resource->reported ? 'error' : '' }}">{{ $resource->name }}</h2>
            <p>
                {{ utrans('custom.version_current') }}:&nbsp;{{ $resource->version }}
            </p>
            <p>
                {{ utrans('custom.version_displayed') }}:&nbsp;{{ $versionView }}
            </p>
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
                    {{ $resource->resource_url }}
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

            @if (!empty($resource->description))
                <p><strong>{{ uctrans('custom.description') }}:</strong></p>
                <div class="m-b-sm">
                    {{ $resource->description }}
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
        </div>
        <div class="info-bar-sm col-sm-7 col-xs-12 p-l-none">
            <ul class="p-l-none">
                <li>{{ __('custom.created_at') }}: {{ $resource->created_at }}</li>
                <li>{{ __('custom.created_by') }}: {{ $resource->created_by }}</li>
                @if (!empty($resource->updated_by))
                    <li>{{ __('custom.updated_at') }}: {{ $resource->updated_at }}</li>
                    <li>{{ __('custom.updated_by') }}: {{ $resource->updated_by }}</li>
                @endif
            </ul>
        </div>
        <div class="col-sm-12 m-t-lg p-l-r-none">
            @include('partials.resource-visualisation')
        </div>
        <div class="col-sm-12 p-l-none">
            <div class="col-sm-12 text-left">
                @if (!empty($admin) || !empty($buttons[$resource->uri]['delete']))
                    <form method="POST">
                        {{ csrf_field() }}
                        <button
                            type="button"
                            class="btn btn-primary js-res-uri"
                            data-toggle="modal"
                            data-target="#embed-resource"
                            data-uri ="{{ $resource->uri }}"
                        >{{ uctrans('custom.embed') }}</button>
                        @if ($resource->version == $versionView)
                            <a
                                class="btn btn-primary"
                                href="{{ url('/'. $root .'/resource/update/'. $resource->uri) }}"
                            >{{ uctrans('custom.update') }}</a>
                        @endif
                        <a
                            class="btn btn-primary"
                            href="{{ url('/'. $root .'/resource/edit/'. $resource->uri) }}"
                        >{{ uctrans('custom.edit') }}</a>
                        <button
                            name="delete"
                            class="btn del-btn btn-primary"
                            data-confirm="{{ __('custom.remove_data') }}"
                        >{{ uctrans('custom.remove') }}</button>
                    </form>
                @endif
            </div>
        </div>

        <!-- IF there are old versions of this article -->
        @if (!empty($versions))
            <div class="col-sm-12 pull-left m-t-md p-l-none">
                <div class="pull-left history">
                    @foreach ($versions as $version)
                    <div>
                        <a href="{{ url('/'. $root .'/resource/view/'. $resource->uri .'/'. $version) }}">
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
</div>
@include('partials.resource-embed')
