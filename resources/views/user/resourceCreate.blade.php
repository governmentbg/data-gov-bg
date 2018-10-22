@extends('layouts.app')
@php $root = !(Auth::user()->is_admin) ? 'user' : 'admin'; @endphp
@section('content')
<div class="container">
    @include('partials.alerts-bar')
    @if (isset($fromOrg))
        @include('partials.'. $root .'-nav-bar', ['view' => 'organisation'])
        @include('partials.org-nav-bar', ['view' => 'dataset', 'organisation' => $fromOrg])
        <div class="col-sm-3 col-xs-12">
            @include('partials.org-info', ['organisation' => $fromOrg])
        </div>
    @elseif (isset($group))
        @include('partials.'. $root .'-nav-bar', ['view' => 'group'])
        @include('partials.group-nav-bar', ['view' => 'dataset', 'group' => $group])
        <div class="col-sm-3 col-xs-12">
            @include('partials.group-info', ['group' => $group])
        </div>
    @else
        @include('partials.'. $root .'-nav-bar', ['view' => 'dataset'])
    @endif
    @include('components.datasets.resource_create')
</div>
@endsection
