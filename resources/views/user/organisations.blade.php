@extends('layouts.app')

@section('content')
    <div class="container">
        @include('partials.alerts-bar')
        @include('partials.user-nav-bar', ['view' => 'organisation'])
        <div class="row">
            <div class="col-sm-6 col-xs-12 text-left">
                <span class="badge badge-pill m-t-md new-data user-add-btn">
                    <a href="{{ url('/user/organisations/register') }}">{{ __('custom.add_new_organisation') }}</a>
                </span>
            </div>
            <div class="col-sm-6 col-xs-12 search-field text-right">
                <form method="GET" action="{{ url('/user/organisations/search') }}">
                    <input
                        type="text"
                        class="m-t-md"
                        placeholder="{{ __('custom.search') }}"
                        value="{{ isset($search) ? $search : '' }}"
                        name="q"
                    >
                </form>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-6 col-xs-12 text-left">
                <span class="badge badge-pill m-t-md new-data user-add-btn">
                    <a href="{{ url('/user/organisations/datasets') }}">{{ __('custom.data_sets') }}</a>
                </span>
            </div>
        </div>
        <div class="col-xs-12 m-t-md list-orgs user-orgs">
            <div class="row">
                @if (count($organisations))
                    @foreach ($organisations as $key => $organisation)
                        <div class="col-md-4 col-sm-12 org-col">
                            <div class="col-xs-12">
                                <a href="{{ url('/user/organisations/view/'. $organisation->uri) }}">
                                    <img class="img-responsive logo" src="{{ $organisation->logo }}"/>
                                </a>
                            </div>
                            <div class="col-xs-12">
                                <a href="{{ url('/user/organisations/view/'. $organisation->uri) }}"><h3 class="org-name">{{ $organisation->name }}</h3></a>
                                <div class="org-desc">{{ $organisation->description }}</div>
                                <p class="text-right show-more">
                                    <a href="{{ url('/user/organisations/view/'. $organisation->uri) }}" class="view-profile">{{ __('custom.see_more') }}</a>
                                </p>
                            </div>
                            <div class="col-xs-12 ch-del-btns">
                                <div class="row">
                                    @if (\App\Role::isAdmin($organisation->id))
                                        <form
                                            method="POST"
                                            action="{{ url('/user/organisations/edit/'. $organisation->uri) }}"
                                        >
                                            {{ csrf_field() }}
                                            <div class="col-xs-6">
                                                <button type="submit" name="edit">{{ __('custom.edit') }}</button>
                                            </div>
                                            <input type="hidden" name="view" value="1">
                                        </form>
                                        <form
                                            method="POST"
                                            action="{{ url('/user/organisations/delete/'. $organisation->id) }}"
                                        >
                                            {{ csrf_field() }}
                                            <div class="col-xs-6 text-right">
                                                <button
                                                    type="submit"
                                                    name="delete"
                                                    data-confirm="{{ __('custom.delete_organisation_confirm') }}"
                                                >{{ __('custom.remove') }}</button>
                                            </div>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="col-sm-12 m-t-xl text-center no-info">
                        {{ __('custom.no_info') }}
                    </div>
                @endif
            </div>
            @if (isset($pagination))
                <div class="row">
                    <div class="col-xs-12 text-center">
                        {{ $pagination->render() }}
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection
