@if (isset($pagination))
    <div class="row">
        <div class="col-xs-12 text-center">
            {{ $pagination->render() }}
        </div>
    </div>
@endif
<div class="row">
    @include('partials.org-info', ['organisation' => $organisation])
    <div class="col-sm-9 col-xs-12 p-md">
        @if (count($chronology))
            <div class="col-xs-12 p-sm">
                @foreach ($chronology as $actionHistory)
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
                        <div class="col-md-1 p-l-none">
                            <img class="img-thumnail" src="{{ asset('img/'. $objType .'-icon.svg') }}"/>
                        </div>
                        <div class="col-md-11 p-h-sm">
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
        @else
            <div class="col-sm-9 m-t-xl no-info">
                {{ __('custom.no_info') }}
            </div>
        @endif
    </div>
</div>
@if (isset($pagination))
    <div class="row">
        <div class="col-xs-12 text-center">
            {{ $pagination->render() }}
        </div>
    </div>
@endif
