@extends('layouts.app')

@section('content')
    <div class="container">
        @include('partials.alerts-bar')
        @include('partials.user-nav-bar', ['view' => 'organisation'])
        <div class="row">
            <div class="col-sm-3 col-xs-12 text-left">
                @if ($buttons['add'])
                    <span class="badge badge-pill m-t-md new-data user-add-btn">
                        <a href="{{ url('/user/organisations/register') }}">{{ __('custom.add_new_organisation') }}</a>
                    </span>
                @endif
            </div>
            @if ($buttons['view'])
                <div class="col-lg-8 col-md-7 col-sm-12 col-xs-12 search-field">
                    <form method="GET" action="{{ url('/user/organisations/search') }}">
                        <input
                            type="text"
                            class="m-t-md input-border-r-12 form-control"
                            placeholder="{{ __('custom.search') }}"
                            value="{{ isset($search) ? $search : '' }}"
                            name="q"
                        >
                    </form>
                </div>
            @endif
        </div>
        <div class="col-xs-12 m-t-md list-orgs user-orgs">
            <div class="row">
                @if (count($organisations))
                    @foreach ($organisations as $key => $organisation)
                        @if ($buttons[$organisation->uri]['view'])
                            <div class="col-md-4 col-sm-12 org-col">
                                <div class="col-xs-12 m-t-lg">
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
                                        @if ($buttons[$organisation->uri]['edit'])
                                            <form
                                                method="POST"
                                                action="{{ url('/user/organisations/edit/'. $organisation->uri) }}"
                                            >
                                                {{ csrf_field() }}
                                                <div class="col-xs-6">
                                                    <button type="submit" name="edit">{{ uctrans('custom.edit') }}</button>
                                                </div>
                                                <input type="hidden" name="view" value="1">
                                            </form>
                                        @endif
                                        @if ($buttons[$organisation->uri]['delete'])
                                            <form
                                                method="POST"
                                                action="{{ url('/user/organisations/delete/'. $organisation->id) }}"
                                            >
                                                {{ csrf_field() }}
                                                <div class="col-xs-6 text-right">
                                                    <button
                                                        type="submit"
                                                        name="delete"
                                                        class="del-btn"
                                                        data-confirm="{{ __('custom.delete_organisation_confirm') }}"
                                                    >{{ uctrans('custom.remove') }}</button>
                                                </div>
                                            </form>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endif
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
