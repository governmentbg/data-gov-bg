@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-xs-12 m-t-md">
            <div class="row">
                <div class="col-sm-3 cl-xs-12 sidenav m-b-md">
                    <span class="my-profile m-b-lg m-b-lg">Моят профил</span>
                    <span class="badge badge-pill m-t-lg new-data"><a  href="{{ url('/user/create') }}">Добави нов набор</a></span>
                </div>
                <div class="col-sm-9 cl-xs-12">
                    <div class="filter-content">
                        <div class="col-md-12">
                            <div class="row">
                                <div class="col-sm-12 p-l-none">
                                    <div>
                                        <ul class="nav filter-type right-border">
                                            <li><a class="p-l-none" href="{{ url('/user') }}">известия</a></li>
                                            <li><a class="active" href="{{ url('/user/datasets') }}">моите данни</a></li>
                                            <li><a href="{{ url('/user/groups') }}">групи</a></li>
                                            <li><a href="{{ url('/user/organisations') }}">организации</a></li>
                                            <li><a href="{{ url('/user/settings') }}">настройки</a></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xs-12">
                    <div class="articles m-t-lg">
                        @for ($i = 0; $i < 3; $i++)
                            <div class="article m-b-lg">
                                <div>Дата на добавяне: {{ date('d.m.Y') }}</div>
                                <div class="col-sm-12 p-l-none">
                                    <a href="{{ url('/user/datasetView') }}">
                                        <h2 class="m-t-xs">Lorem ipsum dolor sit amet</h2>
                                    </a>
                                    <p>
                                        Pellentesque risus nisl, hendrerit eget tellus sit amet, ornare blandit nisi. Morbi consectetur, felis in semper euismod, mi libero fringilla felis, sit amet ullamcorper enim turpis non nisi. Ut euismod nibh at ante dapibus, sit amet pharetra lectus blandit. Aliquam eget orci tellus. Aliquam quis dignissim lectus, non dictum purus. Pellentesque scelerisque quis enim at varius. Duis a ex faucibus urna volutpat varius ac quis mauris. Sed porttitor cursus metus, molestie ullamcorper dolor auctor sed. Praesent dictum posuere tellus, vitae eleifend dui ornare et. Donec eu ornare eros. Cras eget velit et ex viverra facilisis eget nec lacus.
                                    </p>
                                    <div class="col-sm-12 p-l-none">
                                        <div class="pull-left">
                                            <span class="badge badge-pill m-r-md m-b-sm"><a href="{{ url('/user/edit') }}">редактиране</a></span>
                                            <span class="badge badge-pill m-b-sm">
                                                <a
                                                    href="#"
                                                    onclick="return confirm('Изтриване на данните?');"
                                                    >премахване</a>
                                            </span>
                                        </div>
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
