@extends('layouts.app')

@section('content')
<div class="container">
    @include('partials.alerts-bar')
    @include('partials.user-nav-bar', ['view' => 'group'])
    <div class="row">
        <div class="col-sm-3 col-xs-12 sidenav">
            <span class="badge badge-pill m-t-lg new-data"><a  href="{{ url('/user/groupRegistration') }}">{{ __('custom.create_group') }}</a></span>
        </div>
        <div class="col-sm-9 col-xs-12 m-t-md">
            <div class="filter-content tex">
                <ul class="nav filter-type right-border">
                    <li><a class="active p-l-none" href="{{ url('/user/groupView') }}">{{ trans_choice(__('custom.groups'), 2) }}</a></li>
                    <li><a href="{{ url('/user/groupMembers') }}">{{ __('custom.members') }}</a></li>
                </ul>
            </div>
        </div>
    </div>
    <div class="col-xs-12 page-content p-sm">
        <div class="col-xs-12 list-orgs">
            <div class="row">
                <div class="col-xs-12 p-md">
                    <div class="col-xs-12 org-logo">
                        <img class="img-responsive" src="{{ !empty($group->logo) ? $group->logo : '' }}"/>
                    </div>
                    <div class="col-xs-12">
                        <h3>{{ $group->name }}</h3>
                        <p>{{ $group->description }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
