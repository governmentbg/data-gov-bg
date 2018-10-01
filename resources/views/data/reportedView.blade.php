@extends('layouts.app')

@section('content')
    <div class="container">
        @include('partials.alerts-bar')
        @include('partials.public-dataset-reported-nav-bar', ['extended' => true])
        @include('components.public-dataset-view', ['routeName' => 'reportedResourceView'])
    </div>
@endsection
