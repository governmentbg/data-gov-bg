@extends('layouts.app')

@section('content')
<div class="container m-b-lg login-form">
    <div class="m-b-lg text-center">
        <img class="responsive logo-login" src="{{ asset('img/opendata-logo-color.svg') }}">
    </div>
    <div class="m-b-lg">
        <h3 class="text-center">{{ __('custom.pass_change') }}</h3>
    </div>
    <div>
        @foreach (['danger', 'warning', 'success', 'info'] as $msg)
        @if(Session::has('alert-' . $msg))
        <p class="alert alert-{{ $msg }}">
            {{ Session::get('alert-' . $msg) }}
            <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
        </p>
        @endif
        @endforeach
        <form class="form-horizontal" method="POST">
            {{ csrf_field() }}

            <div class="form-group row">
                <label for="password" class="col-xs-12 col-form-label">{{ __('custom.password') }}:</label>
                <div class="col-xs-12">
                    <input type="password" class="input-border-r-12 form-control" name="password">
                    @if (!empty($errors['password']))
                        <span class="error">{{ $errors['password'] }}</span>
                    @endif
                </div>
            </div>
            <div class="form-group row">
                <label for="password_confirm" class="col-xs-12 col-form-label">{{ __('custom.password_confirm') }}:</label>
                <div class="col-xs-12">
                    <input type="password" class="input-border-r-12 form-control" name="password_confirm">
                    @if (!empty($errors['password_confirm']))
                        <span class="error">{{ $errors['password_confirm'] }}</span>
                    @endif
                </div>
            </div>
            <div class="form-group row">
                <div class="col-xs-12">
                    <button type="submit" name="save" class="col-xs-12 btn btn-primary login-btn">{{ utrans('custom.save') }}</button>
                </div>
            </div>
            <div class="form-group row">
                <div class="col-xs-12">
                    <a href="{{ url('/login') }}" class="col-xs-12 btn btn-primary">{{ utrans('custom.cancel') }}</a>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
