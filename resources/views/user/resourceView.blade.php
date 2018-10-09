@extends('layouts.app')

@section('content')
<div class="container">
    @include('partials.alerts-bar')
    @include('partials.user-nav-bar', ['view' => 'user'])
    <div class="col-xs-12 m-t-md">
        @include('components.datasets.resource_view')
    </div>
</div>
@endsection
