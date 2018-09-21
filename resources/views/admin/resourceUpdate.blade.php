@extends('layouts.app')

@section('content')
<div class="container">
    @include('partials.alerts-bar')
    @include('partials.admin-nav-bar', ['view' => 'datasets'])
    @include('components.datasets.resource_update_version', ['admin' => true])
</div>
@endsection