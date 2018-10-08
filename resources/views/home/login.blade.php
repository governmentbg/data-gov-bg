@extends('layouts.app')

@section('content')
<div class="container m-b-lg login-form">
    <div class="m-b-lg text-center">
        <img class="responsive logo-login" src="{{ asset('img/opendata-logo-color.svg') }}">
    </div>
    <div>
        @foreach (['danger', 'warning', 'success', 'info'] as $msg)
            @if (Session::has('alert-' . $msg))
                <p class="alert alert-{{ $msg }}">
                    {{ Session::get('alert-' . $msg) }}
                    <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                </p>
            @endif
        @endforeach
        <form class="form-horizontal" method="POST">
            {{ csrf_field() }}

            <div class="form-group row">
                <label for="username" class="col-xs-12 col-form-label">{{ __('custom.user_name') }}:</label>
                <div class="col-xs-12">
                    <input type="text" class="input-border-r-12 form-control" name="username">
                    @if (!empty($error['username']))
                        <span class="error">{{ $error['username'][0] }}</span>
                    @endif
                </div>
            </div>
            <div class="form-group row">
                <label for="password" class="col-xs-12 col-form-label">{{ __('custom.password') }}:</label>
                <div class="col-xs-12">
                    <input type="password" class="input-border-r-12 form-control" name="password">
                    @if (!empty($error['password']))
                        <span class="error">{{ $error['password'][0] }}</span>
                    @endif
                </div>
            </div>
            <div class="form-group row">
                <label for="remember_me" class="col-xs-6 col-form-label">{{ __('custom.remember_me') }}</label>
                <div class="col-xs-6">
                    <div class="js-check pull-right">
                        <input type="checkbox" name="remember_me" value="1">
                    </div>
                </div>
            </div>
            <div class="form-group row">
                <div class="col-xs-12">
                    <button
                        type="submit"
                        class="col-xs-12 btn btn-primary login-btn js-ga-event"
                        data-ga-action="login"
                        data-ga-label="login attempt"
                        data-ga-category="users"
                    >{{ uctrans('custom.login') }}</button>
                </div>
            </div>
        </form>
        <div class="form-group row">
            <div class="col-xs-12">
                 <a
                    href="{{ url('/password/forgotten') }}"
                    class="col-xs-12 btn btn-primary"
                >{{ uctrans('custom.forgotten_password') }}</a>
            </div>
        </div>
        <div class="form-group row text-center">
            <div class="col-xs-12">
                <a href="{{ url('/registration') }}"><h3>{{ uctrans('custom.new_profile') }}</h3></a>
            </div>
        </div>
    </div>
</div>
@endsection
