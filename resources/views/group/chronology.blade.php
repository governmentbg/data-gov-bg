@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-sm-9 col-xs-11 p-sm col-sm-offset-3">
                <div class="filter-content">
                    <div class="col-md-12">
                        <div class="row">
                            <div class="col-xs-12 p-l-r-none">
                                <div>
                                    <ul class="nav filter-type right-border">
                                        <li><a class="p-l-none" href="{{ url('/groups') }}">{{ untrans('custom.groups', 2) }}</a></li>
                                        <li><a href="{{ url('/groups/view/'. $group->uri) }}">{{ untrans('custom.groups', 1) }}</a></li>
                                        <li><a href="{{ route('data', ['group' => [$group->id]]) }}">{{ __('custom.data') }}</a></li>
                                        <li><a class="active" href="{{ url('/groups/chronology/'. $group->uri) }}">{{ __('custom.chronology') }}</a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @include('components.public-chronology')
    </div>
@endsection
