@extends('layouts.app')

@section('content')
    <div class="container">
        @include('partials.alerts-bar')
        @include('partials.admin-nav-bar', ['view' => 'organisation'])
        <div class="col-xs-12 m-t-lg p-l-r-none">
            <span class="my-profile m-l-sm">{{ uctrans('custom.organisations_list') }}</span>
        </div>
        <div class="row">
            <div class="col-sm-3 col-xs-12 text-left">
                <span class="badge badge-pill m-t-md new-data user-add-btn">
                    <a href="{{ url('/admin/organisations/register') }}">{{ __('custom.add_new_organisation') }}</a>
                </span>
            </div>
            <div class="col-lg-8 col-md-7 col-sm-12 col-xs-12 search-field admin">
                <form method="GET" action="{{ url('/admin/organisations/search') }}">
                    <input
                        type="text"
                        class="m-t-md input-border-r-12 form-control js-ga-event"
                        placeholder="{{ __('custom.search') }}"
                        value="{{ isset($search) ? $search : '' }}"
                        name="q"
                        data-ga-action="search"
                        data-ga-label="data search"
                        data-ga-category="data"
                    >
                    @foreach (app('request')->except(['q', 'page']) as $key => $value)
                        @if (is_array($value))
                            @foreach ($value as $innerValue)
                                <input name="{{ $key }}[]" type="hidden" value="{{ $innerValue }}">
                            @endforeach
                        @else
                            <input name="{{ $key }}" type="hidden" value="{{ $value }}">
                        @endif
                    @endforeach
                </form>
            </div>
        </div>

        <div class="row">
            @include('partials.admin-sidebar', ['action' => 'Admin\OrganisationController@list', 'options' => ['active', 'approved', 'parentOrg']])
            <div class="col-sm-9 col-xs-12 list-orgs user-orgs">
                <div class="row mx-auto">
                    <div class="col-xs-12 text-center">
                        @include('partials.pagination')
                    </div>
                    <div class="col-xs-12">
                        @if (count($organisations))
                            @foreach ($organisations as $key => $organisation)
                                <div class="col-md-4 col-sm-12 org-col">
                                    <div class="cust-tooltip user">{{ $organisation->name }}</div>
                                    <div class="col-xs-12 m-t-lg logo-box">
                                        <a href="{{ url('/admin/organisations/view/'. $organisation->uri) }}">
                                            <img class="img-responsive logo" src="{{ $organisation->logo }}"/>
                                        </a>
                                    </div>
                                    <div class="col-xs-12">
                                        <a href="{{ url('/admin/organisations/view/'. $organisation->uri) }}"><h3 class="org-name">{{ $organisation->name }}</h3></a>
                                        <div class="org-desc">{!! nl2br(e($organisation->description)) !!}</div>
                                        <p class="text-right show-more">
                                            <a href="{{ url('/admin/organisations/view/'. $organisation->uri) }}" class="view-profile">{{ __('custom.see_more') }}</a>
                                        </p>
                                    </div>
                                    <div class="col-xs-12 ch-del-btns">
                                        <div class="row">
                                            @if (\App\Role::isAdmin())
                                                <form
                                                    method="POST"
                                                    action="{{ url('/admin/organisations/edit/'. $organisation->uri) }}"
                                                >
                                                    {{ csrf_field() }}
                                                    <div class="col-xs-6">
                                                        <button type="submit" name="edit">{{ uctrans('custom.edit') }}</button>
                                                    </div>
                                                    <input type="hidden" name="view" value="1">
                                                </form>
                                                <form
                                                    method="POST"
                                                    action="{{ url('/admin/organisations/delete/'. $organisation->id) }}"
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
                            @endforeach
                        @else
                            <div class="col-sm-12 m-t-md text-center no-info">
                                {{ __('custom.no_info') }}
                            </div>
                        @endif
                    </div>
                </div>
                @if (isset($pagination))
                    <div class="col-xs-12 text-center">
                        {{ $pagination->render() }}
                    </div>
                @endif
            </div>
        </div>

    </div>
@endsection
