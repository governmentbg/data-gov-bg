@extends('layouts.app')

@section('content')
    <div class="container">
        @include('partials.alerts-bar')
        <div class="row">
            <div class="col-sm-9 col-xs-12 p-sm col-sm-offset-3 col-xs-offset-2">
                @include('partials.org-type-bar', ['orgTypes' => $orgTypes])
            </div>
        </div>
        <div class="row">
            <div class="col-sm-3 col-xs-12 text-left p-w-xl">
            @if (isset($buttons['add']) && $buttons['add'])
                <span class="badge badge-pill m-t-md new-data">
                    <a href="{{ url('/'. $buttons['rootUrl'] .'/organisations/register') }}">{{ __('custom.add_new_organisation') }}</a>
                </span>
            @endif
            </div>
            <div class="col-lg-9 col-md-6 col-sm-12 col-xs-12 search-field p-l-lg m-t-xs">
                <form method="GET" action="{{ url('/organisation') }}">
                    <input
                        type="text"
                        class="m-t-md input-border-r-12 form-control js-ga-event"
                        placeholder="{{ __('custom.search') }}"
                        value="{{ isset($getParams['q']) ? $getParams['q'] : '' }}"
                        name="q"
                        data-ga-action="search"
                        data-ga-label="data search"
                        data-ga-category="data"
                    >
                    @foreach (array_except($getParams, ['q', 'page']) as $qp => $qpv)
                        <input type="hidden" name="{{ $qp }}" value="{{ $qpv }}"/>
                    @endforeach
                </form>
            </div>
        </div>
        <div class="row">
            @if (count($organisations))
                <div class="col-sm-9 col-xs-12 m-t-lg col-sm-offset-3 p-w-sm">
                    {{ __('custom.order_by_name') }}
                </div>
                <div class="col-sm-9 col-xs-12 m-t-sm col-sm-offset-3 p-w-sm">
                    <a
                        href="{{
                            action(
                                'OrganisationController@list',
                                array_merge(
                                    array_except(app('request')->input(), ['sort', 'order', 'page']),
                                    ['sort' => 'name', 'order' => 'asc']
                                )
                            )
                        }}"
                        class="{{
                            isset(app('request')->input()['order']) && app('request')->input()['order'] == 'asc'
                                ? 'active'
                                : ''
                        }}"
                    >{{ uctrans('custom.order_asc') }}</a>
                    <a
                        href="{{
                            action(
                                'OrganisationController@list',
                                array_merge(
                                    array_except(app('request')->input(), ['sort', 'order', 'page']),
                                    ['sort' => 'name', 'order' => 'desc']
                                )
                            )
                        }}"
                        class="m-l-xl {{
                            isset(app('request')->input()['order']) && app('request')->input()['order'] == 'desc'
                                ? 'active'
                                : ''
                        }}"
                    >{{ uctrans('custom.order_desc') }}</a>
                </div>
            @endif
        </div>
        <div class="col-xs-12 m-t-md text-center">
            @if (count($organisations))
                @include('partials.pagination')
            @endif
        </div>
        <div class="row">
            <div class="col-xs-12 list-orgs">
                <div class="row">
                    @if (count($organisations))
                        @foreach ($organisations as $key => $organisation)
                            <div class="col-md-4 col-sm-6 col-xs-10 col-sm-offset-0 col-xs-offset-1">
                                <div class="row">
                                    <div class="col-xs-12 org-col p-l-r-none">
                                        <div class="cust-tooltip organisation">{{ $organisation->name }}</div>
                                        <div class="col-xs-12 m-t-lg logo-box">
                                            <a href="{{ url('/organisation/profile/'. $organisation->uri) }}">
                                                <img class="img-responsive logo" src="{{ $organisation->logo }}"/>
                                            </a>
                                        </div>
                                        <div class="col-xs-12">
                                            <a href="{{ route('orgProfile', array_merge(app('request')->input(), ['uri' => $organisation->uri])) }}">
                                                <h3 class="org-name">{{ $organisation->name }}</h3>
                                            </a>
                                            <div class="org-desc">{!! nl2br(e($organisation->description)) !!}</div>
                                            <p class="text-right show-more">
                                                <a href="{{ route('orgProfile', array_merge(app('request')->input(), ['uri' => $organisation->uri])) }}" class="view-profile">
                                                    {{ __('custom.see_more') }}
                                                </a>
                                            </p>
                                        </div>
                                        <div class="col-xs-12 ch-del-btns">
                                            <div class="row">
                                                @if (isset($buttons[$organisation->id]['edit']) && $buttons[$organisation->id]['edit'])
                                                    <form method="POST" action="{{ url('/'. $buttons['rootUrl'] .'/organisations/edit/'. $organisation->uri) }}">
                                                        {{ csrf_field() }}
                                                        <div class="col-xs-6">
                                                            <button type="submit">{{ uctrans('custom.edit') }}</button>
                                                        </div>
                                                    </form>
                                                @endif
                                                @if (isset($buttons[$organisation->id]['delete']) && $buttons[$organisation->id]['delete'])
                                                    <form method="POST" action="{{ route('orgDelete', app('request')->input()) }}">
                                                        {{ csrf_field() }}
                                                        <div class="col-xs-6 text-right">
                                                            <button
                                                                type="submit"
                                                                name="delete"
                                                                class="del-btn"
                                                                data-confirm="{{ __('custom.delete_organisation_confirm') }}"
                                                            >{{ uctrans('custom.remove') }}</button>
                                                        </div>
                                                        <input class="user-org-del" type="hidden" name="org_uri" value="{{ $organisation->uri }}">
                                                    </form>
                                                @endif
                                            </div>
                                        </div>
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
        </div>
        <div class="col-xs-12 m-t-md text-center">
            @if (count($organisations))
                @include('partials.pagination')
            @endif
        </div>
    </div>
@endsection
