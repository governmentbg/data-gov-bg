<div class="row">
    <div class="col-xs-12">
        <div class="row">
            @if (count($chronology))
                @if (isset($pagination))
                    <div class="row">
                        <div class="col-sm-12 m-t-md text-center">
                            {{ $pagination->render() }}
                        </div>
                    </div>
                @endif
                <div class="col-xs-12 col-xs-offset-1 chronology">
                    @foreach ($chronology as $chr)
                        @php
                            $actObj = [];
                            if (isset($actionObjData[$chr->module][$chr->action_object])) {
                                $actObj = $actionObjData[$chr->module][$chr->action_object];
                            }
                            $objName = isset($actObj['obj_name']) ? $actObj['obj_name'] : '';
                            $objModule = isset($actObj['obj_module']) ? $actObj['obj_module'] : '';
                            $objType = isset($actObj['obj_type']) ? $actObj['obj_type'] : '';
                            $objView = isset($actObj['obj_view']) ? $actObj['obj_view'] : '';
                            $parentObjId = isset($actObj['parent_obj_id']) ? $actObj['parent_obj_id'] : '';
                            if (isset($actObj['obj_owner_id'])) {
                                $objOwnerName = isset($actObj['obj_owner_name']) ? $actObj['obj_owner_name'] : '';
                                $objOwnerView = isset($actObj['obj_owner_view']) ? $actObj['obj_owner_view'] : '';
                                $objOwnerLogo = isset($actObj['obj_owner_logo']) ? $actObj['obj_owner_logo'] : '';
                            } else {
                                if ($chr->user_firstname || $chr->user_lastname) {
                                    $objOwnerName = trim($chr->user_firstname .' '. $chr->user_lastname);
                                } else {
                                    $objOwnerName = $chr->user;
                                }
                                $objOwnerView = '/user/profile/'. $chr->user_id;
                                $objOwnerLogo = null;
                            }
                        @endphp
                        <div class="row">
                            <div class="col-xs-1 info-icon">
                                <img class="img-responsive m-xs m-t-md" src="{{ asset('img/'. $objType .'-icon.svg') }}"/>
                            </div>
                            <div class="col-xs-11 p-h-sm">
                                <div class="col-md-1 col-xs-2 logo-img">
                                    @if (isset($objOwnerLogo))
                                        <a href="{{ url($objOwnerView) }}">
                                            <img class="img-responsive m-xs m-t-sm" src="{{ $objOwnerLogo }}" title="{{ $objOwnerName }}"/>
                                        </a>
                                    @endif
                                </div>
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
                                            {{ $actObj['parent_obj_module'] }}
                                             <a href="{{ url($actObj['parent_obj_view']) }}">
                                                <b>{{ $actObj['parent_obj_name'] }}</b>
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
                @if (isset($pagination))
                    <div class="row">
                        <div class="col-sm-12 text-center">
                            {{ $pagination->render() }}
                        </div>
                    </div>
                @endif
            @else
                <div class="col-sm-12 m-t-xl text-center no-info">
                    {{ __('custom.no_info') }}
                </div>
            @endif
        </div>
    </div>
</div>
