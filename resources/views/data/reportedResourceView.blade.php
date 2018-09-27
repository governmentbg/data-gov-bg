@extends('layouts.app')

@section('content')
    @include('partials.add-signal', ['postUrl' => 'data/resource/sendSignal'])
    <div class="container">
        @include('partials.alerts-bar')
        @include('partials.public-dataset-reported-nav-bar')
        @include('components.public-resource-view', ['rootUrl' => '/data/reported/view', 'routeName' => 'reportedResourceView'])
    </div>
@endsection
