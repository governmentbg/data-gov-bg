@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-xs-12 m-t-md">
            <div class="row">
                <div class="col-sm-3 cl-xs-12 sidenav m-b-md">
                    <span class="my-profile m-b-lg m-b-lg">{{ __('custom.my_profile') }}</span>
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
                                            <li><a class="active" href="{{ url('/user/userGroups') }}">{{ trans_choice(__('custom.groups'), 2) }}</a></li>
                                            <li><a href="{{ url('/user/organisations') }}">{{ trans_choice(__('custom.organisations'), 2) }}</a></li>
                                            <li><a href="{{ url('/user/settings') }}">{{ __('custom.settings') }}</a></li>
                                            <li><a href="{{ url('/user/invite') }}">{{ __('custom.invite') }}</a></li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="col-xs-12 p-l-none m-t-md">
                                    <ul class="nav filter-type right-border">
                                        <li><a class="p-l-none" href="{{ url('/user/groupView') }}">{{ trans_choice(__('custom.groups'), 2) }}</a></li>
                                        <li><a class="active" href="{{ url('/user/groupMembers') }}">{{ __('custom.members') }}</a></li>
                                    </ul>
                                </div>
                                <div class="m-r-md p-h-xs col-md-6">
                                    <input class="rounded-input" type="text">
                                </div>
                                <div class="m-r-md p-h-xs col-md-6">
                                    <div class="col-xs-6 p-r-none">
                                        <span>{{ __('custom.add_members') }}</span>
                                    </div>
                                    <div class="col-xs-6 p-r-none">
                                        <ul class="input-border-r-12">
                                            <li><a href="{{ url('/user/registration') }}">{{ __('custom.new_user') }}</a></li>
                                            <li>{{ __('custom.existing_user') }}</li>
                                            <li>{{ __('custom.invite_by_mail') }}</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xs-12 page-content text-left p-l-none">
                        @for ($i=1; $i <=3; $i++)
                            <div class="col-xs-12 p-l-none">
                                <div class="col-xs-12">
                                    <h3>{{ __('custom.member_name') }}</h3>
                                    <span class="badge badge-pill m-r-md m-b-sm"><a href="{{ url('/user/groupEdit') }}">{{ __('custom.edit') }}</a></span>
                                    <span class="badge badge-pill m-b-sm">
                                        <a
                                            href="#"
                                            onclick="return confirm('Изтриване на група?');"
                                            >{{ __('custom.remove') }}</a>
                                    </span>
                                </div>
                            </div>
                        @endfor
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
