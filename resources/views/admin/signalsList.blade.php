
@extends('layouts.app')

@section('content')
    <div class="container admin">
        @include('partials.alerts-bar')
        @include('partials.admin-nav-bar', ['view' => 'signals'])
        <div class="col-xs-12 m-t-lg m-b-lg p-l-r-none">
            <span class="my-profile m-l-sm">{{ uctrans('custom.signals_list') }}</span>
        </div>
        <div class="row">
            @include('partials.admin-sidebar', [
                'action' => 'Admin\SignalController@list',
                'options' => ['range', 'state', 'search']
            ])
            <div class="col-sm-9 col-xs-12">
                <div class="row sort-links">
                    <div class="col-xs-12 m-b-md">{{ __('custom.order_by') }}:</div>
                    <div class="col-xs-12 order-datasets">
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
                        >{{ uctrans('custom.status') }}</a>
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
                        >{{ uctrans('custom.lastname') }}</a>
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
                <div class="row">
                    @include('partials.pagination')
                    @if (count($signals))
                        @foreach ($signals as $index => $signal)
                            <div class="col-xs-12 {{ $index ? 'm-t-lg' : '' }}">
                                <div class="row">
                                <div class="col-md-10 col-xs-12">
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
                                    class="col-md-2 col-xs-12 text-right js-terms-req-preview"
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
                                    @if ($signal->created_at != $signal->updated_at)
                                        <div class="col-xs-12">{{ __('custom.updated_at') }}: &nbsp; {{ $signal->updated_at }}</div>
                                        <div class="col-xs-12">{{ __('custom.updated_by') }}: &nbsp; {{ $signal->updated_by }}</div>
                                    @endif
                                </div>
                                </div>
                            </div>
                            <div class="col-xs-12 p-l-r-none m-l-sm text-right m-t-xs m-b-lg {{ 'js-terms-req-btns-'. $index }} hidden">
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
                        <div class="col-sm-12 m-t-md text-center no-info">
                            {{ __('custom.no_info') }}
                        </div>
                    @endif

                    @if (isset($pagination))
                        <div class="row">
                            <div class="col-xs-12 text-center">
                                {{ $pagination->render() }}
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

    </div>
@endsection
