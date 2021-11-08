@php
    $root = empty($admin) ? 'user' : 'admin';
    $resourceTypes = App\Resource::getTypes();
@endphp
@if (isset($dataset->name))
    <div class="{{ isset($fromOrg) || isset($group) ? 'col-sm-9' : 'col-sm-12' }} user-dataset">
        <h2>{{ $dataset->name }}</h2>
        <div class="col-xs-12 p-l-none m-b-lg">
            <div class="tags pull-left">
                @foreach ($dataset->tags as $tag)
                    <span class="badge badge-pill whitespace">{{ $tag->name }}</span>
                @endforeach
            </div>
        </div>
        <p>
            <strong>{{ utrans('custom.status') }}:</strong>
            @if ($dataset->status == App\DataSet::STATUS_DRAFT)
                &nbsp;<span>{{ utrans('custom.draft') }}</span>&nbsp;
            @else
                &nbsp;<span>{{ utrans('custom.published') }}</span>&nbsp;
            @endif
            &nbsp;&nbsp;<strong>{{ __('custom.visibility') }}:</strong>
            @if ($dataset->visibility == App\DataSet::VISIBILITY_PUBLIC)
                &nbsp;<span>{{ utrans('custom.public') }}</span>&nbsp;
            @else
                &nbsp;<span>{{ utrans('custom.private') }}</span>&nbsp;
            @endif
            &nbsp;&nbsp;<strong>{{ __('custom.version') }}:</strong>
            &nbsp;{{ $dataset->version }}
        </p>
        @if (!empty($admin))
            <p>
                <strong>{{ __('custom.id') }}:</strong>
                &nbsp;{{ $dataset->id }}
            </p>
        @endif
        <p>
            <strong>{{ __('custom.unique_identificator') }}:</strong>
            &nbsp;{{ $dataset->uri }}
        </p>
        @if (!empty($dataset->category_id))
            <p>
                <strong>{{ __('custom.main_topic') }}:</strong>
                &nbsp;{{ $dataset->category_name }}
            </p>
        @endif
        @if (!empty($dataset->terms_of_use_id))
            <p>
                <strong>{{ utrans('custom.license', 1) }}:</strong>
                &nbsp;{{ $dataset->terms_of_use_name }}
            </p>
        @endif
        @if (!empty($dataset->source))
            <p>
                <strong>{{ __('custom.source') }}:</strong>
                &nbsp;{{ $dataset->source }}
            </p>
        @endif
        @if (!empty($dataset->author_name))
            <p>
                <strong>{{ __('custom.author') }}:</strong>
                &nbsp;{{ $dataset->author_name }}
            </p>
        @endif
        @if (!empty($dataset->author_email))
            <p>
                <strong>{{ __('custom.contact_author') }}:</strong>
                &nbsp;{{ $dataset->author_email }}
            </p>
        @endif
        @if (!empty($dataset->support_name))
            <p>
                <strong>{{ __('custom.contact_support_name') }}:</strong>
                &nbsp;{{ $dataset->support_name }}
            </p>
        @endif
        @if (!empty($dataset->support_email))
            <p>
                <strong>{{ __('custom.contact_support') }}:</strong>
                &nbsp;{{ $dataset->support_email }}
            </p>
        @endif
        <p><strong>{{ __('custom.description') }}:</strong></p>
        <div class="m-b-sm">
            @if (!empty($dataset->description))
                {!! nl2br(e($dataset->description)) !!}
            @endif
        </div>
        @if (!empty($dataset->sla))
            <p><strong>{{ __('custom.sla_agreement') }}:</strong></p>
            <div class="m-b-sm">
                {!! nl2br(e($dataset->sla)) !!}
            </div>
        @endif
        @if (!empty($dataset->org))
            <p><strong>{{ utrans('custom.organisations') }}:</strong></p>
            <div class="m-b-sm">
                <p><a href="{{ url('/'. $root .'/organisations/view/'. $dataset->org->uri) }}">{{ $dataset->org->name }}</a></p>
            </div>
        @endif
        @if (!empty($dataset->groups))
            <p><strong>{{ utrans('custom.groups', 2) }}:</strong></p>
            <div class="m-b-sm">
                @foreach ($dataset->groups as $groupName)
                    <p>
                        <a href="{{ url('/'. $root .'/groups/view/'. $groupName->uri) }}">{{ $groupName->name }}</a>
                    </p>
                @endforeach
            </div>
        @endif
        @if (!empty($dataset->forum_link) && !empty($admin))
            <p>
                <strong>{{ __('custom.forum_link') }}:</strong>
                &nbsp;{{ $dataset->forum_link }}
            </p>
        @endif

        @if (
            (!empty($admin) || !empty($buttons['addToGroup']))
            && !empty($groups)
        )
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

        @if (
            !empty($admin) && \Auth::user()->is_admin
        )
            <p><strong>{{ __('custom.move_resources_to_new_dataset') }}</strong></p>
            <div class="col-xs-12 p-l-r-none">
                <form method="POST" class="col-lg-4" action="{{ route('adminOrgDatasetMove') }}">
                    {{ csrf_field() }}
                    <div class="form-group row">
                        <input type="hidden" name="current_data_set_id" value="{{$dataset->id}}">
                        <input type="text" name="new_data_set_id" class="form-control">
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

        @if (
            isset($dataset->custom_settings[0])
            && !empty($dataset->custom_settings[0]->key)
        )
            <p><b>{{ __('custom.additional_fields') }}:</b></p>
            @foreach ($dataset->custom_settings as $field)
                <div class="row m-b-lg">
                    <div class="col-xs-6">{{ $field->key }}</div>
                    <div class="col-xs-6 text-left">{{ $field->value }}</div>
                </div>
            @endforeach
        @endif

        <div class="col-xs-12 m-t-md p-l-r-none">
            <div class="history resource-links">
                @if (!empty($resources) && count($resources) > 1)
                    @include('partials.sorting.data-sets')
                @endif
                @include('partials.pagination')
                @foreach ($resources as $resource)
                    @if (!empty($admin) || !empty($buttons[$resource->uri]['view']))
                        <div class="m-b-xs {{ $resource->reported ? 'signaled' : '' }}">
                            <a
                                    @if (!empty($fromOrg))
                                    href="{{ url('/'. $root. '/organisations/'. $fromOrg->uri .'/resource/'. $resource->uri) }}"
                                    @elseif (!empty($group))
                                    href="{{ url('/'. $root. '/groups/'. $group->uri .'/resource/'. $resource->uri) }}"
                                    @else
                                    href="{{ url('/'. $root .'/resource/view/'. $resource->uri) }}"
                                    @endif
                            >
                                <span>
                                    <svg
                                            id="Layer_1"
                                            data-name="Layer 1"
                                            xmlns="http://www.w3.org/2000/svg"
                                            viewBox="0 0 30 30"
                                    >
                                        <path d="M26.72,29.9H3.33V0H26.72ZM4.62,28.61H25.43V1.29H4.62Z"/>
                                        <path d="M11.09,6.18V9.12H8.14V6.18h2.95m1.29-1.3H6.85v5.53h5.53V4.88Z"/>
                                        <path d="M11.09,13.48v2.94H8.14V13.48h2.95m1.29-1.29H6.85v5.52h5.53V12.19Z"/>
                                        <path d="M11.09,20.78v2.94H8.14V20.78h2.95m1.29-1.29H6.85V25h5.53V19.49Z"/>
                                        <rect x="14.34" y="21.38" width="7.57" height="1.74"/>
                                        <rect x="14.34" y="14.08" width="7.57" height="1.74"/>
                                        <rect x="14.34" y="6.78" width="7.57" height="1.74"/>
                                    </svg>
                                </span>
                                <span class="version-heading">{{ utrans('custom.resource') }}</span>
                                <span class="version">&nbsp;&#8211;&nbsp;{{ $resource->name }}</span>
                            </a>
                            @if ($resource->type == $resourceTypes[App\Resource::TYPE_FILE])
                                @if($resource->file_format == App\Resource::getFormats()[App\Resource::FORMAT_ZIP])
                                    <form method="POST" class="pull-right" action="{{ route('downloadZip', $resource->uri) }}">
                                @else
                                    <form method="POST" class="pull-right" action="{{ url('/resource/download') }}">
                                @endif
                                    {{ csrf_field() }}
                                    <input
                                            hidden
                                            name="resource"
                                            type="text"
                                            value="{{ $resource->id }}"
                                    >
                                    <input
                                            hidden
                                            name="version"
                                            type="text"
                                            value="{{ $resource->version }}"
                                    >
                                    <input
                                            hidden
                                            name="name"
                                            type="text"
                                            value="{{ $resource->name }}"
                                    >
                                    <input
                                            hidden
                                            name="format"
                                            type="text"
                                            value="{{ $resource->file_format }}"
                                    >
                                    <button
                                            name="download"
                                            type="submit"
                                            class="btn btn-primary js-ga-event"
                                            data-ga-action="download"
                                            data-ga-label="resource download"
                                            data-ga-category="data"
                                    >{{ uctrans('custom.download') }}</button>
                                </form>
                            @endif
                        </div>
                    @endif
                @endforeach
                @include('partials.pagination')
            </div>
        </div>
        <div class="info-bar-sm col-sm-7 col-xs-12 p-l-none">
            <ul class="p-l-none">
                <li>{{ __('custom.created_at') }}: {{ $dataset->created_at }}</li>
                <li>{{ __('custom.created_by') }}: {{ $dataset->created_by }}</li>
                @if (!empty($dataset->updated_by))
                    <li>{{ __('custom.updated_at') }}: {{ $dataset->updated_at }}</li>
                    <li>{{ __('custom.updated_by') }}: {{ $dataset->updated_by }}</li>
                @endif
            </ul>
        </div>

        <div class="{{ isset($fromOrg) || isset($group) ? 'col-sm-9' : 'col-xs-12' }} mng-btns p-l-r-none">
            @if (!empty($admin) || !empty($buttons['addResource']))
                <a
                        class="btn btn-primary"
                        @if (isset($group))
                        href="{{ url('/'. $root .'/groups/'. $group->uri .'/dataset/resource/create/'. $dataset->uri) }}"
                        @elseif (isset($fromOrg))
                        href="{{ url('/'. $root .'/organisations/'. $fromOrg->uri .'/dataset/resource/create/'. $dataset->uri) }}"
                        @else
                        href="{{ url('/'. $root .'/dataset/resource/create/'. $dataset->uri) }}"
                        @endif
                >{{ uctrans('custom.add_resource') }}</a>
            @endif
            @if (!empty($admin) || !empty($buttons[$dataset->uri]['edit']))
                <a
                        class="btn btn-primary"
                        @if (isset($group))
                        href="{{ url('/'. $root .'/groups/'. $group->uri .'/dataset/edit/'. $dataset->uri) }}"
                        @elseif (isset($fromOrg))
                        href="{{ url('/'. $root .'/organisations/'. $fromOrg->uri .'/dataset/edit/'. $dataset->uri) }}"
                        @else
                        href="{{ url('/'. $root .'/dataset/edit/'. $dataset->uri) }}"
                        @endif
                >{{ uctrans('custom.edit') }}</a>
            @endif
            <form method="POST" class="inline-block">
                {{ csrf_field() }}
                <a
                        name="back"
                        @if (isset($group))
                        href="{{ url('/'. $root .'/groups/datasets/'. $group->uri) }}"
                        @elseif (isset($fromOrg))
                        href="{{ url('/'. $root .'/organisations/datasets/'. $fromOrg->uri) }}"
                        @else
                        href="{{ url('/'. $root .'/datasets/') }}"
                        @endif
                        class="btn btn-primary"
                >{{ uctrans('custom.close') }}</a>
            </form>
            @if (!empty($admin) || !empty($buttons[$dataset->uri]['delete']))
                <form method="POST" class="inline-block">
                    {{ csrf_field() }}
                    <input type="hidden" name="dataset_uri" value="{{ $dataset->uri }}">
                    <button
                            class="btn del-btn btn-primary"
                            type="submit"
                            name="delete"
                            data-confirm="{{ __('custom.remove_data') }}"
                    >{{ uctrans('custom.remove') }}</button>
                </form>
            @endif
        </div>
    </div>

    @include('components.signal-box', ['signals' => $dataset->signals])
@endif