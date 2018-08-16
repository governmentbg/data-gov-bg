@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        @include('partials.sidebar', ['filter' => $filter, 'mainCats' => $mainCats])
        <div class="col-sm-9 col-xs-12 p-sm page-content">
            <div class="filter-content">
                <div class="col-md-12">
                    <div class="row">
                        <div class="col-lg-12 p-l-r-none">
                            <div>
                                <ul class="nav filter-type right-border">
                                    <li><a class="active p-l-none" href="{{ url('/data') }}">{{ __('custom.data') }}</a></li>
                                    <li><a href="{{ url('/data/relatedData') }}">{{ __('custom.linked_data') }}</a></li>
                                    <li><a href="{{ url('/data/reportedList') }}">{{ __('custom.signal_data') }}</a></li>
                                </ul>
                            </div>
                            <div>
                                <div class="m-r-md p-h-xs">
                                    <input class="rounded-input" type="text">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div>
                    <div class="m-r-md p-h-xs">
                        <p>{{ __('custom.list_order_by') }}:</p>
                        <ul class="nav sort-by">
                            <li><a href="#">{{ __('custom.relevance') }}</a></li>
                            <li><a href="#">{{ __('custom.names_asc') }}</a></li>
                            <li><a href="#">{{ __('custom.names_desc') }}</a></li>
                            <li><a href="#">{{ __('custom.last_change') }}</a></li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="articles">
                @for ($i = 0; $i < 2; $i++)
                    <div class="article m-t-lg m-b-md">
                        <div class="art-heading-bar row">
                            <div class="col-sm-7 col-xs-12 p-l-r-none">
                                <div class="col-sm-2 col-xs-4 logo">
                                    <a href="#">
                                        <img
                                            alt="Име на организацията!!!"
                                            class="img-responsive"
                                            src="{{ asset('img/test-img/logo-org-1.jpg') }}"
                                        >
                                    </a>
                                </div>
                                <div class="socialPadding p-w-sm">
                                    <div class='social fb'><a href="#"><i class='fa fa-facebook'></i></a></div>
                                    <div class='social tw'><a href="#"><i class='fa fa-twitter'></i></a></div>
                                    <div class='social gp'><a href="#"><i class='fa fa-google-plus'></i></a></div>
                                </div>
                                <div class="sendMail m-r-sm">
                                    <span><a href="#"><i class="fa fa-envelope"></i></a></span>
                                </div>
                                <div class="status p-w-sm">
                                    <span>{{ __('custom.approved') }} </span>
                                </div>
                            </div>
                            <div class="follow pull-right">
                                <span class="badge badge-pill"><a href="#">{{ __('custom.follow') }}</a></span>
                            </div>
                        </div>
                        <div class="col-sm-12 p-l-none">
                            <a href="{{ url('/data/view') }}"><h2>Lorem ipsum dolor sit amet</h2></a>
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
                                    <span><a href="{{ url('/data/view') }}">{{ __('custom.see_more') }}</a></span>
                                </div>
                            </div>
                        </div>
                    </div>
                @endfor
                <div class="article m-t-lg m-b-md">
                    <div class="art-heading-bar">
                        <div class="col-sm-7 col-xs-12 p-l-r-none">
                            <div class="row">
                                <div class="col-sm-2 col-xs-4 logo">
                                    <a href="#">
                                        <img
                                            alt="Име на организацията!!!"
                                            class="img-responsive"
                                            src="{{ asset('img/test-img/logo-org-1.jpg') }}"
                                        >
                                    </a>
                                </div>
                                <div class="socialPadding p-w-sm">
                                    <div class='social fb'><a href="#"><i class='fa fa-facebook'></i></a></div>
                                    <div class='social tw'><a href="#"><i class='fa fa-twitter'></i></a></div>
                                    <div class='social gp'><a href="#"><i class='fa fa-google-plus'></i></a></div>
                                </div>
                                <div class="sendMail m-r-sm">
                                    <span><a href="#"><i class="fa fa-envelope"></i></a></span>
                                </div>
                                <div class="status notApproved p-w-sm">
                                    <span>{{ __('custom.unapproved') }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-5 col-xs-12">
                            <div class="follow pull-right">
                                <span class="badge badge-pill"><a href="#">{{ __('custom.follow') }}</a></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-12 p-l-none">
                        <a href="{{ url('/data/view') }}"><h2>Lorem ipsum dolor sit amet</h2></a>
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
                                <span><a href="{{ url('/data/view') }}">{{ __('custom.see_more') }}</a></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
