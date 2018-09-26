@extends('layouts.app')

@section('content')
    <div class="container">
        @include('partials.alerts-bar')
        <div class="row">
            <div class="col-sm-3 p-sm">
                <h4 class="m-l-lg">{{ uctrans('custom.groups_list') }}</h4>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-3 col-xs-12 text-left p-w-xl">
            @if (isset($buttons['add']) && $buttons['add'])
                <span class="badge badge-pill m-t-md new-data">
                    <a href="{{ url('/'. $buttons['rootUrl'] .'/groups/register') }}">{{ __('custom.add_new_group') }}</a>
                </span>
            @endif
            </div>
            <div class="col-lg-8 col-md-7 col-sm-12 col-xs-12 p-w-xl search-field">
                <form method="GET" action="{{ url('/groups') }}">
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
            @if (count($groups))
                <div class="col-lg-8 col-md-7 col-sm-12 col-xs-12 m-t-lg col-xs-offset-3 p-w-xl">
                    {{ __('custom.order_by_name') }}
                </div>
                <div class="col-lg-8 col-md-7 col-sm-12 col-xs-12 m-t-md col-xs-offset-3 p-w-xl">
                    <a
                        href="{{
                            action(
                                'GroupController@list',
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
                                'GroupController@list',
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
        @if (count($groups))
            @include('partials.pagination')
        @endif
        <div class="row">
            <div class="col-xs-12 user-orgs">
                <div class="row">
                    @if (count($groups))
                        @foreach ($groups as $key => $group)
                            <div class="col-md-4 col-sm-12 org-col">
                                <div class="col-xs-12 m-t-lg">
                                    <a href="{{ url('/groups/view/'. $group->uri) }}">
                                        <img class="img-responsive logo" src="{{ $group->logo }}"/>
                                    </a>
                                </div>
                                <div class="col-xs-12">
                                    <a href="{{ route('groupView', array_merge(array_except($getParams, ['page']), ['uri' => $group->uri])) }}">
                                        <h3 class="org-name">{{ $group->name }}</h3>
                                    </a>
                                    <div class="org-desc">{!! nl2br(e($group->description)) !!}</div>
                                    <p class="text-right show-more">
                                        <a href="{{ route('groupView', array_merge(array_except($getParams, ['page']), ['uri' => $group->uri])) }}" class="view-profile">
                                            {{ __('custom.see_more') }}
                                        </a>
                                    </p>
                                </div>
                                <div class="col-xs-12 ch-del-btns">
                                    <div class="row">
                                        @if (isset($buttons[$group->id]['edit']) && $buttons[$group->id]['edit'])
                                            <form method="POST" action="{{ url('/'. $buttons['rootUrl'] .'/groups/edit/'. $group->uri) }}">
                                                {{ csrf_field() }}
                                                <div class="col-xs-6">
                                                    <button type="submit">{{ uctrans('custom.edit') }}</button>
                                                </div>
                                            </form>
                                        @endif
                                        @if (isset($buttons[$group->id]['delete']) && $buttons[$group->id]['delete'])
                                            <form method="POST" action="{{ route('groupDelete', array_except($getParams, ['page'])) }}">
                                                {{ csrf_field() }}
                                                <div class="col-xs-6 text-right">
                                                    <button
                                                        type="submit"
                                                        name="delete"
                                                        class="del-btn"
                                                        data-confirm="{{ __('custom.delete_group_confirm') }}"
                                                    >{{ uctrans('custom.remove') }}</button>
                                                </div>
                                                <input class="user-org-del" type="hidden" name="group_uri" value="{{ $group->uri }}">
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
            </div>
        </div>
        @if (count($groups))
            @include('partials.pagination')
        @endif
    </div>
@endsection
