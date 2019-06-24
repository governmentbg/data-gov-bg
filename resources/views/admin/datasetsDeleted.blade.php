@extends('layouts.app')

@section('content')

<div class="container">
    @include('partials.alerts-bar')
    @include('partials.admin-nav-bar', ['view' => 'dataset'])
    <div class="col-xs-12 m-t-lg p-l-r-none">
        <div class="col-xs-4 p-l-r-none">
            <span class="my-profile m-l-sm">{{ uctrans('custom.datasets_list') }}</span>
        </div>
        <div class="col-xs-8">
            <div class="filter-content org-nav-bar">
                <div class="row">
                    <div class="col-md-12">
                        <ul class="nav filter-type right-border">
                            <li>
                                <a
                                    class="{{ $view == 'datasets' ? 'active' : null }}"
                                    href="{{ url('/admin/datasets') }}"
                                >{{ ultrans('custom.datasets') }}</a>
                            </li>
                            <li>
                                <a
                                    class="{{ $view == 'deletedDatasets' ? 'active' : null }}"
                                    href="{{ url('/admin/datasetsDeleted') }}"
                                >{{ ultrans('custom.deleted_datasets') }}</a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-4 col-xs-12 text-left">
    </div>
    <div class="col-lg-8 col-md-9 col-sm-8 col-xs-12 search-field admin">
        <form method="GET">
            <input
                type="text"
                class="m-t-md input-border-r-12 form-control js-ga-event"
                placeholder="{{ __('custom.search') }}.."
                value="{{ isset($search) ? $search : '' }}"
                name="q"
                data-ga-action="search"
                data-ga-label="data search"
                data-ga-category="data"
            >
            @foreach (app('request')->except(['q', 'page']) as $key => $value)
                @if (is_array($value))
                    @foreach ($value as $innerValue)
                        <input name="{{ $key }}[]" type="hidden" value="{{ $innerValue }}">
                    @endforeach
                @else
                    <input name="{{ $key }}" type="hidden" value="{{ $value }}">
                @endif
            @endforeach
        </form>
    </div>
    @include('partials.pagination')
    <div class="row">
        <div class="col-sm-3 col-xs-12 text-left p-l-none">
        </div>
        <div class="col-sm-9 col-xs-12 m-t-md">
            <div class="row">
                <div class="articles m-t-lg">
                    @if (count($datasets))
                        @foreach ($datasets as $set)
                            <div class="article m-b-lg col-xs-12 user-dataset">
                                <div>{{ __('custom.date_added') }}: {{ $set->created_at }}</div>
                                <div class="col-sm-13 p-l-none">
                                    <a href="{{ url('/admin/viewDeletedDataset/'. $set->uri) }}">
                                        <h2 class="m-t-xs">{{ $set->name }}</h2>
                                    </a>
                                    <div class="desc">
                                        {!! nl2br(truncate(e($set->descript), 150)) !!}
                                    </div>
                                    <div class="col-sm-12 p-l-none btns">
                                        <div class="pull-left row">
                                            <div class="col-xs-6">
                                                <span class="badge badge-pill m-r-md m-b-sm">
                                                    <a
                                                        href="{{ url('/admin/viewDeletedDataset/'. $set->uri) }}"
                                                    >{{ uctrans('custom.preview') }}</a>
                                                </span>
                                            </div>
                                            <div class="col-xs-6">
                                                @if (in_array($set->id, $allowActionsForDataset))
                                                    <form method="POST" action="{{ url('admin/organisations/hardDeleteDataset') }}">
                                                        {{ csrf_field() }}
                                                        <div class="col-xs-6 text-right">
                                                            <button
                                                                class="badge badge-pill m-b-sm del-btn"
                                                                type="submit"
                                                                name="delete"
                                                                data-confirm="{{ __('custom.hard_remove') }}"
                                                            >{{ uctrans('custom.hard_remove') }}</button>
                                                        </div>
                                                        <input type="hidden" name="dataset_id" value="{{ $set->id }}">
                                                        <input type="hidden" name="source" value="user">
                                                    </form>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="col-sm-12 m-t-md text-center no-info">
                            {{ __('custom.no_info') }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @if (isset($pagination))
        <div class="row">
            <div class="col-xs-12 text-center pagination">
                {{ $pagination->render() }}
            </div>
        </div>
    @endif
</div>
@endsection
