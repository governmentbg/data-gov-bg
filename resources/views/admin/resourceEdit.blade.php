@extends('layouts.app')

@section('content')
<div class="container">
    @include('partials.alerts-bar')
    @include('partials.admin-nav-bar', ['view' => 'datasets'])
    @include('components.datasets.resource_edit_metadata', ['admin' => true])
</div>
@endsection