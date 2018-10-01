@extends('layouts.app')

@section('content')
    <div class="container">
        @include('partials.alerts-bar')
        @include('partials.public-org-dataset-nav-bar', ['extended' => true])
        @include('components.public-dataset-view', ['routeName' => 'orgDataResourceView', 'user' => []])
    </div>
@endsection
