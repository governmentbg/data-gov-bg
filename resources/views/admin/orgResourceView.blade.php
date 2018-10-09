@extends('layouts.app')
@section('content')
    <div class="container">
        @include('partials.alerts-bar')
        @include('partials.admin-nav-bar', ['view' => 'organisation'])
        @if (isset($fromOrg))
            @include('partials.org-nav-bar', ['view' => 'dataset', 'organisation' => $fromOrg])
        @endif
        @if (isset($resource->name))
            <div class="row">
                @if (isset($fromOrg))
                    @include('partials.org-info', ['organisation' => $fromOrg])
                @endif
                <div class="col-sm-9 col-xs-12 m-t-lg side-info">
                    @include('components.datasets.resource_view')
                </div>
            </div>
        @else
            <div class="col-sm-12 m-t-xl no-info">
                {{ __('custom.no_info') }}
            </div>
        @endif
    </div>
@endsection
