@extends('layouts.app')

@section('content')
<div class="container">
    @include('partials.alerts-bar')
    @include('partials.user-nav-bar', ['view' => 'group'])
    <div class="row">
        <div class="col-sm-3 cl-xs-12 sidenav m-b-md">
        </div>
        <div class="col-sm-9 col-xs-12 m-t-md">
            <div class="filter-content tex">
                <ul class="nav filter-type right-border">
                    <li><a class="p-l-none" href="{{ url('/user/viewGroup') }}">{{ trans_choice(__('custom.groups'), 2) }}</a></li>
                    <li><a class="active" href="{{ url('/user/groupMembers') }}">{{ __('custom.members') }}</a></li>
                </ul>
            </div>
        </div>
    </div>
    <div class="col-sm-3 cl-xs-12 sidenav m-b-md">
    </div>
    <div class="col-sm-9 cl-xs-12 p-l-none">
        <div class="filter-content tex">
            <div class="m-r-md p-h-xs col-md-6">
                <input class="rounded-input" type="text">
            </div>
        </div>
        <div class="m-r-md p-h-xs col-md-6">
            <div class="col-xs-6 p-r-none">
                <span>{{ __('custom.add_members') }}</span>
            </div>
            <div class="col-xs-6 p-r-none">
                <ul class="input-border-r-12">
                    <li><a href="{{ url('/user/registration') }}">{{ __('custom.new_user') }}</a></li>
                    <li>{{ __('custom.existing_user') }}</li>
                    <li>{{ __('custom.invite_by_mail') }}</li>
                </ul>
            </div>
        </div>
        <div class="col-xs-12 page-content text-left p-l-none">
            @for ($i=1; $i <=3; $i++)
                <div class="col-xs-12 p-l-none">
                    <div class="col-xs-12">
                        <h3>{{ __('custom.member_name') }}</h3>
                        <span class="badge badge-pill m-r-md m-b-sm"><a href="{{ url('/user/groupEdit') }}">{{ __('custom.edit') }}</a></span>
                        <span class="badge badge-pill m-b-sm">
                            <a
                                href="#"
                                onclick="return confirm('Изтриване на група?');"
                                >{{ __('custom.remove') }}</a>
                        </span>
                    </div>
                </div>
            @endfor
        </div>
    </div>
</div>
@endsection
