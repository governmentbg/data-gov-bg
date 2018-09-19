@extends('layouts.app')

@section('content')
<div class="container">
    @include('partials.alerts-bar')
    @if (Auth::user()->is_admin)
        @include('partials.admin-nav-bar', ['view' => 'group'])
    @else
        @include('partials.user-nav-bar', ['view' => 'group'])
    @endif
    @if (isset($fromOrg) && !is_null($fromOrg))
        @include('partials.org-nav-bar', ['view' => 'datasets', 'organisation' => $fromOrg])
        @if (isset($dataSetName))
            <div class="sidenav text-center">
                <div class="profile-name m-l-lg">{{ $dataSetName }}</div>
            </div>
        @endif
        <div class="row">
            @include('partials.org-info', ['organisation' => $fromOrg])
            <div class="col-sm-9 col-xs-12">
                @include('components.datasets.resource_create')
            </div>
        </div>
    @elseif (isset($group))
        @include('partials.group-nav-bar', ['view' => 'datasets', 'group' => $group])
        <div class="row">
            <div class="col-sm-3 col-xs-12">
                @include('partials.group-info', ['group' => $group])
            </div>
            <div class="col-sm-9 col-xs-12">
                @include('components.datasets.resource_create')
            </div>
        </div>
    @endif
</div>
@endsection
