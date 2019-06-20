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
    @include('components.datasets.view_deleted_dataset', ['admin' => true, 'dataset' => $dataset, 'resources' => $resources])
</div>
@endsection
