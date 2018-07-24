@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-xs-12 m-t-md">
            <div class="row">
                <div class="col-md-3 col-sm-4 col-xs-12 sidenav">
                    <form type="POST">
                        <span class="my-profile m-b-lg m-l-sm">Моят профил</span>
                        <ul class="nav">
                            <li class="js-show-submenu m-t-lg">
                                <ul class="sidebar-submenu open">
                                    <li><a class="active" href="#">Организации</a></li>
                                    <li><a href="#">Групи</a></li>
                                    <li><a href="#">Набор данни</a></li>
                                    <li><a href="#">Основна тема</a></li>
                                </ul>
                            </li>
                        </ul>
                    </form>
                </div>
                <div class="col-md-9 col-sm-8 col-xs-12 p-sm">
                    <div class="filter-content">
                        <div class="col-md-12">
                            <div class="row">
                                <div class="col-sm-12 p-l-none">
                                    <div>
                                        <ul class="nav filter-type right-border">
                                            <li><a class="active p-l-none" href="{{ url('/user') }}">известия</a></li>
                                            <li><a href="{{ url('/user/datasets') }}">моите данни</a></li>
                                            <li><a href="{{ url('/user/groups') }}">групи</a></li>
                                            <li><a href="{{ url('/user/organisations') }}">организации</a></li>
                                            <li><a href="{{ url('/user/settings') }}">настройки</a></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="articles">
                        @for ($i = 0; $i < 3; $i++)
                            <div class="article m-t-md">
                                <div>Дата на добавяне: {{ date('d.m.Y') }}</div>
                                <div class="col-sm-12 p-l-none">
                                    <a href="{{ url('/user/datasetView') }}"><h2>Lorem ipsum dolor sit amet</h2></a>
                                    <p>
                                        Pellentesque risus nisl, hendrerit eget tellus sit amet, ornare blandit nisi. Morbi consectetur, felis in semper euismod, mi libero fringilla felis, sit amet ullamcorper enim turpis non nisi. Ut euismod nibh at ante dapibus, sit amet pharetra lectus blandit. Aliquam eget orci tellus. Aliquam quis dignissim lectus, non dictum purus. Pellentesque scelerisque quis enim at varius. Duis a ex faucibus urna volutpat varius ac quis mauris. Sed porttitor cursus metus, molestie ullamcorper dolor auctor sed. Praesent dictum posuere tellus, vitae eleifend dui ornare et. Donec eu ornare eros. Cras eget velit et ex viverra facilisis eget nec lacus.
                                    </p>
                                    <div class="col-sm-12 p-l-none">
                                        <div class="pull-right">
                                            <span><a href="{{ url('/user/datasetView') }}">Виж още</a></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endfor
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
