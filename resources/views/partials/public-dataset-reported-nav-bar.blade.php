<div class="row">
    <div class="col-sm-9 col-xs-11 pull-right p-sm">
        <div class="filter-content">
            <div class="col-md-12">
                <div class="row">
                    <div class="col-md-8 col-sm-10 col-xs-10 p-l-none">
                        <div>
                            <ul class="nav filter-type right-border">
                                <li><a class="p-l-none" href="{{ url('/data') }}">{{ __('custom.data') }}</a></li>
                                <li><a href="{{ url('/data/linkedData') }}">{{ __('custom.linked_data') }}</a></li>
                                <li><a class="active" href="{{ url('/data/reported') }}">{{ __('custom.signal_data') }}</a></li>
                            </ul>
                        </div>
                        @if (isset($extended) && $extended)
                            <div class="m-t-sm">
                                <ul class="nav filter-type right-border">
                                    <li><a class="p-l-none" href="{{ url('/groups') }}">{{ untrans('custom.groups', 2) }}</a></li>
                                    <li><a href="{{ url('/data/chronology') }}">{{ __('custom.chronology') }}</a></li>
                                </ul>
                            </div>
                        @endif
                    </div>
                    <div class="col-md-4 col-sm-2 col-xs-2 p-l-none">
                        <div class="col-lg-5 col-md-6 col-sm-12 col-xs-12 exclamation-sign">
                            <img src="{{ asset('img/reported.svg') }}">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>