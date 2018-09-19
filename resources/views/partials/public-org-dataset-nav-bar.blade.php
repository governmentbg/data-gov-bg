<div class="row">
    <div class="col-sm-9 col-xs-11 p-sm col-sm-offset-3">
        <div class="filter-content">
            <div class="col-md-12">
                <div class="row">
                    <div class="col-xs-12 p-l-r-none">
                        <ul class="nav filter-type right-border">
                            <li><a class="p-l-none" href="{{ url('/organisation/profile/'. $organisation->uri) }}">{{ __('custom.profile') }}</a></li>
                            <li><a class="active" href="{{ url('/organisation/'. $organisation->uri .'/datasets') }}">{{ __('custom.data') }}</a></li>
                            <li><a href="{{ url('/organisation/'. $organisation->uri .'/chronology') }}">{{ __('custom.chronology') }}</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>