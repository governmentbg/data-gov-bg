@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-xs-12 m-t-md">
                <div class="row">
                    <div class="col-xs-12 p-sm">
                        <div class="filter-content">
                            <div class="col-md-12">
                                <div class="row">
                                    <div class="col-md-3 col-sm-12 p-l-none"><span class="my-profile m-b-lg m-l-sm">Моят профил</span></div>
                                    <div class="col-md-7 col-sm-12 p-l-none">
                                        <div>
                                            <ul class="nav filter-type right-border">
                                                <li><a href="{{ url('/user') }}">известия</a></li>
                                                <li><a href="{{ url('/user/datasets') }}">моите данни</a></li>
                                                <li><a href="">групи</a></li>
                                                <li><a class="active" href="{{ url('/user/organisations') }}">организации</a></li>
                                                <li><a href="{{ url('/user/settings') }}">настройки</a></li>
                                            </ul>
                                        </div>
                                    </div>
                                    <div class="col-md-2 col-sm-12 p-l-none search text-right">
                                        <form method="GET" action="{{ url('/user/organisations/search') }}">
                                            <input
                                                type="text"
                                                placeholder=" търсене.."
                                                value="{{ isset($search) ? $search : '' }}"
                                                name="q"
                                            >
                                        </form>
                                    </div>
                                </div>
                                <div class="row create-org">
                                    <div class="col-xs-12 p-l-none text-right">
                                        <span>създаване на организация</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xs-12 list-orgs user-orgs">
            <div class="row">
                @foreach ($organisations as $organisation)
                    <div class="col-md-4 col-sm-12 org-col">
                        <div class="col-xs-12 org-logo">
                            <a href="{{ url('/organisation/profile') }}">
                                <div class="logo-cont"><img class="img-responsive" src="{{ asset('img/test-img/logo-org-3.jpg') }}"/></div>
                            </a>
                        </div>
                        <div class="col-xs-12">
                            <a href="{{ url('/organisation/profile') }}"><h3 class="org-name">{{ $organisation->name }}</h3></a>
                            <div class="org-desc">{{ $organisation->description }}</div>
                            <p class="text-right show-more"><a href="{{ url('/organisation/profile') }}" class="view-profile">виж още</a></p>
                        </div>
                        <div class="col-xs-12 ch-del-btns">
                            <div class="row">
                                <div class="col-xs-6"><button type="submit" name="change">промяна</button></div>
                                <form method="POST" action="{{ url('/user/organisation/delete') }}">
                                    {{ csrf_field() }}
                                    <div class="col-xs-6 text-right"><button type="submit" name="delete">изтриване</button></div>
                                    <input class="user-org-del" type="hidden" name="id" value="{{ $organisation->id }}">
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="row">
                <div class="col-xs-12 text-center">
                    {{ $pagination->render() }}
                </div>
            </div>
        </div>
    </div>
@endsection
