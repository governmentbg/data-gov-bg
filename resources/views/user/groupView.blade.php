@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-xs-12 m-t-md">
            <div class="row">
                <div class="col-sm-3 cl-xs-12 sidenav m-b-md">
                    <span class="my-profile m-b-lg m-b-lg">Моят профил</span>
                    <span class="badge badge-pill m-t-lg new-data"><a  href="{{ url('/user/groupRegistration') }}">Създаване на група</a></span>
                </div>
                <div class="col-sm-9 cl-xs-12">
                    <div class="filter-content tex">
                        <div class="col-md-12">
                            <div class="row">
                                <div class="col-sm-12 p-l-none">
                                    <div>
                                        <ul class="nav filter-type right-border">
                                            <li><a class="p-l-none" href="{{ url('/user') }}">известия</a></li>
                                            <li><a href="{{ url('/user/datasets') }}">моите данни</a></li>
                                            <li><a class="active" href="{{ url('/user/groups') }}">групи</a></li>
                                            <li><a href="{{ url('/user/organisations') }}">организации</a></li>
                                            <li><a href="{{ url('/user/settings') }}">настройки</a></li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="col-xs-12 p-l-none m-t-md">
                                    <ul class="nav filter-type right-border">
                                        <li><a class="active p-l-none" href="{{ url('/user/groupView') }}">група</a></li>
                                        <li><a href="{{ url('/user/groupMembers') }}">членове</a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xs-12 page-content p-sm">
                    <div class="col-xs-12 list-orgs">
                        <div class="row">
                            <div class="col-xs-12 p-md">
                                <div class="col-xs-12 org-logo">
                                    <img class="img-responsive" src="{{ asset('img/test-img/logo-org-4.jpg') }}"/>
                                </div>
                                <div class="col-xs-12">
                                    <h3>Име</h3>
                                    <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
