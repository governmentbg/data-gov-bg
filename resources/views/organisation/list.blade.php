@extends('layouts.app')

@section('content')
    <div class="container">
        @include('partials.alerts-bar')
        <div class="row">
            <div class="col-sm-9 col-xs-12 col-md-offset-3 p-h-sm p-l-r-none">
                <div class="filter-content">
                    <div class="col-md-12">
                        <div class="row">
                            <div class="col-md-6 text-center p-l-none">
                                <div>
                                    <ul class="nav filter-type right-border">
                                        @foreach ($orgTypes as $orgType)
                                            <li>
                                                <a
                                                    href="{{
                                                        action(
                                                            'OrganisationController@list',
                                                            array_merge(
                                                                ['type' => $orgType->id],
                                                                array_except(app('request')->input(), ['type', 'page', 'q'])
                                                            )
                                                        )
                                                    }}"
                                                    class="{{ (isset($getParams['type']) && $getParams['type'] == $orgType->id) ? 'active' : '' }}"
                                                >{{ $orgType->name }}</a>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-8 col-md-7 col-sm-12 col-xs-12 col-xs-offset-3 p-w-xl search-field">
                <form method="GET" action="{{ url('/organisation') }}">
                    <input
                        type="text"
                        class="m-t-md input-border-r-12 form-control"
                        placeholder="{{ __('custom.search') }}"
                        value="{{ isset($getParams['q']) ? $getParams['q'] : '' }}"
                        name="q"
                    >
                    @foreach (array_except($getParams, ['q', 'page']) as $qp => $qpv)
                        <input type="hidden" name="{{ $qp }}" value="{{ $qpv }}"/>
                    @endforeach
                </form>
            </div>
        </div>
        <div class="row">
            @if (count($organisations))
                <div class="col-lg-8 col-md-7 col-sm-12 col-xs-12 m-t-lg col-xs-offset-3 p-w-xl">
                    {{ __('custom.order_by_name') }}
                </div>
                <div class="col-lg-8 col-md-7 col-sm-12 col-xs-12 m-t-md col-xs-offset-3 p-w-xl">
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
                    >{{ __('custom.order_asc') }}</a>
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
                    >{{ __('custom.order_desc') }}</a>
                </div>
            @endif
        </div>
        @if (count($organisations))
            @include('partials.pagination')
        @endif
        <div class="row">
            <div class="col-xs-12 list-orgs">
                <div class="row">
                    @if (count($organisations))
                        @foreach ($organisations as $key => $organisation)
                            <div class="col-md-4 col-sm-12 org-col">
                                <div class="col-xs-12 m-t-lg">
                                    <a href="{{ url('/organisation/profile/'. $organisation->uri) }}">
                                        <img class="img-responsive logo" src="{{ $organisation->logo }}"/>
                                    </a>
                                </div>
                                <div class="col-xs-12">
                                    <h3 class="org-name"><a href="{{ url('/organisation/profile/'. $organisation->uri) }}">{{ $organisation->name }}</a></h3>
                                    <div class="org-desc">{!! nl2br(e($organisation->description)) !!}</div>
                                    <p class="text-right show-more">
                                        <a href="{{ url('/organisation/profile/'. $organisation->uri) }}" class="view-profile">{{ __('custom.see_more') }}</a>
                                    </p>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="col-sm-12 m-t-xl text-center no-info">
                            {{ __('custom.no_info') }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
        @if (count($organisations))
            @include('partials.pagination')
        @endif
    </div>
@endsection
