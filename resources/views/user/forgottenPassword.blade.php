@extends('layouts.app')

@section('content')
<div class="container m-b-lg login-form">
    <div class="m-b-lg text-center">
        <img class="responsive logo-login" src="{{ asset('img/opendata-logo-color.svg') }}">
    </div>
    <div class="m-b-lg">
        <h3 class="text-center">{{ uctrans('custom.forgotten_password') }}</h3>
        <p>{{ __('custom.enter_username') }}</p>
        <p>{{ __('custom.receive_email') }}</p>
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
                <label for="username" class="col-xs-12 col-form-label">{{ uctrans('custom.user_name') }}:</label>
                <div class="col-xs-12">
                    <input type="text" class="input-border-r-12 form-control" name="username">
                    @if (!empty($errors['username']))
                    <span class="error">{{ $errors['username'] }}</span>
                    @endif
                </div>
            </div>
            <div class="form-group row">
                <div class="col-xs-12">
                    <button type="submit" class="col-xs-12 btn btn-primary login-btn">{{ uctrans('custom.send') }}</button>
                </div>
            </div>
            <div class="form-group row">
                <div class="col-xs-12">
                    <a href="{{ url()->previous() }}" class="col-xs-12 btn btn-primary">{{ uctrans('custom.back') }}</a>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
