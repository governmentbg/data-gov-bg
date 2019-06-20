@extends('layouts.app')

@section('content')
@php
    $view = $organisation->type == App\Organisation::TYPE_GROUP ? 'group' : 'organisation';
@endphp

<div class="container">
    @include('partials.alerts-bar')
    @include('partials.admin-nav-bar', ['view' => $view])

    @if ($view == 'group')
        @include('partials.group-nav-bar', ['view' => 'deletedDatasets', 'group' => $organisation])
    @else
        @include('partials.org-nav-bar', ['view' => 'deletedDatasets', 'organisation' => $organisation])
    @endif
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
    @include('components.datasets.deleted_datasets', ['admin' => true])
    @if (isset($pagination))
        <div class="row">
            <div class="col-xs-12 text-center pagination">
                {{ $pagination->render() }}
            </div>
        </div>
    @endif
</div>
@endsection
