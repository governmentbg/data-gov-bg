@php $root = empty($admin) ? 'user' : 'admin'; @endphp
@if (isset($dataset->name))
    <div class="row">
        <div class="col-sm-12 user-dataset m-l-10">
            <h2>{{ $dataset->name }}</h2>
            <div class="col-sm-12 p-l-none">
                <div class="tags pull-left">
                    @foreach ($dataset->tags as $tag)
                        <span class="badge badge-pill">{{ $tag->name }}</span>
                    @endforeach
                </div>
            </div>
            <p>
                @if ($dataset->status == App\DataSet::STATUS_DRAFT)
                    &nbsp;<span>{{ utrans('custom.draft') }}</span>&nbsp;
                @else
                    &nbsp;<span>{{ utrans('custom.published') }}</span>&nbsp;
                @endif
                @if ($dataset->visibility == App\DataSet::VISIBILITY_PUBLIC)
                    &nbsp;<span>{{ utrans('custom.public') }}</span>&nbsp;
                @else
                    &nbsp;<span>{{ utrans('custom.private') }}</span>&nbsp;
                @endif
                &nbsp;{{ utrans('custom.version') }}:&nbsp;{{ $dataset->version }}
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
                <p><strong>{{ utrans('custom.organisation') }}:</strong></p>
                <div class="m-b-sm">
                    <p><a href="{{ url('/'. $root .'/organisations/view/'. $dataset->org->uri) }}">{{ $dataset->org->name }}</a></p>
                </div>
            @endif
            @if (!empty($dataset->groups))
                <p><strong>{{ utrans('custom.groups', 2) }}:</strong></p>
                <div class="m-b-sm">
                    @foreach ($dataset->groups as $group)
                    <p>
                        <a href="{{ url('/'. $root .'/groups/view/'. $group->uri) }}">{{ $group->name }}</a>
                    </p>
                    @endforeach
                </div>
            @endif

            @if (!empty($admin) || !empty($buttons['addResource']))
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
                            @foreach ($groups as $id => $group)
                                <option
                                    value="{{ $id }}"
                                    {{
                                        !empty(old('group_id'))
                                        && in_array($id, old('group_id'))
                                        || !empty($setGroups)
                                        && in_array($id, $setGroups)
                                        ? 'selected' : ''
                                    }}
                                >{{ $group }}</option>
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
            <div class="col-sm-12 pull-left m-t-md p-l-none">
                <div class="pull-left history">
                    @foreach ($resources as $resource)
                        @if (!empty($admin) || !empty($buttons[$resource->uri]['view']))
                            <div class="{{ $resource->reported ? 'signaled' : '' }}">
                                <a href="{{ url('/'. $root .'/resource/view/'. $resource->uri) }}">
                                    <span>
                                        <svg id="Layer_1" data-name="Layer 1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 30 30"><path d="M26.72,29.9H3.33V0H26.72ZM4.62,28.61H25.43V1.29H4.62Z"/><path d="M11.09,6.18V9.12H8.14V6.18h2.95m1.29-1.3H6.85v5.53h5.53V4.88Z"/><path d="M11.09,13.48v2.94H8.14V13.48h2.95m1.29-1.29H6.85v5.52h5.53V12.19Z"/><path d="M11.09,20.78v2.94H8.14V20.78h2.95m1.29-1.29H6.85V25h5.53V19.49Z"/><rect x="14.34" y="21.38" width="7.57" height="1.74"/><rect x="14.34" y="14.08" width="7.57" height="1.74"/><rect x="14.34" y="6.78" width="7.57" height="1.74"/></svg>
                                    </span>
                                    <span class="version-heading">{{ utrans('custom.resource') }}</span>
                                    <span class="version">&nbsp;&#8211;&nbsp;{{ $resource->name }}</span>
                                </a>
                            </div>
                        @endif
                    @endforeach
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
        </div>
    </div>
    <div class="row">
        <div class="col-lg-12 mng-btns">
            @if (!empty($admin) || !empty($buttons['addResource']))
                <a
                    class="btn btn-primary"
                    href="{{ url('/'. $root .'/dataset/resource/create/'. $dataset->uri) }}"
                >{{ uctrans('custom.add_resource') }}</a>
            @endif
            @if (!empty($admin) || !empty($buttons[$dataset->uri]['edit']))
                <a
                    class="btn btn-primary"
                    href="{{ url('/'. $root .'/dataset/edit/'. $dataset->uri) }}"
                >{{ uctrans('custom.edit') }}</a>
            @endif
            <form method="POST" class="inline-block">
            {{ csrf_field() }}
                <button
                    name="back"
                    class="btn btn-primary"
                >{{ uctrans('custom.close') }}</button>
            </form>
            @if (!empty($admin) || !empty($buttons[$dataset->uri]['delete']))
                <form method="POST" class="inline-block" action="{{ url('/'. $root .'/dataset/delete') }}">
                    {{ csrf_field() }}
                    <button
                        class="btn del-btn btn-primary"
                        type="submit"
                        name="delete"
                        data-confirm="{{ __('custom.remove_data') }}"
                    >{{ uctrans('custom.remove') }}</button>
                    <input type="hidden" name="dataset_uri" value="{{ $dataset->uri }}">
                </form>
            @endif
        </div>
    </div>
    @include('components.signal-box', ['signals' => $dataset->signals])
@endif
