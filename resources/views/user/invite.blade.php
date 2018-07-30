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
                    <div class="col-md-3 col-sm-12 p-l-none"><span class="my-profile m-b-lg m-l-sm">Моят профил</span></div>
                    <div class="col-md-7 col-sm-12 p-l-none">
                        <div>
                            <ul class="nav filter-type right-border">
                                <li><a href="{{ url('/user') }}">известия</a></li>
                                <li><a href="{{ url('/user/datasets') }}">моите данни</a></li>
                                <li><a href="{{ url('/user/groups') }}">групи</a></li>
                                <li><a href="{{ url('/user/organisations') }}">организации</a></li>
                                <li><a href="{{ url('/user/settings') }}">настройки</a></li>
                                <li><a class="active" href="{{ url('/user/invite') }}">покана</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row text-center">
        <div class="frame col-lg-6">
            <form class="form-horizontal" method="post">
                {{ csrf_field() }}
                <div class="form-group text-center">
                    <h2>Добавяне на потербител по мейл<h2>
                </div>
                <div class="form-group">
                    <label for="email" class="col-lg-2 col-form-label">E-mail: </label>
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
                        <label for="role" class="col-lg-2 col-form-label">Роля: </label>
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
                <button type="submit" name="send" class="m-l-md btn btn-primary m-b-sm pull-right">Покани</button>
                @if (Auth::user()->is_admin)
                    <button type="submit" name="generate" class="m-l-md btn btn-primary m-b-sm pull-right">Генерирай</button>
                @endif
            </form>
        </div>
    </div>
</div>
@endsection
