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
                        <form>
                            <div class="form-group row">
                                <label for="username" class="col-xs-12 col-form-label">Потребителско име:</label>
                                <div class="col-xs-12">
                                    <input type="text" class="input-border-r-12 form-control" id="username">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="password" class="col-xs-12 col-form-label">Парола:</label>
                                <div class="col-xs-12">
                                    <input type="password" class="input-border-r-12 form-control" id="password">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="password" class="col-xs-6 col-form-label">Запомни ме</label>
                                <div class="col-xs-6">
                                    <input type="checkbox" class="pull-right input-border-r-12 form-control custom-checkbox">
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
                                <a href="{{ url('/user/registration') }}"><h3>нов профил</h3></a>
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
