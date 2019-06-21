@if ($view == 'organisation')
    @php
        $viewPath = 'organisations';
    @endphp
@else
    @php
        $viewPath = 'groups';
    @endphp
@endif
@if (isset($dataset->name))
    <div class="{{ isset($group) ? 'col-sm-9' : 'col-sm-12' }} user-dataset">
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
        @if (!empty($dataset->organisation))
            <p><strong>{{ utrans('custom.organisations') }}:</strong></p>
            <div class="m-b-sm">
                <p><a href="{{ url('admin/organisations/view/'. $dataset->organisation->uri) }}">{{ $dataset->organisation->name }}</a></p>
            </div>
        @endif
        @if (!empty($dataset->groups))
            <p><strong>{{ utrans('custom.groups', 2) }}:</strong></p>
            <div class="m-b-sm">
                @foreach ($dataset->groups as $groupName)
                <p>
                    <a href="{{ url('admin/groups/view/'. $groupName->uri) }}">{{ $groupName->name }}</a>
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
                @foreach ($resources as $resource)
                    @if (!empty($admin) || !empty($buttons[$resource->uri]['view']))
                        <div class="m-b-xs {{ $resource->reported ? 'signaled' : '' }}">
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
                @if (!empty($dataset->deleted_by))
                    <li>{{ __('custom.deleted_at') }}: {{ $dataset->deleted_at }}</li>
                    <li>{{ __('custom.deleted_by') }}: {{ $dataset->deleted_by }}</li>
                @endif
            </ul>
        </div>

        <div class="col-sm-9 mng-btns p-l-r-none">
            <form method="POST" class="inline-block">
            {{ csrf_field() }}
                <a
                    name="back"
                    href="{{ url('admin/'. $viewPath .'/deletedDatasets/'. $organisation->uri) }}"
                    class="btn btn-primary"
                >{{ uctrans('custom.close') }}</a>
            </form>
            @if ($allowDelete)
                <form method="POST" class="inline-block" action="{{ url('admin/organisations/hardDeleteDataset') }}">
                    {{ csrf_field() }}
                    <button
                       class="btn del-btn btn-primary"
                        type="submit"
                        name="delete"
                        data-confirm="{{ __('custom.remove_data') }}"
                    >{{ uctrans('custom.remove') }}</button>
                    <input type="hidden" name="dataset_id" value="{{ $dataset->id }}">
                </form>
            @endif
        </div>
    </div>
@endif
