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
                                            <li><a href="{{ url('/user/invite') }}">покана</a></li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="col-xs-12 p-l-none">
                                    <div class="m-r-md p-h-xs col-md-6">
                                        <input class="rounded-input" type="text">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xs-12 page-content p-sm">
                    <div class="col-xs-12 list-orgs">
                        <div class="row">
                            <div class="col-sm-4 p-md">
                                <div class="col-xs-12 org-logo">
                                    <a href="{{ url('/user/groupView') }}">
                                        <img class="img-responsive" src="{{ asset('img/test-img/logo-org-4.jpg') }}"/>
                                    </a>
                                </div>
                                <div class="col-xs-12">
                                    <a href="{{ url('/user/groupView') }}"><h3>Име на организация</h3></a>
                                    <p class="text-justify">Pellentesque risus nisl, hendrerit eget tellus sit amet, ornare blandit nisi. Morbi consectetur, felis in semper euismod, mi libero fringilla felis, sit amet ullamcorper enim turpis non nisi.</p>
                                    <p class="text-right"><a href="{{ url('/user/groupView') }}" class="view-profile">виж още</a></p>
                                    <div class="control-btns text-center">
                                        <span class="badge badge-pill m-r-md m-b-sm"><a href="{{ url('/user/groupEdit') }}">промяна</a></span>
                                        <span class="badge badge-pill m-b-sm">
                                            <a
                                                href="#"
                                                onclick="return confirm('Изтриване на група?');"
                                                >изтриване</a>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-4 p-md">
                                <div class="col-xs-12 org-logo">
                                    <a href="{{ url('/user/groupView') }}">
                                        <img class="img-responsive" src="{{ asset('img/test-img/logo-org-1.jpg') }}"/>
                                    </a>
                                </div>
                                <div class="col-xs-12">
                                    <a href="{{ url('/user/groupView') }}"><h3>Име на организация</h3></a>
                                    <p>Pellentesque risus nisl, hendrerit eget tellus sit amet, ornare blandit nisi. Morbi consectetur, felis in semper euismod, mi libero fringilla felis, sit amet ullamcorper enim turpis non nisi.</p>
                                    <p class="text-right"><a href="{{ url('/user/groupView') }}" class="view-profile">виж още</a></p>
                                    <div class="control-btns text-center">
                                        <span class="badge badge-pill m-r-md m-b-sm"><a href="{{ url('/user/groupEdit') }}">промяна</a></span>
                                        <span class="badge badge-pill m-b-sm">
                                            <a
                                                href="#"
                                                onclick="return confirm('Изтриване на група?');"
                                                >изтриване</a>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-4 p-md">
                                <div class="col-xs-12 org-logo">
                                    <a href="{{ url('/user/groupView') }}"><img class="img-responsive" src="{{ asset('img/test-img/logo-org-2.jpg') }}"/></a>
                                </div>
                                <div class="col-xs-12">
                                    <a href="{{ url('/user/groupView') }}"><h3>Име на организация</h3></a>
                                    <p>Pellentesque risus nisl, hendrerit eget tellus sit amet, ornare blandit nisi. Morbi consectetur, felis in semper euismod, mi libero fringilla felis, sit amet ullamcorper enim turpis non nisi.</p>
                                    <p class="text-right"><a href="{{ url('/user/groupView') }}" class="view-profile">виж още</a></p>
                                    <div class="control-btns text-center">
                                        <span class="badge badge-pill m-r-md m-b-sm"><a href="{{ url('/user/groupEdit') }}">промяна</a></span>
                                        <span class="badge badge-pill m-b-sm">
                                            <a
                                                href="#"
                                                onclick="return confirm('Изтриване на група?');"
                                                >изтриване</a>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-4 p-md">
                                <div class="col-xs-12 org-logo">
                                    <a href="{{ url('/user/groupView') }}"><img class="img-responsive" src="{{ asset('img/test-img/logo-org-3.jpg') }}"/></a>
                                </div>
                                <div class="col-xs-12">
                                    <a href="{{ url('/user/groupView') }}"><h3>Име на организация</h3></a>
                                    <p>Pellentesque risus nisl, hendrerit eget tellus sit amet, ornare blandit nisi. Morbi consectetur, felis in semper euismod, mi libero fringilla felis, sit amet ullamcorper enim turpis non nisi.</p>
                                    <p class="text-right"><a href="{{ url('/user/groupView') }}" class="view-profile">виж още</a></p>
                                    <div class="control-btns text-center">
                                        <span class="badge badge-pill m-r-md m-b-sm"><a href="{{ url('/user/Edit') }}">промяна</a></span>
                                        <span class="badge badge-pill m-b-sm">
                                            <a
                                                href="#"
                                                onclick="return confirm('Изтриване на група?');"
                                                >изтриване</a>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-4 p-md">
                                <div class="col-xs-12 org-logo">
                                    <a href="{{ url('/user/groupView') }}">
                                        <img class="img-responsive" src="{{ asset('img/test-img/logo-org-1.jpg') }}"/>
                                    </a>
                                </div>
                                <div class="col-xs-12">
                                    <a href="{{ url('/user/groupView') }}"><h3>Име на организация</h3></a>
                                    <p>Pellentesque risus nisl, hendrerit eget tellus sit amet, ornare blandit nisi. Morbi consectetur, felis in semper euismod, mi libero fringilla felis, sit amet ullamcorper enim turpis non nisi.</p>
                                    <p class="text-right"><a href="{{ url('/user/groupView') }}" class="view-profile">виж още</a></p>
                                    <div class="control-btns text-center">
                                        <span class="badge badge-pill m-r-md m-b-sm"><a href="{{ url('/user/groupEdit') }}">промяна</a></span>
                                        <span class="badge badge-pill m-b-sm">
                                            <a
                                                href="#"
                                                onclick="return confirm('Изтриване на група?');"
                                                >изтриване</a>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-4 p-md">
                                <div class="col-xs-12 org-logo">
                                    <a href="{{ url('/user/groupView') }}"><img class="img-responsive" src="{{ asset('img/test-img/logo-org-2.jpg') }}"/></a>
                                </div>
                                <div class="col-xs-12">
                                    <a href="{{ url('/user/groupView') }}"><h3>Име на организация</h3></a>
                                    <p>Pellentesque risus nisl, hendrerit eget tellus sit amet, ornare blandit nisi. Morbi consectetur, felis in semper euismod, mi libero fringilla felis, sit amet ullamcorper enim turpis non nisi.</p>
                                    <p class="text-right"><a href="{{ url('/user/groupView') }}" class="view-profile">виж още</a></p>
                                    <div class="control-btns text-center">
                                        <span class="badge badge-pill m-r-md m-b-sm"><a href="{{ url('/user/groupEdit') }}">промяна</a></span>
                                        <span class="badge badge-pill m-b-sm">
                                            <a
                                                href="#"
                                                onclick="return confirm('Изтриване на група?');"
                                                >изтриване</a>
                                        </span>
                                    </div>
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
