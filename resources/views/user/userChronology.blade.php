@extends('layouts.app')

@section('content')
<div class="container">
    <div class="col-xs-12 m-t-md col-lg-offset-1">
        <div class="col-xs-12 col-sm-12">
            <div class="filter-content">
                <div class="col-md-12 col-lg-offset-2">
                    <div class="row">
                        <div class="col-sm-12">
                            <div>
                                <ul class="nav filter-type right-border">
                                    <li><a href="{{ url('/users/list') }}">{{ trans_choice(__('custom.users'), 2) }}</a></li>
                                    <li><a href="{{ url('/user/profile/'. $user->id) }}">{{ trans_choice(__('custom.users'), 1) }}</a></li>
                                    <li><a href="{{ route('data', ['user' => [$user->id]]) }}">{{ __('custom.data') }}</a></li>
                                    <li><a class="active" href="{{ url('/user/profile/chronology/'. $user->id) }}">{{ __('custom.chronology') }}</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @include('partials.pagination')
            <div class="row">
                @if (count($chronology))
                    <div class="col-xs-12 p-sm m-t-sm chronology">
                        @foreach ($chronology as $chr)
                            @php
                                if (isset($actionObjData[$chr->module][$chr->action_object])) {
                                    $objName = $actionObjData[$chr->module][$chr->action_object]['obj_name'];
                                    $objModule = $actionObjData[$chr->module][$chr->action_object]['obj_module'];
                                    $objType = $actionObjData[$chr->module][$chr->action_object]['obj_type'];
                                    $objView = $actionObjData[$chr->module][$chr->action_object]['obj_view'];
                                    $parentObjId = $actionObjData[$chr->module][$chr->action_object]['parent_obj_id'];
                                } else {
                                    $objName = '';
                                    $objModule = '';
                                    $objType = '';
                                    $objView = '';
                                    $parentObjId = '';
                                }
                            @endphp
                            <div class="row">
                                <div class="col-xs-1 info-icon">
                                    <img class="img-thumnail m-xs m-t-md" src="{{ asset('img/'. $objType .'-icon.svg') }}"/>
                                </div>
                                <div class="col-xs-11 p-h-sm">
                                    <div class="col-md-10 col-xs-10 m-t-md p-l-none">
                                        <p>
                                            <a href="{{ url('/user/profile/'. $chr->user_id) }}">
                                                <b>{{ ($chr->user_firstname || $chr->user_lastname) ? trim($chr->user_firstname .' '. $chr->user_lastname) : $chr->user }}</b>
                                            </a>
                                            {{ $actionTypes[$chr->action]['name'] .' '. $objModule }}
                                            <a href="{{ url($objView) }}">
                                                <b>"{{ $objName }}"</b>
                                            </a>
                                            @if ($parentObjId != '')
                                                {{ $actionTypes[$chr->action]['linkWord'] }}
                                                {{ $actionObjData[$chr->module][$chr->action_object]['parent_obj_module'] }}
                                                 <a href="{{ url($actionObjData[$chr->module][$chr->action_object]['parent_obj_view']) }}">
                                                    <b>{{ $actionObjData[$chr->module][$chr->action_object]['parent_obj_name'] }}</b>
                                                </a>
                                            @endif
                                            {{ sprintf(
                                                __('custom.at_x_time_on_date'),
                                                date('H:i', strtotime($chr->occurrence)),
                                                date('d.m.Y', strtotime($chr->occurrence))
                                            ) }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <div class="row">
                        <div class="col-sm-9 text-center">
                            {{ $pagination->render() }}
                        </div>
                    </div>
                @else
                    <div class="col-sm-9 m-t-xl text-center no-info">
                        {{ __('custom.no_info') }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
