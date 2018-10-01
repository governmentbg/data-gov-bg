@extends('layouts.app')

@section('content')
    <div class="container">
        @include('partials.alerts-bar')
        @include('partials.public-dataset-nav-bar', ['extended' => true])
        @include('components.public-dataset-view', ['routeName' => 'dataResourceView'])
    </div>
@endsection
