@extends('layouts.app')

@section('content')
<div class="container">
    @include('partials.alerts-bar')
    @include('partials.admin-nav-bar', ['view' => 'dataset'])
    <div class="col-xs-12 m-t-md p-l-r-none">
        @include('components.datasets.resource_view', ['admin' => true])
    </div>
</div>
@endsection
