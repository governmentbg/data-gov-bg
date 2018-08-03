@extends('layouts.app')

@section('content')
<div class="container">
    <div class="col-xs-12">
        <div class="row">
            <div class="col-sm-3 sidenav p-l-r-none">
            </div>
            <div class="col-sm-9 col-xs-12 p-l-r-none">
                <div class="filter-content">
                    <div class="col-md-12">
                        <div class="row">
                            <div class="col-md-6 text-center p-l-none">
                                <div>
                                    <ul class="nav filter-type right-border">
                                        <li><a class="active p-l-none" href="{{ url('/organisation/profile') }}">{{ __('custom.profile') }}</a></li>
                                        <li><a href="{{ url('/organisation/datasets') }}">{{ __('custom.data') }}</a></li>
                                        <li><a href="{{ url('/organisation/chronology') }}">{{ __('custom.chronology') }}</a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row m-t-lg">
            <div class="col-sm-12">
                <div class="row">
                    <div class="col-md-3 col-sm-6 col-xs-12">
                        <img class="img-responsive" src="{{ asset('img/test-img/logo-org-4.jpg') }}"/>
                    </div>
                    <div class="col-md-9 col-sm-6 col-xs-12 info-box">
                        <div class="row">
                            <div class="col-lg-4 col-md-5 col-xs-12">
                                <a href="#" class="followers">
                                    <p>86</p>
                                    <hr>
                                    <p>{{ __('custom.followers') }} </p>
                                    <img src="{{ asset('/img/followers.svg') }}">
                                </a>
                            </div>
                            <div class="col-lg-4 col-md-5 col-xs-12">
                                <a href="#" class="data-sets">
                                    <p>120</p>
                                    <hr>
                                    <p>{{ __('custom.data_sets') }}</p>
                                    <img src="{{ asset('/img/data-sets.svg') }}">
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="m-t-lg">
                    <div class="m-b-md">
                        <div>
                            <div class="col-xs-12 p-l-none">
                                <div>
                                    <h3>{{ __('custom.organization_name') }} </h3>
                                    <p>
                                        Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Turpis tincidunt id aliquet risus feugiat in ante metus dictum. Arcu non odio euismod lacinia at quis risus sed vulputate. Elit ut aliquam purus sit. Augue mauris augue neque gravida in fermentum et sollicitudin. Blandit libero volutpat sed cras ornare arcu dui. Odio euismod lacinia at quis. Tristique sollicitudin nibh sit amet commodo nulla facilisi. Mattis molestie a iaculis at erat pellentesque. Auctor eu augue ut lectus arcu bibendum at varius vel. Id venenatis a condimentum vitae sapien pellentesque habitant. Proin sed libero enim sed.
                                    </p>
                                </div>
                            </div>
                            <div class="col-xs-12 p-l-r-none articles">
                                <div class="col-sm-8 col-xs-12 p-l-none article pull-left">
                                    <span>За контакт</span></br>
                                    <span>Иван Иванов</span></br>
                                    <span>дирекция Български пощи</span></br></br>
                                    <span>тел. 02/940 2445</span></br>
                                    <span>e-mail: ivanov@bgpost.org</span></br></br>
                                    <span>{{ __('custom.follow_us') }}</span>
                                    <div class="col-xs-12 p-l-none art-heading-bar m-t-sm">
                                        <div class="socialPadding">
                                            <div class='social fb'><a href="#"><i class='fa fa-facebook'></i></a></div>
                                            <div class='social tw'><a href="#"><i class='fa fa-twitter'></i></a></div>
                                            <div class='social gp'><a href="#"><i class='fa fa-google-plus'></i></a></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-4 col-xs-12 pull-right text-right">
                                    <div class="follow m-t-md m-b-sm p-w-sm">
                                        <span class="badge badge-pill"><a href="#">{{ __('custom.follow') }}</a></span>
                                        <!-- if it is already followed -->
                                        <!--<span class="badge badge-pill"><a href="#">{{ __('custom.stop_follow') }}</a></span>-->
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
