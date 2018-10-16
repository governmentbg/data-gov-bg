@extends('layouts.app')

@section('content')
<div class="container">
    @include('partials.alerts-bar')
    @include('partials.admin-nav-bar', ['view' => 'organisation'])
    @include('partials.org-nav-bar', ['view' => 'dataset', 'organisation' => $organisation])
    @include('partials.pagination')
    @include('components.datasets.org_datasets', ['admin' => true])
    @if (isset($pagination))
        <div class="row">
            <div class="col-xs-12 text-center pagination">
                {{ $pagination->render() }}
            </div>
        </div>
    @endif
</div>
@endsection
