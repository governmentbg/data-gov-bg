@extends('layouts.app')

@section('content')
<div class="container">
    @include('partials.alerts-bar')
    @include('partials.user-nav-bar', ['view' => 'organisation'])
    @include('partials.org-nav-bar', ['view' => 'dataset', 'organisation' => $organisation])
    @include('partials.pagination')
    @include('components.datasets.org_datasets')
    @include('partials.pagination')
</div>
@endsection
