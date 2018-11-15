@extends('layouts.mail')

@section('title')
    <b>{{ __('custom.newsletter') }}</b>
@endsection

@section('content')
    {{ __('custom.greetings') }}, {{ $user }}!<br>
    <div>
        @if (isset($actionsHistory) && count($actionsHistory))
            <div>
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
                        } else {
                            $objOwnerId = $actionHistory->user_id;
                            $objOwnerView = '/user/profile/'. $actionHistory->user_id;
                        }

                        if (!empty($actionHistory->user_firstname) || !empty($actionHistory->user_lastname)) {
                            $objOwnerName = trim($actionHistory->user_firstname .' '. $actionHistory->user_lastname);
                        } elseif (!empty($actionHistory->user)) {
                            $objOwnerName = $actionHistory->user;
                        } else {
                            $objOwnerName = null;
                        }

                        $objId = $actionObjData[$actionHistory->module][$actionHistory->action_object]['obj_id'];
                        $objName = $actionObjData[$actionHistory->module][$actionHistory->action_object]['obj_name'];
                        $objModule = $actionObjData[$actionHistory->module][$actionHistory->action_object]['obj_module'];
                        $objType = $actionObjData[$actionHistory->module][$actionHistory->action_object]['obj_type'];
                        $objView = $actionObjData[$actionHistory->module][$actionHistory->action_object]['obj_view'];
                        $parentObjId = $actionObjData[$actionHistory->module][$actionHistory->action_object]['parent_obj_id'];
                    @endphp
                    <div style="width: 100%;">
                        <div style="width: 20%; float: left;">
                            <img
                                src="{{ $message->embed('img/'. $objType .'-icon.png') }}"
                                style="width: 55px; height: 55px; margin-top: 25px;"
                            />
                        </div>
                        <div style="width: 78%; float: left;">
                            <div>
                                <div>{{ __('custom.date') }}: {{ date('d.m.Y', strtotime($actionHistory->occurrence)) }}</div>
                                <h3 style="font-family: inherit; font-weight: 800; line-height: 1.1;">
                                    <a
                                        href="{{ url($objOwnerView) }}"
                                        style="color: black; text-decoration: none;"
                                    >{{ $objOwnerName }}</a></h3>
                                <p>
                                    {{ $actionTypes[$actionHistory->action]['name'] .' '. $objModule }}

                                    @if ($objView != '')
                                        <a href="{{ url($objView) }}" style="text-decoration: none; color: black;"><b>{{ $objName }}</b></a>
                                    @else
                                        <b>{{ $objName }}</b>
                                    @endif

                                    @if ($parentObjId != '')
                                        {{ $actionTypes[$actionHistory->action]['linkWord'] }}
                                        {{ $actionObjData[$actionHistory->module][$actionHistory->action_object]['parent_obj_module'] }}
                                        <a
                                            href="{{ url($actionObjData[$actionHistory->module][$actionHistory->action_object]['parent_obj_view']) }}"
                                            style="text-decoration: none; color: black;"
                                        >
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
        @else
            <div>
                {{ __('custom.no_info') }}
            </div>
        @endif
    </div>
@endsection
