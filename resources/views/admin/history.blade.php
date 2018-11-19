@extends('layouts.app')

@section('content')
<div class="container admin">
    @include('partials.alerts-bar')
    @include('partials.admin-nav-bar', ['view' => $view])
    <div class="col-xs-12 m-t-lg  m-b-md p-l-r-none">
        <span class="my-profile m-l-sm">{{ uctrans('custom.history_'. $view) }}</span>
    </div>
    <div class="row">
        @include('partials.admin-sidebar', [
            'action'  => 'Admin\HistoryController@history',
            'history' => true,
        ])
        <div class="col-sm-9 col-xs-12">
            <div class="row sort-links">
                <div class="col-xs-12 m-t-sm m-b-sm">{{ __('custom.order_by') }}:</div>
                <div class="col-xs-12 order-datasets">
                    <a
                        href="{{
                            action(
                                'Admin\HistoryController@history',
                                array_merge(
                                    ['order_field' => 'username', 'type' => $view],
                                    array_except(app('request')->input(), ['order_field'])
                                )
                            )
                        }}"

                        class="{{
                            isset(app('request')->input()['order_field'])
                            && app('request')->input()['order_field'] == 'username'
                                ? 'active'
                                : ''
                        }}"
                    >{{ utrans('custom.name') }}</a>
                    <a
                        href="{{
                            action(
                                'Admin\HistoryController@history',
                                array_merge(
                                    ['order_field' => 'occurrence', 'type' => $view],
                                    array_except(app('request')->input(), ['order_field'])
                                )
                            )
                        }}"

                        class="{{
                            isset(app('request')->input()['order_field'])
                            && app('request')->input()['order_field'] == 'occurrence'
                                ? 'active'
                                : ''
                        }}"
                    >{{ __('custom.date') }}</a>
                    @if ($view == 'action')
                        <a
                            href="{{
                                action(
                                    'Admin\HistoryController@history',
                                    array_merge(
                                        ['order_field' => 'module_name', 'type' => $view],
                                        array_except(app('request')->input(), ['order_field'])
                                    )
                                )
                            }}"

                            class="{{
                                isset(app('request')->input()['order_field'])
                                && app('request')->input()['order_field'] == 'module_name'
                                    ? 'active'
                                    : ''
                            }}"
                        >{{ __('custom.module') }}</a>
                        <a
                            href="{{
                                action(
                                    'Admin\HistoryController@history',
                                    array_merge(
                                        ['order_field' => 'action', 'type' => $view],
                                        array_except(app('request')->input(), ['order_field'])
                                    )
                                )
                            }}"

                            class="{{
                                isset(app('request')->input()['order_field'])
                                && app('request')->input()['order_field'] == 'action'
                                    ? 'active'
                                    : ''
                            }}"
                        >{{ __('custom.action') }}</a>
                        <a
                            href="{{
                                action(
                                    'Admin\HistoryController@history',
                                    array_merge(
                                        ['order_field' => 'action_object', 'type' => $view],
                                        array_except(app('request')->input(), ['order_field'])
                                    )
                                )
                            }}"

                            class="{{
                                isset(app('request')->input()['order_field'])
                                && app('request')->input()['order_field'] == 'action_object'
                                    ? 'active'
                                    : ''
                            }}"
                        >{{ __('custom.object') }}</a>
                        <a
                            href="{{
                                action(
                                    'Admin\HistoryController@history',
                                    array_merge(
                                        ['order_field' => 'action_msg', 'type' => $view],
                                        array_except(app('request')->input(), ['order_field'])
                                    )
                                )
                            }}"

                            class="{{
                                isset(app('request')->input()['order_field'])
                                && app('request')->input()['order_field'] == 'action_msg'
                                    ? 'active'
                                    : ''
                            }}"
                        >{{ utrans('custom.information') }}</a>
                        <a
                            href="{{
                                action(
                                    'Admin\HistoryController@history',
                                    array_merge(
                                        ['order_field' => 'ip_address', 'type' => $view],
                                        array_except(app('request')->input(), ['order_field'])
                                    )
                                )
                            }}"

                            class="{{
                                isset(app('request')->input()['order_field'])
                                && app('request')->input()['order_field'] == 'ip_address'
                                    ? 'active'
                                    : ''
                            }}"
                        >{{ __('custom.ip_address') }}</a>
                    @endif
                    <a
                        href="{{
                            action(
                                'Admin\HistoryController@history',
                                array_merge(
                                    ['order_type' => 'asc', 'type' => $view],
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
                                'Admin\HistoryController@history',
                                array_merge(
                                    ['order_type' => 'desc', 'type' => $view],
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
            <div class="row m-t-md">
                <form method="POST" class="form-horizontal">
                    @include('partials.pagination')
                    {{ csrf_field() }}
                    <div class="col-lg-12">
                        @if (!empty($history))
                            <div class="table-responsive opn-tbl text-center">
                                <table class="table">
                                    <thead>
                                        <th>{{ __('custom.date') }}</th>
                                        <th>{{ trans_choice(utrans('custom.users'), 1) }}</th>
                                        @if ($view == 'action')
                                            <th>{{ __('custom.module') }}</th>
                                            <th>{{ __('custom.action') }}</th>
                                            <th>{{ __('custom.object') }}</th>
                                        @endif
                                        <th>{{ utrans('custom.information') }}</th>
                                        <th>{{ __('custom.ip_address') }}</th>
                                    </thead>
                                    <tbody>
                                        @foreach ($history as $record)
                                            <tr>
                                                <td>{{ $record->occurrence }}</td>
                                                <td>{{ $record->user }}</td>
                                                @if ($view == 'action')
                                                    <td>{{ $record->module }}</th>
                                                    <td>{{ $actionTypes[$record->action] }}</th>
                                                    <td>{{ $record->action_object }}</th>
                                                @endif
                                                <td>{{ $record->action_msg }}</td>
                                                <td>{{ $record->ip_address }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="col-sm-12 m-t-md text-center no-info">
                                {{ __('custom.no_info') }}
                            </div>
                        @endif
                    </div>
                    @include('partials.pagination')
                    @if (!empty($history))
                        <div class="col-lg-12 text-right">
                            <button
                                class="btn btn-primary add"
                                type="submit"
                                name="download"
                            >{{ uctrans('custom.download') }}</button>
                        </div>
                    @endif
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
