@extends('layouts.app')

@section('content')
<div class="container">
    @include('partials.alerts-bar')
    @if (isset($fromOrg))
        @if(\Auth::user()->is_admin)
            @include('partials.admin-nav-bar', ['view' => 'organisation'])
        @else
            @include('partials.user-nav-bar', ['view' => 'organisation'])
        @endif
        @include('partials.org-nav-bar', ['view' => 'dataset', 'organisation' => $fromOrg])
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
        @if(\Auth::user()->is_admin)
            @include('partials.admin-nav-bar', ['view' => 'group'])
        @else
            @include('partials.user-nav-bar', ['view' => 'group'])
        @endif
        @include('partials.group-nav-bar', ['view' => 'dataset', 'group' => $group])
        <div class="row">
            <div class="col-sm-3 col-xs-12">
                @include('partials.group-info', ['group' => $group])
            </div>
            <div class="col-sm-9 col-xs-12">
                @include('components.datasets.resource_create')
            </div>
        </div>
    @else
        @if(\Auth::user()->is_admin)
            @include('partials.admin-nav-bar', ['view' => 'dataset'])
        @else
            @include('partials.user-nav-bar', ['view' => 'dataset'])
        @endif
        @include('components.datasets.resource_create')
    @endif
</div>
@endsection
