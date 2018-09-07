@extends('layouts.app')

@section('content')
    <div class="container">
        @include('partials.alerts-bar')
        @include('partials.user-nav-bar', ['view' => 'dataset'])
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
                    @if (!empty($dataset->descript))
                    <p><strong>{{ __('custom.description') }}:</strong></p>
                    <div class="m-b-sm">
                        {{ $dataset->description }}
                    </div>
                    @endif
                    @if (!empty($dataset->sla))
                    <p><strong>{{ __('custom.sla_agreement') }}:</strong></p>
                    <div class="m-b-sm">
                        {{ $dataset->sla }}
                    </div>
                    @endif
                    <div class="col-sm-12 pull-left m-t-md p-l-none">
                        <div class="pull-left history">
                            @foreach ($resources as $resource)
                                <div class="{{ $resource->reported ? 'signaled' : '' }}">
                                    <a href="{{ route('resourceView', ['uri' => $resource->uri]) }}">
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
                    <div class="info-bar-sm col-sm-7 col-xs-12 p-l-none">
                        <ul class="p-l-none">
                            <li>{{ __('custom.created_at') }}: {{ $dataset->created_at }}</li>
                            <li>{{ __('custom.created_by') }}: {{ $dataset->created_by }}</li>
                            @if (!empty($dataset->updated_by))
                                <li>{{ __('custom.updated_at') }}: {{ $dataset->updated_at }}</li>
                                <li>{{ __('custom.updated_by') }}: {{ $dataset->updated_by }}</li>
                            @endif
                            <li>.....................................</li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-2 col-sm-3 text-left m-l-10">
                    <a
                        class="btn btn-primary"
                        href="{{ route('resourceCreate', ['uri' => $dataset->uri]) }}"
                    >{{ uctrans('custom.add_resource') }}</a>
                </div>
                <div class="col-md-2 col-sm-3 text-left m-l-10">
                    <a
                        class="btn btn-primary"
                        href="{{ url('/user/dataset/edit/'. $dataset->uri) }}"
                    >{{ uctrans('custom.edit') }}</a>
                </div>
                <div class="col-md-9 col-sm-8 text-left m-l-10 m-t-md">
                    <form method="POST">
                        {{ csrf_field() }}
                        <button
                            class="btn del-btn btn-primary"
                            type="submit"
                            name="delete"
                            data-confirm="{{ __('custom.remove_data') }}"
                        >{{ uctrans('custom.remove') }}</button>
                        <input type="hidden" name="dataset_uri" value="{{ $dataset->uri }}">
                    </form>
                </div>
            </div>
        @endif
    </div>
@endsection
