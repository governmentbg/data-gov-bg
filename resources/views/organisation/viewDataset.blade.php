@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        @include('partials.sidebar-org')
        <div class="col-sm-9 col-xs-11 page-content p-sm">
            <div class="filter-content">
                <div class="col-md-12">
                    <div class="row">
                        <div class="col-xs-12 p-l-r-none">
                            <div>
                                <ul class="nav filter-type right-border">
                                    <li><a class="p-l-none" href="{{ url('/organisation/profile') }}">{{ __('custom.profile') }}</a></li>
                                    <li><a class="active" href="{{ url('/organisation/datasets') }}">{{ __('custom.data') }}</a></li>
                                    <li><a href="{{ url('/organisation/chronology') }}">{{ __('custom.chronology') }}</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="articles">
                <div class="article m-b-md m-t-lg">
                    <div>
                        <div class="col-sm-7 col-xs-12 p-l-none m-t-md">
                            <div class="pull-left">
                                <img class="img-responsive" src="{{ asset('img/test-img/logo-org-4.jpg') }}"/>
                            </div>
                        </div>
                    </div>
                    <div>
                        <div class="col-sm-7 col-xs-12 p-l-none">
                            <ul class="p-l-none">
                                <li>{{ __('custom.managed_by') }}:</li>
                                <li>{{ __('custom.version') }}:</li>
                                <li>{{ __('custom.last_update') }}:</li>
                                <li>{{ __('custom.created') }}:</li>
                            </ul>
                        </div>
                    </div>
                    <div class="col-xs-12 p-l-none art-heading-bar">
                        <div class="socialPadding">
                            <div class='social fb'><a href="#"><i class='fa fa-facebook'></i></a></div>
                            <div class='social tw'><a href="#"><i class='fa fa-twitter'></i></a></div>
                            <div class='social gp'><a href="#"><i class='fa fa-google-plus'></i></a></div>
                        </div>
                        <div class="sendMail p-w-sm">
                            <span><a href="#"><i class="fa fa-envelope"></i></a></span>
                        </div>
                        <div class="status p-w-sm">
                            <span>{{ __('custom.approved') }}</span>
                        </div>
                    </div>

                    <div class="col-xs-12 p-l-none">
                        <h2>Lorem ipsum dolor sit amet</h2>
                        <p>
                            Pellentesque risus nisl, hendrerit eget tellus sit amet, ornare blandit nisi. Morbi consectetur, felis in semper euismod, mi libero fringilla felis, sit amet ullamcorper enim turpis non nisi. Ut euismod nibh at ante dapibus, sit amet pharetra lectus blandit. Aliquam eget orci tellus. Aliquam quis dignissim lectus, non dictum purus. Pellentesque scelerisque quis enim at varius. Duis a ex faucibus urna volutpat varius ac quis mauris. Sed porttitor cursus metus, molestie ullamcorper dolor auctor sed. Praesent dictum posuere tellus, vitae eleifend dui ornare et. Donec eu ornare eros. Cras eget velit et ex viverra facilisis eget nec lacus.
                        </p>
                        <div class="col-xs-12 p-l-none">
                            <div class="tags pull-left">
                                <span class="badge badge-pill">ТАГ</span>
                                <span class="badge badge-pill">ДЪЛЪГ ТАГ</span>
                                <span class="badge badge-pill">ТАГ</span>
                            </div>
                        </div>

                        <!-- chart goes here -->
                        <div class="col-xs-12 pull-left m-t-md p-l-none">
                            <img class="img-responsive" src="{{ asset('img/test-img/bar-chart.jpg') }}">
                        </div>
                       <div class="col-xs-12 pull-left m-t-md p-l-r-none">
                            <div class="col-md-6 col-xs-12 text-left p-l-r-none m-b-md">
                                <div class="badge-info m-r-md pull-left">
                                    <span class="badge badge-pill js-toggle-info-box m-b-sm">{{ __('custom.information') }}</span>
                                    <div class="info-box">
                                        <p>
                                        {{ __('custom.row') }}<br>
                                        {{ __('custom.from') }} ... &nbsp; {{ __('custom.to') }} ...
                                        </p>
                                        <p>
                                        {{ __('custom.column') }}<br>
                                        {{ __('custom.from') }} ... &nbsp; {{ __('custom.to') }} ...
                                        </p>
                                    </div>
                                </div>
                                <div class="badge-info m-r-md">
                                    <span class="badge badge-pill js-toggle-info-box m-b-sm">{{ __('custom.show_as') }}</span>
                                    <div class="info-box">
                                        <p>lorem ipsum</p>
                                        <p>lorem ipsum</p>
                                        <p>lorem ipsum</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 col-xs-12 text-right p-l-r-none m-b-md group-three">
                                <span class="badge badge-pill m-b-sm"><a href="#">{{ __('custom.download') }}</a></span>
                                <span class="badge badge-pill m-b-sm"><a href="#">{{ __('custom.signal') }}</a></span>
                                <span class="badge badge-pill m-b-sm"><a href="#">{{ __('custom.comment') }}</a></span>
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
                        <!-- IF there are commnets -->
                        <div class="col-sm-12 pull-left m-t-md p-l-none">
                            <div class="comments p-lg">
                                @for ($i=0; $i<3; $i++)
                                    <div class="comment-box p-lg m-b-lg">
                                        <img class="img-rounded coment-avatar" src="{{ asset('img/test-img/avatar.png') }}"/>
                                        <p class="comment-author p-b-xs">{{ __('custom.profile_name') }}</p>
                                        <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p>
                                    </div>
                                @endfor
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
