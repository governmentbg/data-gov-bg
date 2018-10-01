@extends('layouts.app')

@section('content')
    <div class="container">
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
                            <div class="col-xs-12 p-l-r-none m-t-sm">
                                <ul class="nav filter-type right-border">
                                    <li><a class="p-l-none" href="{{ route('groups', ['dataset' => $dataset->uri]) }}">{{ untrans('custom.groups', 2) }}</a></li>
                                    <li><a class="active" href="{{ url('/organisation/dataset/chronology/'. $dataset->uri) }}">{{ __('custom.chronology') }}</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @include('components.public-chronology')
    </div>
@endsection
