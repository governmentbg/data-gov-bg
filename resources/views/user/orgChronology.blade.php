@extends('layouts.app')

@section('content')
<div class="container">
    @include('partials.alerts-bar')

    @if (Auth::user()->is_admin)
        @include('partials.admin-nav-bar', ['view' => 'organisation'])
    @else
        @include('partials.user-nav-bar', ['view' => 'organisation'])
    @endif

    @include('partials.org-nav-bar', ['view' => 'chronology', 'organisation' => $organisation])
    @include('components.chronology', compact('organisation', 'chronology', 'pagination', 'actionObjData', 'actionTypes', 'pagination'))
</div>
@endsection
