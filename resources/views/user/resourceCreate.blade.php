@extends('layouts.app')

@section('content')
<div class="container">
    @include('partials.alerts-bar')
    @include('partials.user-nav-bar', ['view' => 'datasets'])
    @if (isset($fromOrg) && !is_null($fromOrg))
        @include('partials.org-nav-bar', ['view' => 'datasets', 'organisation' => $fromOrg])
        @if (isset($dataSetName))
            <div class="sidenav text-center">
                <div class="profile-name m-l-lg">{{ $dataSetName }}</div>
            </div>
        @endif
        <div class="row">
            <div class="org col-sm-3 col-xs-12 m-t-lg m-l-md">
                <div><img class="full-size" src="{{ $fromOrg->logo }}"></div>
                <h2 class="elipsis-1">{{ $fromOrg->name }}</h2>
                <h4>{{ truncate($fromOrg->descript, 150) }}</h4>
                <p class="text-right show-more">
                    <a href="{{ url('/admin/organisations/view/'. $fromOrg->uri) }}" class="view-profile">{{ __('custom.see_more') }}</a>
                </p>
            </div>
        </div>
    @endif
    @include('components.datasets.resource_create')
</div>
@endsection
