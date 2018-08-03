@extends('layouts.app')

@section('content')
    <div class="container">
        @include('partials.alerts-bar')
        @include('partials.user-nav-bar', ['view' => 'organisation'])
        <div class="row">
            <div class="col-xs-12 text-left">
                <span class="badge badge-pill m-t-md new-data user-add-btn">
                    <a href="{{ url('/user/organisations/register') }}">{{ __('custom.add_new_organisation') }}</a>
                </span>
            </div>
        </div>
        @if (!empty($organisation))
            <div class="row">
                <div class="col-xs-12 m-t-md">
                    <div class="row">
                        <div class="col-xs-12 page-content p-sm">
                            <div class="col-xs-12 list-orgs">
                                <div class="row">
                                    <div class="col-xs-12 p-md">
                                        <div class="col-xs-12 org-logo">
                                            <img class="img-responsive" src="{{ $organisation->logo }}"/>
                                        </div>
                                        <div class="col-xs-12">
                                            <h3>{{ $organisation->name }}</h3>
                                            <p>{{ $organisation->description }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
@endsection
