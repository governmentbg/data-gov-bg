@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-xs-12 m-t-md">
            <div class="row">
                <div class="col-sm-3 col-xs-12 sidenav">
                    <span class="my-profile m-b-lg m-l-sm">{{ __('custom.my_profile') }}</span>
                </div>
                <div class="col-sm-9 col-xs-12">
                    <div class="filter-content">
                        <div class="col-md-12">
                            <div class="row">
                                <div class="col-sm-12 p-l-none">
                                    <div>
                                        <ul class="nav filter-type right-border">
                                            <li><a class="p-l-none" href="{{ url('/user') }}">{{ __('custom.notifications') }}</a></li>
                                            <li>
                                                <!-- if there is resource with signal -->
                                                <div class="col-xs-12 text-center exclamation-sign">
                                                    <img src="{{ asset('img/reported.svg') }}">
                                                </div>
                                                <!-- end-->
                                                <a class="active" href="{{ url('/user/datasets') }}">{{ __('custom.my_data') }}</a>
                                            </li>
                                            <li><a href="{{ url('/user/userGroups') }}">{{ trans_choice(__('custom.groups'), 2) }}</a></li>
                                            <li><a href="{{ url('/user/organisations') }}">{{ trans_choice(__('custom.organisations'), 2) }}</a></li>
                                            <li><a href="{{ url('/user/settings') }}">{{ __('custom.settings') }}</a></li>
                                            <li><a href="{{ url('/user/invite') }}">{{ __('custom.invite') }}</a></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-10 col-xs-12 m-t-lg">
                    <div class="articles">
                        <div class="article m-b-md">
                            <div>
                                <div class="col-sm-12 col-xs-12 p-l-none">
                                    <ul class="p-l-none">
                                        <li>{{ __('custom.contact_support_name') }}:</li>
                                        <li>{{ __('custom.version') }}:</li>
                                        <li>{{ __('custom.last_update') }}:</li>
                                        <li>{{ __('custom.created') }}:</li>
                                    </ul>
                                </div>
                            </div>
                            <div class="col-sm-12 p-l-none art-heading-bar">
                                <div class="socialPadding">
                                    <div class='social fb'><a href="#"><i class='fa fa-facebook'></i></a></div>
                                    <div class='social tw'><a href="#"><i class='fa fa-twitter'></i></a></div>
                                    <div class='social gp'><a href="#"><i class='fa fa-google-plus'></i></a></div>
                                </div>
                                <div class="sendMail p-w-sm">
                                    <span><a href="#"><i class="fa fa-envelope"></i></a></span>
                                </div>
                            </div>

                            <div class="col-sm-12 p-l-none">
                                <h2>Lorem ipsum dolor sit amet</h2>
                                <p>
                                    Pellentesque risus nisl, hendrerit eget tellus sit amet,
                                    ornare blandit nisi. Morbi consectetur, felis in semper euismod,
                                    mi libero fringilla felis, sit amet ullamcorper enim turpis non nisi.
                                    Ut euismod nibh at ante dapibus, sit amet pharetra lectus blandit.
                                    Aliquam eget orci tellus. Aliquam quis dignissim lectus, non dictum purus.
                                    Pellentesque scelerisque quis enim at varius. Duis a ex faucibus urna volutpat
                                    varius ac quis mauris. Sed porttitor cursus metus, molestie ullamcorper dolor
                                    auctor sed. Praesent dictum posuere tellus, vitae eleifend dui ornare et.
                                    Donec eu ornare eros. Cras eget velit et ex viverra facilisis eget nec lacus.
                                </p>
                                <div class="col-sm-12 p-l-none">
                                    <div class="tags pull-left">
                                        <span class="badge badge-pill">ТАГ</span>
                                        <span class="badge badge-pill">ДЪЛЪГ ТАГ</span>
                                        <span class="badge badge-pill">ТАГ</span>
                                    </div>
                                </div>

                                <!-- chart goes here -->
                                <div class="col-sm-12 pull-left m-t-md p-l-r-none">
                                    <img class="img-responsive" src="{{ asset('img/test-img/bar-chart.jpg') }}">
                                </div>
                                <div class="col-xs-12 pull-left m-t-md p-l-r-none text-right">
                                    <div class="col-sm-12 m-t-lg p-l-none">
                                        <div>
                                            <span class="badge badge-pill m-b-sm">
                                                <a
                                                    href="#"
                                                    onclick="return confirm('Изтриване на данните?');"
                                                    >{{ __('custom.remove') }}</a>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <!-- IF there are old versions of this article -->
                                <div class="col-sm-12 pull-left m-t-md p-l-none">
                                    <div class="pull-left history">
                                        <div>
                                            <a href="#">
                                                <span class="version-heading">{{ __('custom.title') }}</span>
                                                <span class="version">&nbsp;&#8211;&nbsp;версия 3</span>
                                            </a>
                                        </div>
                                        <div>
                                            <a href="#">
                                                <span class="version-heading">{{ __('custom.title') }}</span>
                                                <span class="version">&nbsp;&#8211;&nbsp;версия 2</span>
                                            </a>
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
</div>
@endsection
