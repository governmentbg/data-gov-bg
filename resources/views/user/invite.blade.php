@extends('layouts.app')

@section('content')
<div class="container invite">
    <div class="row">
        <div class="col-xs-12 m-t-md">
            <div class="flash-message">
                @foreach (['danger', 'warning', 'success', 'info'] as $msg)
                    @if(Session::has('alert-' . $msg))
                        <p class="alert alert-{{ $msg }}">
                            {{ Session::get('alert-' . $msg) }}
                            <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                        </p>
                    @endif
                @endforeach
            </div>
            <div class="filter-content">
                <div class="col-md-12">
                    <div class="col-md-3 col-sm-12 p-l-none"><span class="my-profile m-b-lg m-l-sm">{{ __('custom.my_profile') }}</span></div>
                    <div class="col-md-7 col-sm-12 p-l-none">
                        <div>
                            <ul class="nav filter-type right-border">
                                <li><a href="{{ url('/user') }}">{{ __('custom.notifications') }}</a></li>
                                <li><a href="{{ url('/user/datasets') }}">{{ __('custom.my_data') }}</a></li>
                                <li><a href="{{ url('/user/userGroups') }}">{{ trans_choice(__('custom.groups'), 2) }}</a></li>
                                <li><a href="{{ url('/user/organisations') }}">{{ trans_choice(__('custom.organisations'), 2) }}</a></li>
                                <li><a href="{{ url('/user/settings') }}">{{ __('custom.settings') }}</a></li>
                                <li><a class="active" href="{{ url('/user/invite') }}">{{ __('custom.invite') }}</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="frame col-lg-6">
            <form class="form-horizontal" method="post">
                {{ csrf_field() }}
                <div class="form-group text-center">
                    <h2>{{ __('custom.add_user_by_mail') }}<h2>
                </div>
                <div class="form-group">
                    <label for="email" class="col-lg-2 col-form-label">{{ __('custom.email') }}: </label>
                    <div class="col-lg-10">
                        <input
                            id="email"
                            name="email"
                            type="email"
                            class="input-border-r-12 form-control"
                        >
                    </div>
                </div>
                @if (Auth::user()->is_admin)
                    <div class="form-group">
                        <label for="role" class="col-lg-2 col-form-label">{{ __('custom.roles') }}: </label>
                        <div class="col-lg-10">
                            <select class="input-border-r-12 form-control open-select" name="role" id="role" size="{{ count($roleList) }}">
                                @foreach($roleList as $role)
                                    <option
                                        value="{{ $role->id }}"
                                    >{{ $role->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                @endif
                <button type="submit" name="send" class="m-l-md btn btn-primary m-b-sm pull-right">{{ uctrans('custom.invite') }}</button>
                @if (Auth::user()->is_admin)
                    <button type="submit" name="generate" class="m-l-md btn btn-primary m-b-sm pull-right">{{ __('custom.generate') }}</button>
                @endif
            </form>
        </div>
    </div>
</div>
@endsection
