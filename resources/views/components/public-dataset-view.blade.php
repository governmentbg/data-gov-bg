@if (!empty($dataset))
    <div class="row">
        <div class="col-sm-9 col-xs-12 page-content col-sm-offset-3 p-h-sm">
            <div class="articles">
                <div class="article col-xs-12">
                    <div>
                    @if (!empty($organisation))
                        <div class="col-sm-7 col-xs-12 p-l-r-none m-t-lg m-b-md">
                            <div class="col-md-8 col-sm-10 col-xs-12 logo-img p-l-r-none">
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
                    <div class="col-xs-12 p-l-r-none m-t-md m-b-md">
                        <div class="art-heading-bar row">
                            <div class="col-sm-7 col-xs-12 p-l-r-none">
                                <div class="p-w-sm">
                                    @include('partials.social-icons', ['shareUrl' => url()->current()])
                                </div>
                                @if ($approved)
                                    <div class="status p-w-sm">
                                        <span>{{ __('custom.approved') }} </span>
                                    </div>
                                @else
                                    <div class="status notApproved p-w-sm p-l-r-none">
                                        <span>{{ __('custom.unapproved') }}</span>
                                    </div>
                                @endif
                            </div>
                            <div class="follow pull-right">
                                <form method="post">
                                    {{ csrf_field() }}
                                    @if (isset($buttons['follow']) && $buttons['follow'])
                                        <div>
                                            <button class="badge badge-pill" type="submit" name="follow" value="{{ $dataset->id }}">{{ utrans('custom.follow') }}</button>
                                        </div>
                                    @elseif (isset($buttons['unfollow']) && $buttons['unfollow'])
                                        <div>
                                            <button class="badge badge-pill" type="submit" name="unfollow" value="{{ $dataset->id }}">{{ uctrans('custom.stop_follow') }}</button>
                                        </div>
                                    @endif
                                </form>
                            </div>
                        </div>
                        <h2>{{ $dataset->name }}</h2>
                        <p>
                            <strong>{{ __('custom.unique_identificator') }}:</strong>
                            &nbsp;{{ $dataset->uri }}
                        </p>
                        @if (!empty($dataset->description))
                            <p><strong>{{ __('custom.description') }}:</strong></p>
                            <p>{!! nl2br(e($dataset->description)) !!}</p>
                        @endif
                        @if (!empty($dataset->terms_of_use_id))
                            <p>
                                <strong>{{ utrans('custom.license', 1) }}:</strong>
                                &nbsp;{{ $dataset->terms_of_use_name }}
                            </p>
                        @endif
                        @if (!empty($dataset->category_id))
                            <p>
                                <strong>{{ __('custom.main_topic') }}:</strong>
                                &nbsp;{{ $dataset->category_name }}
                            </p>
                        @endif
                        <div class="col-xs-12 p-l-none">
                            <div class="pull-left">
                                @if (isset($dataset->tags) && count($dataset->tags) > 0)
                                    @foreach ($dataset->tags as $tag)
                                        <span class="badge badge-pill m-b-sm">{{ $tag->name }}</span>
                                    @endforeach
                                @endif
                            </div>
                        </div>

                         @if ( !empty($buttons['addGroup']) && !empty($groups))
                            <div class="col-xs-12 p-l-r-none">
                                <form method="POST" class="col-lg-4">
                                    {{ csrf_field() }}
                                    <div class="form-group row">
                                        <select
                                            id="group"
                                            name="group_id[]"
                                            class="js-autocomplete form-control"
                                            data-placeholder="{{ utrans('custom.groups', 1) }}"
                                            multiple="multiple"
                                        >
                                            <option></option>
                                            @foreach ($groups as $id => $groupName)
                                                <option
                                                    value="{{ $id }}"
                                                    {{
                                                        !empty(old('group_id'))
                                                        && in_array($id, old('group_id'))
                                                        || !empty($setGroups)
                                                        && in_array($id, $setGroups)
                                                        ? 'selected' : ''
                                                    }}
                                                >{{ $groupName }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="form-group row">
                                        <button
                                            type="submit"
                                            name="save"
                                            class="btn btn-primary"
                                        >{{ uctrans('custom.save') }}</button>
                                    </div>
                                </form>
                            </div>
                        @endif

                        @if (count($resources) > 0)
                        <div class="col-sm-12 pull-left p-h-sm p-l-none">
                            <div class="pull-left history">
                                @foreach ($resources as $resource)
                                    <div class="{{ $resource->reported ? 'signaled' : '' }}">
                                        <a href="{{ route($routeName, array_merge(app('request')->input(), ['uri' => $resource->uri, 'version' => ''])) }}">
                                            <span>
                                                <svg id="Layer_1" data-name="Layer 1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 30 30"><path d="M26.72,29.9H3.33V0H26.72ZM4.62,28.61H25.43V1.29H4.62Z"/><path d="M11.09,6.18V9.12H8.14V6.18h2.95m1.29-1.3H6.85v5.53h5.53V4.88Z"/><path d="M11.09,13.48v2.94H8.14V13.48h2.95m1.29-1.29H6.85v5.52h5.53V12.19Z"/><path d="M11.09,20.78v2.94H8.14V20.78h2.95m1.29-1.29H6.85V25h5.53V19.49Z"/><rect x="14.34" y="21.38" width="7.57" height="1.74"/><rect x="14.34" y="14.08" width="7.57" height="1.74"/><rect x="14.34" y="6.78" width="7.57" height="1.74"/></svg>
                                            </span>
                                            <span class="version-heading">{{ utrans('custom.resource') }}</span>
                                            <span class="version">&nbsp;&#8211;&nbsp;{{ $resource->name }}</span>
                                        </a>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        @endif
                        <p>
                            <strong>{{ utrans('custom.version') }}:</strong>&nbsp;{{ $dataset->version }}
                        </p>
                        @if (!empty($dataset->source))
                            <p>
                                <strong>{{ __('custom.source') }}:</strong>&nbsp;{{ $dataset->source }}
                            </p>
                        @endif
                        @if (!empty($dataset->author_name))
                            <p>
                                <strong>{{ __('custom.author') }}:</strong>&nbsp;{{ $dataset->author_name }}
                            </p>
                        @endif
                        @if (!empty($dataset->author_email))
                            <p>
                                <strong>{{ __('custom.contact_author') }}:</strong>&nbsp;{{ $dataset->author_email }}
                            </p>
                        @endif
                        @if (!empty($dataset->support_email))
                            <p>
                                <strong>{{ __('custom.contact_support_name') }}:</strong>&nbsp;{{ $dataset->support_email }}
                            </p>
                        @endif
                        @if (!empty($dataset->support_email))
                            <p>
                                <strong>{{ __('custom.contact_support') }}:</strong>&nbsp;{{ $dataset->support_email }}
                            </p>
                        @endif
                        @if (!empty($dataset->sla))
                            <p>
                                <strong>{{ __('custom.sla_agreement') }}:&nbsp;</strong>
                            </p>
                            <div class="m-b-sm"><p>{!! nl2br(e($dataset->sla)) !!}</p></div>
                        @endif
                        @if (isset($dataset->custom_settings[0]) && !empty($dataset->custom_settings[0]->key))
                            <p><b>{{ __('custom.additional_fields') }}:</b></p>
                            @foreach ($dataset->custom_settings as $field)
                                <div class="row m-b-lg">
                                    <div class="col-xs-6">{{ $field->key }}</div>
                                    <div class="col-xs-6 text-left">{{ $field->value }}</div>
                                </div>
                            @endforeach
                        @endif
                        <div class="info-bar-sm col-sm-12 col-xs-12 p-l-none">
                            <ul class="p-l-none p-h-sm">
                                <li>{{ __('custom.created_at') }}: {{ $dataset->created_at }}</li>
                                @if (!empty($dataset->created_by))
                                    <li>{{ __('custom.created_by') }}: {{ $dataset->created_by }}</li>
                                @endif
                                @if (!empty($dataset->updated_at))
                                    <li>{{ __('custom.updated_at') }}: {{ $dataset->updated_at }}</li>
                                @endif
                                @if (!empty($dataset->updated_by))
                                    <li>{{ __('custom.updated_by') }}: {{ $dataset->updated_by }}</li>
                                @endif
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-9 col-xs-12 page-content p-sm col-sm-offset-3 mng-btns">
            @if (isset($buttons['addResource']) && $buttons['addResource'])
                <a
                    class="btn btn-primary badge badge-pill"
                    href="{{ url(
                        '/'. (isset($buttons['addResourceRootUrl']) ? $buttons['addResourceRootUrl'] : $buttons['rootUrl'] .'/dataset') .
                        '/resource/create/'. $dataset->uri
                    ) }}"
                >{{ uctrans('custom.add_resource') }}</a>
            @endif
            @if (isset($buttons['edit']) && $buttons['edit'])
                <a
                    class="btn btn-primary badge badge-pill"
                    href="{{ url(
                        '/'. (isset($buttons['editRootUrl']) ? $buttons['editRootUrl'] : $buttons['rootUrl'] .'/dataset') .
                        '/edit/'. $dataset->uri
                    ) }}"
                >{{ uctrans('custom.edit') }}</a>
            @endif
            @if (isset($buttons['delete']) && $buttons['delete'])
                <form method="POST" class="inline-block">
                    {{ csrf_field() }}
                    <button
                        class="btn del-btn btn-primary badge badge-pill"
                        type="submit"
                        name="delete"
                        data-confirm="{{ __('custom.remove_data') }}"
                    >{{ uctrans('custom.remove') }}</button>
                    <input type="hidden" name="dataset_uri" value="{{ $dataset->uri }}">
                </form>
            @endif
        </div>
    </div>
@endif
