@extends('layouts.app')

@section('content')
<div class="container">
    <div class="com-sm-12 p-l-r-none">
        <div class="row">
            @include('partials.sidebar')
            <div class="col-sm-9 col-xs-11 page-content p-sm">
                <div class="filter-content">
                    <div class="col-md-12">
                        <div class="row">
                            <div class="col-md-8 col-sm-10  col-xs-10 p-l-none">
                                <div>
                                    <ul class="nav filter-type right-border">
                                        <li><a class="p-l-none" href="{{ url('/data') }}">{{ __('custom.data') }}</a></li>
                                        <li><a href="{{ url('/data/linkedData') }}">{{ __('custom.linked_data') }}</a></li>
                                        <li><a class="active" href="{{ url('/data/reportedList') }}">{{ __('custom.signal_data') }}</a></li>
                                    </ul>
                                </div>
                                <div>
                                    <div class="m-r-md p-h-xs">
                                        <input class="rounded-input" type="text">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 col-sm-2 col-xs-2 p-l-none">
                                <div class="col-md-6 col-sm-12 col-xs-12 exclamation-sign">
                                    <img src="{{ asset('img/reported.svg') }}">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div>
                        <div class="m-r-md p-h-xs">
                            <p>{{ __('custom.list_order_by') }}:</p>
                            <ul class="nav sort-by">
                                <li><a class="p-l-none" href="#">{{ __('custom.relevance') }}</a></li>
                                <li><a class="active" href="#">{{ __('custom.names_asc') }}</a></li>
                                <li><a href="#">{{ __('custom.names_desc') }}</a></li>
                                <li><a href="#">{{ __('custom.last_change') }}</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="articles">
                    @for ($i = 0; $i < 3; $i++)
                        <div class="article m-t-lg m-b-md">
                            <div class="art-heading-bar">
                                <div class="col-xs-12 p-l-none">
                                    <div class="row">
                                        <div class="col-sm-2 col-xs-3 logo">
                                            <a href="#">
                                                <img
                                                    alt="Име на организацията!!!"
                                                    class="img-responsive"
                                                    src="{{ asset('img/test-img/logo-org-1.jpg') }}"
                                                >
                                            </a>
                                        </div>
                                        <div class="col-sm-10 col-xs-9 m-t-sm">
                                            <div class="socialPadding p-w-sm">
                                                <div class='social fb'><a href="#"><i class='fa fa-facebook'></i></a></div>
                                                <div class='social tw'><a href="#"><i class='fa fa-twitter'></i></a></div>
                                                <div class='social gp'><a href="#"><i class='fa fa-google-plus'></i></a></div>
                                            </div>
                                            <div class="sendMail p-w-sm">
                                                <span><a href="#"><i class="fa fa-envelope"></i></a></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-12 p-l-none">
                                <a href="{{ url('/data/reportedView') }}"><h2>Lorem ipsum dolor sit amet</h2></a>
                                <p>
                                    Pellentesque risus nisl, hendrerit eget tellus sit amet, ornare blandit nisi. Morbi consectetur, felis in semper euismod, mi libero fringilla felis, sit amet ullamcorper enim turpis non nisi. Ut euismod nibh at ante dapibus, sit amet pharetra lectus blandit. Aliquam eget orci tellus. Aliquam quis dignissim lectus, non dictum purus. Pellentesque scelerisque quis enim at varius. Duis a ex faucibus urna volutpat varius ac quis mauris. Sed porttitor cursus metus, molestie ullamcorper dolor auctor sed. Praesent dictum posuere tellus, vitae eleifend dui ornare et. Donec eu ornare eros. Cras eget velit et ex viverra facilisis eget nec lacus.
                                </p>
                                <div class="col-sm-12 p-l-none">
                                    <div class="tags pull-left">
                                        <span class="badge badge-pill">ТАГ</span>
                                        <span class="badge badge-pill">ДЪЛЪГ ТАГ</span>
                                        <span class="badge badge-pill">ТАГ</span>
                                    </div>
                                    <div class="pull-right">
                                        <span><a href="{{ url('/data/reportedView') }}">{{ __('custom.see_more') }}</a></span>
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
@endsection
