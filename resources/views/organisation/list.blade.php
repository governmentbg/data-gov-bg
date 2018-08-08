@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        @include('partials.sidebar-org-short')
        <div class="col-sm-9 col-xs-12 page-content p-sm">
            <div class="col-xs-12 list-orgs">
                <div class="row">
                    <div class="col-sm-6">
                        <div class="col-xs-12 org-logo">
                            <a href="{{ url('/organisation/profile') }}">
                                <img class="img-responsive" src="{{ asset('img/test-img/logo-org-4.jpg') }}"/>
                            </a>
                        </div>
                        <div class="col-xs-12">
                            <a href="{{ url('/organisation/profile') }}"><h3>{{ __('custom.organization_name') }}</h3></a>
                            <p class="text-justify">Pellentesque risus nisl, hendrerit eget tellus sit amet, ornare blandit nisi. Morbi consectetur, felis in semper euismod, mi libero fringilla felis, sit amet ullamcorper enim turpis non nisi.</p>
                            <p class="text-right"><a href="{{ url('/organisation/profile') }}" class="view-profile">{{ __('custom.to_profile') }}</a></p>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="col-xs-12 org-logo">
                            <a href="{{ url('/organisation/profile') }}">
                                <img class="img-responsive" src="{{ asset('img/test-img/logo-org-1.jpg') }}"/>
                            </a>
                        </div>
                        <div class="col-xs-12">
                            <a href="{{ url('/organisation/profile') }}"><h3>{{ __('custom.organization_name') }}</h3></a>
                            <p>Pellentesque risus nisl, hendrerit eget tellus sit amet, ornare blandit nisi. Morbi consectetur, felis in semper euismod, mi libero fringilla felis, sit amet ullamcorper enim turpis non nisi.</p>
                            <p class="text-right"><a href="{{ url('/organisation/profile') }}" class="view-profile">{{ __('custom.to_profile') }}</a></p>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="col-xs-12 org-logo">
                            <a href="{{ url('/organisation/profile') }}"><img class="img-responsive" src="{{ asset('img/test-img/logo-org-2.jpg') }}"/></a>
                        </div>
                        <div class="col-xs-12">
                            <a href="{{ url('/organisation/profile') }}"><h3>{{ __('custom.organization_name') }}</h3></a>
                            <p>Pellentesque risus nisl, hendrerit eget tellus sit amet, ornare blandit nisi. Morbi consectetur, felis in semper euismod, mi libero fringilla felis, sit amet ullamcorper enim turpis non nisi.</p>
                            <p class="text-right"><a href="{{ url('/organisation/profile') }}" class="view-profile">{{ __('custom.to_profile') }}</a></p>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="col-xs-12 org-logo">
                            <a href="{{ url('/organisation/profile') }}"><img class="img-responsive" src="{{ asset('img/test-img/logo-org-3.jpg') }}"/></a>
                        </div>
                        <div class="col-xs-12">
                            <a href="{{ url('/organisation/profile') }}"><h3>{{ __('custom.organization_name') }}</h3></a>
                            <p>Pellentesque risus nisl, hendrerit eget tellus sit amet, ornare blandit nisi. Morbi consectetur, felis in semper euismod, mi libero fringilla felis, sit amet ullamcorper enim turpis non nisi.</p>
                            <p class="text-right"><a href="{{ url('/organisation/profile') }}" class="view-profile">{{ __('custom.to_profile') }}</a></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
