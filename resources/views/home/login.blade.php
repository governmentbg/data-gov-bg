@extends('layouts.app')

@section('content')
<div class="container m-b-lg">
    <div class="row">
        <div class="col-md-12 m-t-lg">
            <div class="row">
                <div class="logo col-sm-4 col-xs-1"></div>
                <div class="logo col-sm-4 col-xs-10">
                    <div class="m-b-lg text-center col-xs-12">
                        <img class="responsive logo-login" src="{{ asset('img/opendata-logo-color.svg') }}">
                    </div>
                    <div class="col-xs-12">
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
                                <label for="username" class="col-xs-12 col-form-label">Потребителско име:</label>
                                <div class="col-xs-12">
                                    <input type="text" class="input-border-r-12 form-control" name="username">
                                    @if (!empty($error['username']))
                                        <span class="error">{{ $error['username'][0] }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="password" class="col-xs-12 col-form-label">Парола:</label>
                                <div class="col-xs-12">
                                    <input type="password" class="input-border-r-12 form-control" name="password">
                                    @if (!empty($error['password']))
                                        <span class="error">{{ $error['password'][0] }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="remember_me" class="col-xs-6 col-form-label">Запомни ме</label>
                                <div class="col-xs-6">
                                    <div class="js-check pull-right">
                                        <input type="checkbox" name="remember_me" value="1">
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="col-xs-12">
                                    <button type="submit" class="col-xs-12 btn btn-primary login-btn">вход</button>
                                </div>
                            </div>
                        </form>
                        <div class="form-group row">
                            <div class="col-xs-12">
                                <button type="button" class="col-xs-12 btn btn-primary">забравена парола</button>
                            </div>
                        </div>
                        <div class="form-group row text-center">
                            <div class="col-xs-12">
                                <a href="{{ url('/registration') }}"><h3>нов профил</h3></a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="logo col-sm-4 col-xs-1"></div>
            </div>
        </div>
    </div>
</div>
@endsection
