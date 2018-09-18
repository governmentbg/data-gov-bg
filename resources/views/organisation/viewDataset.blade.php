@extends('layouts.app')

@section('content')
    <div class="container">
        @include('partials.alerts-bar')
        @include('partials.public-org-dataset-nav-bar')
        @include('components.public-dataset-view', ['rootUrl' => '/organisation/datasets', 'user' => []])
    </div>
@endsection
