@extends('layouts.app')

@section('content')
    <div class="container">
        @include('partials.alerts-bar')
        @include('partials.public-dataset-nav-bar', ['extended' => true])
        @include('components.public-dataset-view', ['routeName' => 'dataResourceView'])
        @if (isset($discussion))
            <div class="row discussion">
                @include('vendor.chatter.discussion')
            </div>
        @endif
    </div>
@endsection
