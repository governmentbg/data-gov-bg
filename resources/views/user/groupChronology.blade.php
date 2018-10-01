@extends('layouts.app')

@section('content')
<div class="container">
    @include('partials.alerts-bar')

    @if (Auth::user()->is_admin)
        @include('partials.admin-nav-bar', ['view' => 'group'])
    @else
        @include('partials.user-nav-bar', ['view' => 'group'])
    @endif

    @include('partials.group-nav-bar', ['view' => 'chronology', 'group' => $organisation])
    @include('components.chronology', compact('organisation', 'chronology', 'pagination', 'actionObjData', 'actionTypes', 'pagination'))
</div>
@endsection
