@extends('layouts.app')

@section('content')
<div class="container">
    @include('partials.alerts-bar')
    @if (Auth::user()->is_admin)
        @include('partials.admin-nav-bar', ['view' => 'group'])
    @else
        @include('partials.user-nav-bar', ['view' => 'group'])
    @endif
    @include('partials.group-nav-bar', ['view' => 'dataset', 'group' => $group])
    <div class="col-sm-3 col-xs-12 text-left sidenav">
        @include('partials.group-info', ['group' => $group])
    </div>
    @include('components.datasets.dataset_edit', ['admin' => Auth::user()->is_admin])
</div>
@endsection
