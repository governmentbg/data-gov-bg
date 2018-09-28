@if (!empty($resource))
    <div class="row">
        <div class="col-sm-9 col-xs-11 page-content col-sm-offset-3">
            <div class="articles">
                <div class="article col-xs-12 p-l-none">
                    <div>
                    @if (!empty($organisation))
                        <div class="col-sm-7 col-xs-12 p-l-r-none m-t-lg m-b-md">
                            <div class="col-xs-6 logo-img">
                                <a href="{{ url('/organisation/profile/'. $organisation->uri) }}" title="{{ $organisation->name }}">
                                    <img class="img-responsive" src="{{ $organisation->logo }}" alt="{{ $organisation->name }}">
                                </a>
                            </div>
                        </div>
                        <div class="col-sm-12 col-xs-12 p-l-r-none">
                            <h3>
                                <a href="{{ url('/organisation/profile/'. $organisation->uri) }}">{{ $organisation->name }}</a>
                            </h3>
                        </div>
                    @else
                        <div class="col-sm-12 col-xs-12 p-l-r-none">
                            <div class="pull-left">
                                <h2>
                                    @if (!empty($user))
                                        {{ utrans('custom.author') }}:
                                        <a href="{{ url('/user/profile/'. $user->id) }}">
                                            {{ ($user->firstname || $user->lastname) ? trim($user->firstname .' '. $user->lastname) : $user->username }}
                                        </a>
                                    @elseif (!empty($dataset->created_by))
                                        {{ utrans('custom.author') }}:
                                        <span>{{ $dataset->created_by }}</span>
                                    @endif
                                </h2>
                            </div>
                        </div>
                    @endif
                    </div>
                    <div class="col-xs-12 p-l-none">
                        <h3>
                            {{ uctrans('custom.dataset') }}:&nbsp;
                            <a href="{{ url($rootUrl .'/'. $dataset->uri) }}">{{ $dataset->name }}</a>
                        </h3>
                    </div>
                    <div class="col-xs-12 m-t-md">
                        <div class="art-heading-bar row">
                            <div class="col-sm-12 p-l-none">
                                @include('partials.social-icons', ['shareUrl' => url()->current()])
                                @if ($approved)
                                    <div class="status p-w-sm m-l-sm">
                                        <span>{{ __('custom.approved') }} </span>
                                    </div>
                                @else
                                    <div class="status notApproved p-w-sm m-l-sm">
                                        <span>{{ __('custom.unapproved') }}</span>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="col-xs-12 p-l-r-none">
                        <h2>{{ $resource->name }}</h2>
                        <p>
                            <strong>{{ __('custom.unique_identificator') }}:</strong>
                            &nbsp;{{ $resource->uri }}
                        </p>
                        @if (!empty($resource->description))
                            <p><strong>{{ __('custom.description') }}:</strong></p>
                            <p>{!! nl2br(e($resource->description)) !!}</p>
                        @endif
                        <div class="col-sm-12 p-l-none">
                            <div class="pull-left m-b-md">
                                @if (isset($dataset->tags) && count($dataset->tags) > 0)
                                    @foreach ($dataset->tags as $tag)
                                        <span class="badge badge-pill">{{ $tag->name }}</span>
                                    @endforeach
                                @endif
                            </div>
                        </div>
                        <p>
                            <strong>{{ uctrans('custom.version_current') }}:</strong>&nbsp;{{ $resource->version }}
                        </p>
                        <p>
                            <strong>{{ uctrans('custom.version_displayed') }}:</strong>&nbsp;{{ $versionView }}
                        </p>
                        @if (!empty($resource->file_format))
                            <p><strong>{{ uctrans('custom.format') }}:</strong>&nbsp;{{ $resource->file_format }}</p>
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
                        @if (isset($resource->custom_settings[0]) && !empty($resource->custom_settings[0]->key))
                            <p><b>{{ __('custom.additional_fields') }}:</b></p>
                            @foreach ($resource->custom_settings as $field)
                                <div class="row m-b-lg">
                                    <div class="col-xs-6">{{ $field->key }}</div>
                                    <div class="col-xs-6 text-left">{{ $field->value }}</div>
                                </div>
                            @endforeach
                        @endif
                        <div class="info-bar-sm col-sm-12 col-xs-12 p-l-none p-h-sm">
                            <ul class="p-l-none">
                                <li>{{ __('custom.created_at') }}: {{ $resource->created_at }}</li>
                                @if (!empty($resource->created_by))
                                    <li>{{ __('custom.created_by') }}: {{ $resource->created_by }}</li>
                                @endif
                                @if (!empty($resource->updated_at))
                                    <li>{{ __('custom.updated_at') }}: {{ $resource->updated_at }}</li>
                                @endif
                                @if (!empty($resource->updated_by))
                                    <li>{{ __('custom.updated_by') }}: {{ $resource->updated_by }}</li>
                                @endif
                            </ul>
                        </div>
                        <div class="col-sm-12 p-l-none">
                            <div class="tags"></div>
                        </div>
                    </div>
                </div>

                <div class="col-sm-12 m-t-lg p-l-none">
                    @include('partials.resource-visualisation')
                </div>

                <div class="col-sm-12 m-t-lg p-l-r-none p-h-sm m-b-md">
                    <div class="col-sm-8 text-left p-l-r-none">
                        <form method="POST">
                            {{ csrf_field() }}
                            @if ($resource->type != App\Resource::getTypes()[App\Resource::TYPE_HYPERLINK])
                                <button
                                    type="button"
                                    class="btn btn-primary badge badge-pill js-res-uri"
                                    data-toggle="modal"
                                    data-target="#embed-resource"
                                    data-uri ="{{ $resource->uri }}"
                                >{{ uctrans('custom.embed') }}</button>
                                @if ($resource->version == $versionView)
                                    @if (isset($buttons['update']) && $buttons['update'])
                                        <a
                                            class="btn btn-primary badge badge-pill"
                                            href="{{ url('/'. $buttons['rootUrl'] .'/resource/update/'. $resource->uri) }}"
                                        >{{ uctrans('custom.update') }}</a>
                                    @endif
                                @endif
                            @endif
                            @if (isset($buttons['edit']) && $buttons['edit'])
                                <a
                                    class="btn btn-primary badge badge-pill"
                                    href="{{ url(
                                        '/'. $buttons['rootUrl'] .'/resource/edit/'. $resource->uri .
                                        (isset($buttons['parentUri']) ? '/'. $buttons['parentUri'] : '')
                                    ) }}"
                                >{{ uctrans('custom.edit') }}</a>
                            @endif
                            @if (isset($buttons['delete']) && $buttons['delete'])
                                <button
                                    name="delete"
                                    class="btn del-btn btn-primary badge badge-pill"
                                    data-confirm="{{ __('custom.remove_data') }}"
                                >{{ uctrans('custom.remove') }}</button>
                            @endif
                        </form>
                    </div>
                    <div class="col-sm-4 text-right p-l-none">
                        <button type="button" class="badge badge-pill m-b-sm" data-toggle="modal" data-target="#addSignal">{{ __('custom.signal') }}</button>
                    </div>
                </div>

                <!-- IF there are old versions of this article -->
                @if (!empty($resource->versions_list))
                    <div class="col-sm-12 pull-left p-l-none m-b-md">
                        <div class="pull-left history">
                            @foreach ($resource->versions_list as $version)
                                @if ($version != $versionView)
                                    <div>
                                        <a href="{{ route($routeName, array_merge(app('request')->input(), ['uri' => $resource->uri, 'version' => $version])) }}">
                                            <span class="version-heading">{{ uctrans('custom.version') }}</span>
                                            <span class="version">&nbsp;&#8211;&nbsp;{{ $version }}</span>
                                        </a>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                @endif

                <div class="article col-xs-12 m-t-md m-b-md">
                    <div class="col-sm-12 p-l-none">
                        <!-- signals -->
                        @if (!empty($resource->signals))
                            @foreach ($resource->signals as $signal)
                                <div class="col-sm-12 pull-left m-t-md p-l-none">
                                    <div class="comments">
                                        <div class="comment-box p-lg m-b-lg">
                                            <img class="img-rounded coment-avatar" src="{{ asset('img/test-img/avatar.png') }}"/>
                                            <p class="comment-author p-b-xs">{{ trim($signal->firstname .' '. $signal->lastname) }}</p>
                                            <p>{{ $signal->description }}</p>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif

@include('partials.resource-embed')
