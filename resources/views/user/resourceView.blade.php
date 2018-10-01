@extends('layouts.app')

@section('content')
<div class="container">
    @include('partials.alerts-bar')
    @include('partials.user-nav-bar', ['view' => 'user'])
    @include('components.datasets.resource_view')
</div>
@endsection
