
@extends('layouts.app')

@section('content')
    <div class="container">
        @include('partials.alerts-bar')
        @include('partials.admin-nav-bar', ['view' => 'signals'])
        @include('partials.pagination')
        <div class="row m-b-lg">
            <div class="col-sm-3 hidden-xs"></div>
            <div class="col-sm-9 col-xs-12 m-t-lg m-b-md p-l-lg">{{ __('custom.order_by') }}</div>
            <div class="col-sm-3 hidden-xs"></div>
            <div class="col-sm-9 col-xs-12 p-l-lg order-datasets">
                <a
                    href="{{
                        action(
                            'Admin\SignalController@list',
                            array_merge(
                                ['order_field' => 'created_at'],
                                array_except(app('request')->input(), ['order_field'])
                            )
                        )
                    }}"

                    class="{{
                        isset(app('request')->input()['order_field'])
                        && app('request')->input()['order_field'] == 'created_at'
                            ? 'active'
                            : ''
                    }}"
                >{{ __('custom.date') }}</a>
                <a
                    href="{{
                        action(
                            'Admin\SignalController@list',
                            array_merge(
                                ['order_field' => 'status'],
                                array_except(app('request')->input(), ['order_field'])
                            )
                        )
                    }}"

                    class="{{
                        isset(app('request')->input()['order_field'])
                        && app('request')->input()['order_field'] == 'status'
                            ? 'active'
                            : ''
                    }}"
                >{{ __('custom.status') }}</a>
                <a
                    href="{{
                        action(
                            'Admin\SignalController@list',
                            array_merge(
                                ['order_field' => 'email'],
                                array_except(app('request')->input(), ['order_field'])
                            )
                        )
                    }}"

                    class="{{
                        isset(app('request')->input()['order_field'])
                        && app('request')->input()['order_field'] == 'email'
                            ? 'active'
                            : ''
                    }}"
                >{{ __('custom.e_mail') }}</a>
                <a
                    href="{{
                        action(
                            'Admin\SignalController@list',
                            array_merge(
                                ['order_field' => 'lastname'],
                                array_except(app('request')->input(), ['order_field'])
                            )
                        )
                    }}"

                    class="{{
                        isset(app('request')->input()['order_field'])
                        && app('request')->input()['order_field'] == 'lastname'
                            ? 'active'
                            : ''
                    }}"
                >{{ __('custom.lastname') }}</a>
                <a
                    href="{{
                        action(
                            'Admin\SignalController@list',
                            array_merge(
                                ['order_type' => 'asc'],
                                array_except(app('request')->input(), ['order_type'])
                            )
                        )
                    }}"

                    class="{{
                        isset(app('request')->input()['order_type'])
                        && app('request')->input()['order_type'] == 'asc'
                            ? 'active'
                            : ''
                    }}"
                >{{ uctrans('custom.order_asc') }}</a>
                <a
                    href="{{
                        action(
                            'Admin\SignalController@list',
                            array_merge(
                                ['order_type' => 'desc'],
                                array_except(app('request')->input(), ['order_type'])
                            )
                        )
                    }}"

                    class="{{
                        isset(app('request')->input()['order_type'])
                        && app('request')->input()['order_type'] == 'desc'
                            ? 'active'
                            : ''
                    }}"
                >{{ uctrans('custom.order_desc') }}</a>
            </div>
        </div>
        <div class="row m-b-lg">
            <div class="col-sm-3 sidenav col-xs-12 m-t-md">
                <form
                    method="GET"
                    action="{{ action('Admin\SignalController@list', []) }}"
                >
                    <div class="row m-b-sm">
                        <div class="col-xs-3 p-l-lg from-to">{{ uctrans('custom.from') }}:</div>
                        <div class="col-md-7 col-sm-8 text-left search-field admin">
                            <input class="js-from-filter datepicker input-border-r-12 form-control" name="from" value="{{ $range['from'] }}">
                        </div>
                    </div>
                    <div class="row m-b-sm">
                        <div class="col-xs-3 p-l-lg from-to">{{ uctrans('custom.to') }}:</div>
                        <div class="col-md-7 col-sm-8 text-left search-field admin">
                            <input class="js-to-filter datepicker input-border-r-12 form-control" name="to" value="{{ $range['to'] }}">
                        </div>
                    </div>
                    @if (isset(app('request')->input()['status']))
                        <input type="hidden" name="status" value="{{ app('request')->input()['status'] }}">
                    @endif
                    @if (isset(app('request')->input()['order_field']))
                        <input type="hidden" name="order_field" value="{{ app('request')->input()['order_field'] }}">
                    @endif
                    @if (isset(app('request')->input()['order_type']))
                        <input type="hidden" name="order_type" value="{{ app('request')->input()['order_type'] }}">
                    @endif
                    @if (isset(app('request')->input()['q']))
                        <input type="hidden" name="q" value="{{ app('request')->input()['q'] }}">
                    @endif
                </form>
                <ul class="nav">
                    <li class="js-show-submenu">
                        <a href="#" class="clicable"><i class="fa fa-angle-down"></i>&nbsp;&nbsp;{{ uctrans('custom.status') }}</a>
                        <ul class="sidebar-submenu m-b-md">
                            @foreach ($statuses as $key => $status)
                                <li>
                                    <a
                                        href="{{
                                            action(
                                                'Admin\SignalController@list',
                                                array_merge(
                                                    ['status' => $key],
                                                    array_except(app('request')->input(), ['status'])
                                                )
                                            )
                                        }}"
                                        class="{{
                                            isset(app('request')->input()['status'])
                                            && app('request')->input()['status'] == $key
                                                ? 'active'
                                                : ''
                                        }}"
                                    >{{ uctrans('custom.'. $status) }}</a>
                                </li>
                            @endforeach
                            <li>
                                <a
                                    href="{{
                                        action(
                                            'Admin\SignalController@list',
                                            array_except(app('request')->input(), ['status'])
                                        )
                                    }}"
                                >{{ uctrans('custom.all') }}</a>
                            </li>
                        </ul>
                    </li>
                </ul>
                <div class="form-group">
                    <div class="col-lg-10 col-md-12 search-field admin">
                        <form
                            method="GET"
                            action="{{ action('Admin\SignalController@list', []) }}"
                        >
                            <input
                                type="text"
                                class="m-t-md input-border-r-12 form-control"
                                placeholder="{{ __('custom.search') }}"
                                value="{{ isset($search) ? $search : '' }}"
                                name="q"
                            >
                            @if (isset(app('request')->input()['status']))
                                <input type="hidden" name="status" value="{{ app('request')->input()['status'] }}">
                            @endif
                            @if (isset(app('request')->input()['order_field']))
                                <input type="hidden" name="order_field" value="{{ app('request')->input()['order_field'] }}">
                            @endif
                            @if (isset(app('request')->input()['order_type']))
                                <input type="hidden" name="order_type" value="{{ app('request')->input()['order_type'] }}">
                            @endif
                            @if (isset(app('request')->input()['from']))
                                <input type="hidden" name="from" value="{{ app('request')->input()['from'] }}">
                            @endif
                            @if (isset(app('request')->input()['to']))
                                <input type="hidden" name="to" value="{{ app('request')->input()['to'] }}">
                            @endif
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-sm-9 col-xs-12 m-t-md">
                <div class="row">
                    @if (count($signals))
                        @foreach ($signals as $index => $signal)
                            <div class="col-xs-12 {{ $index ? 'm-t-lg' : '' }}">
                                <div class="col-xs-10">
                                    <p class="h3">
                                        <a
                                            href="{{ url('/admin/dataset/view/'. $signal->dataset_uri) }}"
                                        >{{ $signal->dataset_name }}</a>
                                    </p>
                                    <p>
                                        {{ utrans('custom.signal') }} - {{ $signal->created_by }} - {{ $signal->created_at }}
                                    </p>
                                </div>
                                <div
                                    class="col-xs-2 text-right js-terms-req-preview"
                                    data-index="{{ $index }}"
                                    data-action="show"
                                >
                                    <span class="badge badge-pill m-b-sm">{{ uctrans('custom.preview') }}</span>
                                </div>
                                <div class="hidden {{ 'js-terms-req-cont-'. $index }}">
                                    <div class="col-xs-12">{{ $signal->firstname .' '. $signal->lastname }}</div>
                                    <div class="col-xs-12 m-t-md">{{ $signal->email }}</div>
                                    <div class="col-xs-12 m-t-xs">{{ $signal->description }}</div>
                                    <div class="col-md-3 col-sm-4 col-xs-6 terms-hr"><hr/></div>
                                    <div class="col-xs-12">{{ __('custom.created_at') }}: &nbsp; {{ $signal->created_at }}</div>
                                    <div class="col-xs-12">{{ __('custom.created_by') }}: &nbsp; {{ $signal->created_by }}</div>
                                    <div class="col-xs-12">{{ __('custom.updated_at') }}: &nbsp; {{ $signal->updated_at }}</div>
                                    <div class="col-xs-12">{{ __('custom.updated_by') }}: &nbsp; {{ $signal->updated_by }}</div>
                                </div>
                            </div>
                            <div class="col-xs-12 text-right m-t-xs m-b-lg {{ 'js-terms-req-btns-'. $index }} hidden">
                                <span
                                    class="badge badge-pill m-r-md m-b-sm js-terms-req-close"
                                    data-index="{{ $index }}"
                                    data-action="close"
                                >{{ __('custom.close') }}</span>
                                <span class="badge badge-pill m-r-md m-b-sm">
                                    <a
                                        href="{{ url('/admin/signal/edit/'. $signal->id) }}"
                                    >{{ uctrans('custom.edit') }}</a>
                                </span>
                                <span class="badge del-btn badge-pill m-r-md m-b-sm">
                                    <a
                                        href="{{ url('/admin/signal/delete/'. $signal->id) }}"
                                        data-confirm="{{ __('custom.remove_data') }}"
                                    >{{ __('custom.delete') }}</a>
                                </span>
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

        @if (isset($pagination))
            <div class="row">
                <div class="col-xs-12 text-center">
                    {{ $pagination->render() }}
                </div>
            </div>
        @endif
    </div>
@endsection
