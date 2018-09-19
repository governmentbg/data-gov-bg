@extends('layouts.app')

@section('content')
<div class="container">
    @include('partials.alerts-bar')
    @if ($parent)
        @include('partials.admin-nav-bar', ['view' => $parent->type == App\Organisation::TYPE_GROUP ? 'group' : 'organisation'])
        @if ($parent->type == App\Organisation::TYPE_GROUP)
            @include('partials.group-nav-bar', ['view' => 'datasets', 'group' => $parent])
            <div class="col-sm-3 col-xs-12">
                @include('partials.group-info', ['group' => $parent])
            </div>
            <div class="col-sm-9 col-xs-12">
                @include('components.datasets.resource_edit_metadata', ['admin' => true, 'parent' => $parent])
            </div>
        @else
            @include('partials.org-nav-bar', ['view' => 'datasets', 'organisation' => $parent])
            @include('partials.org-info', ['organisation' => $parent])
            <div class="col-sm-9 col-xs-12">
                @include('components.datasets.resource_edit_metadata', ['admin' => true, 'parent' => $parent])
            </div>
        @endif
    @else
        @include('partials.admin-nav-bar', ['view' => 'datasets'])
        @include('components.datasets.resource_edit_metadata', ['admin' => true])
    @endif
</div>
@endsection
