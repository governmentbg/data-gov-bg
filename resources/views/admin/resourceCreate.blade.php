@extends('layouts.app')

@section('content')
<div class="container">
    @include('partials.alerts-bar')
    @include('partials.admin-nav-bar', ['view' => 'dataset'])
    @include('components.datasets.resource_create', ['admin' => true])
</div>
@endsection
