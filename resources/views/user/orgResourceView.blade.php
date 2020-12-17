@extends('layouts.app')
@section('content')
    <div class="container">
        @include('partials.alerts-bar')
        @if (Auth::user()->is_admin)
            @include('partials.admin-nav-bar', ['view' => 'organisation'])
        @else
            @include('partials.user-nav-bar', ['view' => 'organisation'])
        @endif
        @if (isset($fromOrg))
            @include('partials.org-nav-bar', ['view' => 'dataset', 'organisation' => $fromOrg])
        @endif
        @if (isset($resource->name))
            <div class="row">
                @if (isset($fromOrg))
                    <div class="col-sm-3 col-xs-12">
                        @include('partials.org-info', ['organisation' => $fromOrg])
                    </div>
                @endif
                <div class="col-sm-9 col-xs-12 m-t-lg side-info">
                    @include('components.datasets.resource_view', ['admin' => Auth::user()->is_admin])
                </div>
            </div>
        @else
            <div class="col-sm-12 m-t-xl no-info">
                {{ __('custom.no_info') }}
            </div>
        @endif
    </div>
@endsection
