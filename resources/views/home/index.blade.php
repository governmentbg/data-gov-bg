@extends('layouts.app')

@section('content')
<div class="container home-stats">
    <div class="flash-message">
        @foreach (['danger', 'warning', 'success', 'info'] as $msg)
            @if(Session::has('alert-' . $msg))
                <p class="alert alert-{{ $msg }}">
                    {{ Session::get('alert-' . $msg) }}
                    <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                </p>
            @endif
        @endforeach
    </div>
    <div class="col-md-8 basic-stats">
        <div class="row">
            <div class="col-md-6">
                <a href="{{ url('/users/list') }}" class="reg-users">
                    <p>{{ $users }}</p>
                    <hr>
                    <p>{{ __('custom.registered_users') }}</p>
                    <img src="{{ asset('/img/reg-users.svg') }}">
                </a>
            </div>
            <div class="col-md-6">
                <a href="{{ url('organisation') }}" class="reg-orgs">
                    <p>{{ $organisations }}</p>
                    <hr>
                    <p>{{ utrans('custom.organisations', 2) }}</p>
                    <img src="{{ asset('/img/reg-orgs.svg') }}">
                </a>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <a href="{{ url('data') }}" class="data-sets">
                    <p>{{ $datasets }}</p>
                    <hr>
                    <p>{{ __('custom.data_sets') }}</p>
                    <img src="{{ asset('/img/data-sets.svg') }}">
                </a>
            </div>
            <div class="col-md-6">
                <a href="{{ url('data') }}" class="updates">
                    <p>{{ $updates }}</p>
                    <hr>
                    <p>{{ __('custom.updates') }} </p>
                    <img src="{{ asset('/img/updates.svg') }}">
                </a>
            </div>
        </div>
    </div>
    <div class="col-md-4 most-active">
        <a href="{{ isset($mostActiveOrg->uri) ? url('organisation/profile/'. $mostActiveOrg->uri) : '#' }}">
            <img src="{{ asset('/img/medal.svg') }}">
            <p>{{ __('custom.most_active_agency') }} {{ $lastMonth }}</p>
            <hr>
            <span>{{ isset($mostActiveOrg->name) ? $mostActiveOrg->name : null }}</span>
            <img class="org-logo" src="{{ isset($mostActiveOrg->logo) ? $mostActiveOrg->logo : asset('img/open-data.png') }}">
        </a>
    </div>
</div>

<div class="container">
    <div class="row">
        <div class="col-md-12">
            <h4 class="heading">{{ utrans('custom.topics') }}</h4>
            <div class="picks-box">
                @foreach ($categories as $category)
                    <a
                        href="{{ route('data', ['category' => [$category->id]]) }}"
                    >
                        <img class="home-icon cls-1" src="{{  $category->icon }}"/>
                        <p>{{$category->name}}</p>
                    </a>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection
