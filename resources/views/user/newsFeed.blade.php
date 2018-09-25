@extends('layouts.app')

@section('content')
<div class="container">
    @include('partials.alerts-bar')
    @if (Auth::user()->is_admin)
        @include('partials.admin-nav-bar', ['view' => 'newsfeed'])
    @else
        @include('partials.user-nav-bar', ['view' => 'newsfeed'])
    @endif
    <div class="col-xs-12 sidenav m-t-lg m-b-lg">
        <span class="my-profile m-l-sm">{{uctrans('custom.notifications')}}</span>
    </div>
    <div class="col-xs-12">
        <div class="row">
            <div class="col-md-3 col-sm-4 col-xs-12 sidenav">
                <ul class="nav">
                    <li class="js-show-submenu m-t-lg">
                        <ul class="sidebar-submenu open">
                            <li><a class="{{ ($filter == 'users') ? 'active' : '' }}" href="{{ url('/user/newsFeed/users') }}">{{ utrans('custom.users', 2) }}</a></li>
                            <li><a class="{{ ($filter == 'organisations') ? 'active' : '' }}" href="{{ url('/user/newsFeed/organisations') }}">{{ utrans('custom.organisations', 2) }}</a></li>
                            <li><a class="{{ ($filter == 'groups') ? 'active' : '' }}" href="{{ url('/user/newsFeed/groups') }}">{{ utrans('custom.groups', 2) }}</a></li>
                            <li><a class="{{ ($filter == 'datasets') ? 'active' : '' }}" href="{{ url('/user/newsFeed/datasets') }}">{{ __('custom.data_sets') }}</a></li>
                            <li><a class="{{ ($filter == 'categories') ? 'active' : '' }}" href="{{ url('/user/newsFeed/categories') }}">{{ __('custom.main_topic') }}</a></li>
                            <li><a class="{{ ($filter == 'tags') ? 'active' : '' }}" href="{{ url('/user/newsFeed/tags') }}">{{ utrans('custom.tags', 2) }}</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
            <div class="col-md-9 col-sm-8 col-xs-12 p-sm">
                @if (count($filterData) && ($objIdFilter || count($actionsHistory)))
                    @php
                        $path = url('/') .'/user/newsFeed/'. $filter;
                    @endphp
                    <div class="form-group row">
                        <label for="{{ $filterData['key'] }}" class="col-sm-3 col-xs-12 col-form-label"></label>
                        <div class="col-sm-6 col-sm-pull-1 text-center">
                            <select
                                class="input-border-r-12 form-control js-autocomplete"
                                name="{{ $filterData['key'] }}"
                                id="filter"
                                onchange="document.location.href = '{{ $path }}' + '/' + this.value"
                                data-live-search="true"
                            >
                                <option value="">{{  __($filterData['label']) }}</option>
                                @foreach ($filterData['data'] as $id => $name)
                                    <option value="{{ $id }}"{{ ($id == $objIdFilter) ? 'selected' : ''}}>{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                @endif
                @if (count($actionsHistory))
                    @include('partials.pagination')
                    <div class="col-xs-12 p-sm chronology">
                        @foreach ($actionsHistory as $actionHistory)
                            @php
                                $tsDiff = time() - strtotime($actionHistory->occurrence);
                                $min = floor($tsDiff / 60);
                                $hours = floor($tsDiff / 3600);
                                $days = floor($tsDiff / 86000);
                                if (isset($actionObjData[$actionHistory->module][$actionHistory->action_object]['obj_owner_id'])) {
                                    $objOwnerId = $actionObjData[$actionHistory->module][$actionHistory->action_object]['obj_owner_id'];
                                    $objOwnerName = $actionObjData[$actionHistory->module][$actionHistory->action_object]['obj_owner_name'];
                                    $objOwnerView = $actionObjData[$actionHistory->module][$actionHistory->action_object]['obj_owner_view'];
                                    $objOwnerLogo = $actionObjData[$actionHistory->module][$actionHistory->action_object]['obj_owner_logo'];
                                } else {
                                    $objOwnerId = $actionHistory->user_id;
                                    if ($actionHistory->user_firstname || $actionHistory->user_lastname) {
                                        $objOwnerName = trim($actionHistory->user_firstname .' '. $actionHistory->user_lastname);
                                    } else {
                                        $objOwnerName = $actionHistory->user;
                                    }
                                    $objOwnerView = '/user/profile/'. $actionHistory->user_id;
                                    $objOwnerLogo = null;
                                }
                                $objId = $actionObjData[$actionHistory->module][$actionHistory->action_object]['obj_id'];
                                $objName = $actionObjData[$actionHistory->module][$actionHistory->action_object]['obj_name'];
                                $objModule = $actionObjData[$actionHistory->module][$actionHistory->action_object]['obj_module'];
                                $objType = $actionObjData[$actionHistory->module][$actionHistory->action_object]['obj_type'];
                                $objView = $actionObjData[$actionHistory->module][$actionHistory->action_object]['obj_view'];
                                $parentObjId = $actionObjData[$actionHistory->module][$actionHistory->action_object]['parent_obj_id'];
                            @endphp
                            <div class="row">
                                <div class="col-xs-1 p-l-none">
                                    <img class="img-thumnail" src="{{ asset('img/'. $objType .'-icon.svg') }}"/>
                                </div>
                                <div class="col-xs-11 p-h-sm">
                                    <div class="col-md-1 col-xs-2 logo-img">
                                    @if (isset($objOwnerLogo))
                                        <a href="{{ url($objOwnerView) }}">
                                            <img class="img-responsive" src="{{ $objOwnerLogo }}"/>
                                        </a>
                                    @endif
                                    </div>
                                    <div class="col-md-10 col-xs-10">
                                        <div>{{ __('custom.date_added') }}: {{ date('d.m.Y', strtotime($actionHistory->occurrence)) }}</div>
                                        <h3><a href="{{ url($objOwnerView) }}">{{ $objOwnerName }}</a></h3>
                                        <p>
                                            {{ $actionTypes[$actionHistory->action]['name'] .' '. $objModule }}
                                            @if ($objView != '')
                                                <a href="{{ url($objView) }}"><b>{{ $objName }}</b></a>
                                            @else
                                                <b>{{ $objName }}</b>
                                            @endif
                                            @if ($parentObjId != '')
                                                {{ $actionTypes[$actionHistory->action]['linkWord'] }}
                                                {{ $actionObjData[$actionHistory->module][$actionHistory->action_object]['parent_obj_module'] }}
                                                <a href="{{ url($actionObjData[$actionHistory->module][$actionHistory->action_object]['parent_obj_view']) }}">
                                                    <b>{{ $actionObjData[$actionHistory->module][$actionHistory->action_object]['parent_obj_name'] }}</b>
                                                </a>
                                            @endif
                                            -
                                            @if ($hours == 24)
                                                {{ __('custom.one_day_ago') }}
                                            @elseif ($hours > 24)
                                                {{ sprintf(__('custom.x_days_ago'), $days) }}
                                            @elseif ($min == 60)
                                            {{ __('custom.one_hour_ago') }}
                                            @elseif ($min > 60)
                                                {{ sprintf(__('custom.x_hours_ago'), $hours) }}
                                            @elseif ($min == 1)
                                                {{ __('custom.one_minute_ago') }}
                                            @else
                                                {{ sprintf(__('custom.x_minutes_ago'), $min) }}
                                            @endif
                                        </p>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    @include('partials.pagination')
                @else
                    <div class="col-sm-9 m-t-xl no-info">
                        {{ __('custom.no_info') }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
