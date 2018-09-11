@extends('layouts.app')

@section('content')
<div class="container">
    @include('partials.alerts-bar')
    @include('partials.user-nav-bar', ['view' => 'datasets'])
    @include('components.datasets.resource_create')
</div>
@endsection
