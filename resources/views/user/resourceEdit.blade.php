@extends('layouts.app')

@section('content')
<div class="container">
    @include('partials.alerts-bar')
    @if ($parent)
        @include('partials.user-nav-bar', ['view' => $parent->type == App\Organisation::TYPE_GROUP ? 'group' : 'organisation'])
        @if ($parent->type == App\Organisation::TYPE_GROUP)
            @include('partials.group-nav-bar', ['view' => 'dataset', 'group' => $parent])
            <div class="col-sm-3 col-xs-12">
                @include('partials.group-info', ['group' => $parent])
            </div>
        @else
            @include('partials.org-nav-bar', ['view' => 'dataset', 'organisation' => $parent])
            <div class="col-sm-3 col-xs-12">
                @include('partials.org-info', ['organisation' => $parent])
            </div>
        @endif
    @else
        @include('partials.user-nav-bar', ['view' => 'dataset'])
    @endif
    @include('components.datasets.resource_edit_metadata')
</div>
@endsection
