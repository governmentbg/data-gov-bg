@extends('layouts.app')

@section('content')
    @include('partials.add-signal', ['postUrl' => 'organisation/resource/sendSignal'])
    <div class="container">
        @include('partials.alerts-bar')
        @include('partials.public-org-dataset-nav-bar')
        @include('components.public-resource-view', ['rootUrl' => '/organisation/dataset', 'user' => [], 'showSignals' => false])
    </div>
@endsection
