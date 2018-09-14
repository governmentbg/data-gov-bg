@extends('layouts.app')

@section('content')
    <div class="container">
        @include('partials.alerts-bar')
        @include('partials.admin-nav-bar', ['view' => 'dataset'])
        @include('components.datasets.dataset_view', ['admin' => 'true'])
    </div>
@endsection
