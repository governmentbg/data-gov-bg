@extends('layouts.app')

@section('content')
    <div class="container">
        @include('partials.alerts-bar')
        @include('partials.public-dataset-nav-bar')
        @include('components.public-dataset-view', ['rootUrl' => '/data'])
    </div>
@endsection
