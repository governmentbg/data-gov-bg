@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-xs-12 m-t-md">
            <div class="row">
                <div class="col-sm-3 cl-xs-12 sidenav m-b-md">
                    <span class="my-profile m-b-lg m-b-lg">{{ __('custom.my_profile') }}</span>
                    <span class="badge badge-pill m-t-lg new-data"><a  href="{{ url('/user/groupRegistration') }}">{{ __('custom.create_group') }}</a></span>
                </div>
                <div class="col-sm-9 cl-xs-12">
                    <div class="filter-content tex">
                        <div class="col-md-12">
                            <div class="row">
                                <div class="col-sm-12 p-l-none">
                                    <div>
                                        <ul class="nav filter-type right-border">
                                            <li><a class="p-l-none" href="{{ url('/user') }}">{{ __('custom.notifications') }}</a></li>
                                            <li><a href="{{ url('/user/datasets') }}">{{ __('custom.my_data') }}</a></li>
                                            <li><a class="active" href="{{ url('/user/userGroups') }}">{{ utrans('custom.groups', 2) }}</a></li>
                                            <li><a href="{{ url('/user/organisations') }}">{{ utrans('custom.organisations', 2) }}</a></li>
                                            <li><a href="{{ url('/user/settings') }}">{{__('custom.settings') }}</a></li>
                                            <li><a href="{{ url('/user/invite') }}">{{ __('custom.invite') }}</a></li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="col-xs-12 p-l-none m-t-md">
                                    <ul class="nav filter-type right-border">
                                        <li><a class="active p-l-none" href="{{ url('/user/groupView') }}">{{ utrans('custom.groups') }}</a></li>
                                        <li><a href="{{ url('/user/groupMembers') }}">{{ __('custom.members') }}</a></li>
                                    </ul>
                                </div>
                            </div>
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
        </div>
    </div>
</div>
@endsection
