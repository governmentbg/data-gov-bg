@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-sm-9 col-xs-12 p-h-sm col-sm-offset-3 col-xs-offset-2">
                @include('partials.org-type-bar', ['orgTypes' => $orgTypes, 'getParams' => ['type' => $organisation->type]])
            </div>
            <div class="col-sm-9 col-xs-12 p-sm col-sm-offset-3 col-xs-offset-1">
                <div class="filter-content">
                    <div class="col-md-12">
                        <div class="row">
                            <div class="col-xs-12 p-l-r-none org-nav-bar">
                                <ul class="nav filter-type right-border">
                                    <li><a href="{{ url('/organisation/profile/'. $organisation->uri) }}">{{ __('custom.profile') }}</a></li>
                                    <li><a href="{{ url('/organisation/'. $organisation->uri .'/datasets') }}">{{ __('custom.data') }}</a></li>
                                    <li><a class="active" href="{{ url('/organisation/chronology/'. $organisation->uri) }}">{{ __('custom.chronology') }}</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @include('components.public-chronology')
        </div>
    </div>
@endsection
