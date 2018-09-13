@extends('layouts.app')

@section('content')
<div class="container admin">
    @include('partials.alerts-bar')
    @include('partials.admin-nav-bar', ['view' => $view])
    @include('partials.pagination')
    <h3>{{ utrans('custom.logins_history') }}</h3>
    <div class="row">
        <div class="col-sm-3 sidenav col-xs-12 m-t-md">
            <form
                method="GET"
                action="{{ url('/admin/history/'. $view) }}"
            >
                <div class="row m-b-sm">
                    <div class="col-xs-3 p-l-lg from-to">{{ uctrans('custom.from') }}:</div>
                    <div class="col-md-7 col-sm-8 text-left search-field admin">
                        <input class="js-from-filter datepicker input-border-r-12 form-control" name="period_from" value="{{ $range['from'] }}">
                    </div>
                </div>
                <div class="row m-b-sm">
                    <div class="col-xs-3 p-l-lg from-to">{{ uctrans('custom.to') }}:</div>
                    <div class="col-md-7 col-sm-8 text-left search-field admin">
                        <input class="js-to-filter datepicker input-border-r-12 form-control" name="period_to" value="{{ $range['to'] }}">
                    </div>
                </div>
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
                    <a href="#" class="clicable"><i class="fa fa-angle-down"></i>&nbsp;&nbsp;{{ utrans('custom.organisations') }}</a>
                    <ul class="sidebar-submenu">
                        <li>
                            <a
                                href="{{
                                    !isset(app('request')->input()['orgs_count'])
                                    ? action(
                                        'Admin\HistoryController@history', array_merge(
                                            [
                                                'orgs_count'    => $orgDropCount,
                                                'type'          => $view,
                                                'page'          => 1,
                                            ],
                                            array_except(app('request')->input(), ['orgs_count', 'page'])
                                        )
                                    )
                                    : action(
                                        'Admin\HistoryController@history', array_merge(
                                            [
                                                'orgs_count'    => null,
                                                'org'           => [],
                                                'type'          => $view,
                                                'page'          => 1,
                                            ],
                                            array_except(app('request')->input(), ['org', 'orgs_count', 'page'])
                                        )
                                    )
                                }}"
                                class="{{
                                    isset(app('request')->input()['orgs_count']) && app('request')->input()['orgs_count'] == $orgDropCount
                                        ? 'active'
                                        : ''
                                }}"
                            >{{ !isset(app('request')->input()['orgs_count']) ? __('custom.show_all') : __('custom.clear_filter') }}</a>
                        </li>
                        @foreach ($organisations as $id => $org)
                            <li>
                                <a
                                    href="{{
                                        !in_array($id, $selectedOrgs)
                                            ? action(
                                                'Admin\HistoryController@history', array_merge(
                                                    [
                                                        'org'   => array_merge([$id], $selectedOrgs),
                                                        'type'  => $view,
                                                        'page'  => 1,
                                                    ],
                                                    array_except(app('request')->input(), ['org', 'page'])
                                                )
                                            )
                                            : action(
                                                'Admin\HistoryController@history', array_merge(
                                                    [
                                                        'org'   => array_diff($selectedOrgs, [$id]),
                                                        'type'  => $view,
                                                        'page'  => 1,
                                                    ],
                                                    array_except(app('request')->input(), ['org', 'page'])
                                                )
                                            )
                                    }}"
                                    class="{{
                                        isset($selectedOrgs) && in_array($id, $selectedOrgs)
                                            ? 'active'
                                            : ''
                                    }}"
                                >{{ $org }}</a>
                            </li>
                        @endforeach
                    </ul>
                </li>
            </ul>
            @if ($view == 'action')
                <ul class="nav">
                    <li class="js-show-submenu">
                        <a href="#" class="clicable"><i class="fa fa-angle-down"></i>&nbsp;&nbsp;{{ uctrans('custom.module') }}</a>
                        <ul class="sidebar-submenu">
                            @foreach ($modules as $id => $module)
                                <li>
                                    <a
                                        href="{{
                                            !in_array($module, $selectedModules)
                                            ? action(
                                                'Admin\HistoryController@history', array_merge(
                                                    [
                                                        'module'    => array_merge([$module],$selectedModules),
                                                        'type'      => $view,
                                                        'page'      => 1,
                                                    ],
                                                    array_except(app('request')->input(), ['module', 'page'])
                                                )
                                            )
                                            : action(
                                                'Admin\HistoryController@history', array_merge(
                                                    [
                                                        'module'    => array_diff($selectedModules, [$module]),
                                                        'type'      => $view,
                                                        'page'      => 1,
                                                    ],
                                                    array_except(app('request')->input(), ['module', 'page'])
                                                )
                                            )
                                        }}"
                                        class="{{
                                            isset($selectedModules) && in_array($module, $selectedModules)
                                                ? 'active'
                                                : ''
                                        }}"
                                    >{{ $module }}</a>
                                </li>
                            @endforeach
                        </ul>
                    </li>
                </ul>
                <ul class="nav">
                    <li class="js-show-submenu">
                        <a href="#" class="clicable"><i class="fa fa-angle-down"></i>&nbsp;&nbsp;{{ uctrans('custom.action') }}</a>
                        <ul class="sidebar-submenu">
                            @foreach ($actionTypes as $id => $action)
                                <li>
                                    <a
                                        href="{{
                                            !in_array($id, $selectedActions)
                                            ? action(
                                                'Admin\HistoryController@history', array_merge(
                                                    [
                                                        'action'    => array_merge([$id], $selectedActions),
                                                        'type'      => $view,
                                                        'page'      => 1,
                                                    ],
                                                    array_except(app('request')->input(), ['action', 'page'])
                                                )
                                            )
                                            : action(
                                                'Admin\HistoryController@history', array_merge(
                                                    [
                                                        'action'    => array_diff($selectedActions, [$id]),
                                                        'type'      => $view,
                                                        'page'      => 1,
                                                    ],
                                                    array_except(app('request')->input(), ['action', 'page'])
                                                )
                                            )
                                        }}"
                                        class="{{
                                            isset($selectedActions) && in_array($id, $selectedActions)
                                                ? 'active'
                                                : ''
                                        }}"
                                    >{{  $action }}</a>
                                </li>
                            @endforeach
                        </ul>
                    </li>
                </ul>
            @endif
            <ul class="nav">
                <li class="js-show-submenu">
                    <a href="#" class="clicable"><i class="fa fa-angle-down"></i>&nbsp;&nbsp;{{ utrans('custom.users') }}</a>
                    <ul class="sidebar-submenu">
                        <li>
                            <a
                                href="{{
                                    !isset(app('request')->input()['users_count'])
                                    ? action(
                                        'Admin\HistoryController@history', array_merge(
                                            [
                                                'users_count'   => $userDropCount,
                                                'type'          => $view,
                                                'page'          => 1,
                                            ],
                                            array_except(app('request')->input(), ['users_count', 'page'])
                                        )
                                    )
                                    : action(
                                        'Admin\HistoryController@history', array_merge(
                                            [
                                                'users_count'  => null,
                                                'user'         => [],
                                                'type'         => $view,
                                                'page'         => 1,
                                            ],
                                            array_except(app('request')->input(), ['user', 'users_count', 'page'])
                                        )
                                    )
                                }}"
                                class="{{
                                    isset(app('request')->input()['users_count']) && app('request')->input()['users_count'] == $userDropCount
                                        ? 'active'
                                        : ''
                                }}"
                            >{{ !isset(app('request')->input()['users_count']) ? __('custom.show_all') : __('custom.clear_filter') }}</a>
                        </li>
                        @foreach ($users as $id => $user)
                            <li>
                                <a
                                    href="{{
                                        empty($selectedUser)
                                        ? action(
                                            'Admin\HistoryController@history', array_merge(
                                                [
                                                    'user'  => $id,
                                                    'type'  => $view,
                                                    'page'  => 1,
                                                ],
                                                array_except(app('request')->input(), ['user', 'page'])
                                            )
                                        )
                                        : action(
                                            'Admin\HistoryController@history', array_merge(
                                                [
                                                    'user'  => [],
                                                    'type'  => $view,
                                                    'page'  => 1,
                                                ],
                                                array_except(app('request')->input(), ['user', 'page'])
                                            )
                                        )
                                    }}"
                                    class="{{
                                        isset(app('request')->input()['user']) && $id == $selectedUser
                                            ? 'active'
                                            : ''
                                    }}"
                                >{{ $user }}</a>
                            </li>
                        @endforeach
                    </ul>
                </li>
            </ul>
            <ul class="nav">
                <li class="js-show-submenu">
                    <a href="#" class="clicable"><i class="fa fa-angle-down"></i>&nbsp;&nbsp;{{ utrans('custom.ip_address') }}</a>
                    <ul class="sidebar-submenu">
                        <li>
                            <a
                                href="{{
                                    !isset(app('request')->input()['ips_count'])
                                    ? action(
                                        'Admin\HistoryController@history', array_merge(
                                            [
                                                'ips_count' => $ipDropCount,
                                                'type'      => $view,
                                                'page'      => 1,
                                            ],
                                            array_except(app('request')->input(), ['ips_count', 'page'])
                                        )
                                    )
                                    : action(
                                        'Admin\HistoryController@history', array_merge(
                                            [
                                                'ips_count'  => null,
                                                'ip'         => [],
                                                'type'       => $view,
                                                'page'       => 1,
                                            ],
                                            array_except(app('request')->input(), ['ip', 'ips_count', 'page'])
                                        )
                                    )
                                }}"
                                class="{{
                                    isset(app('request')->input()['ips_count']) && app('request')->input()['ips_count'] == $userDropCount
                                        ? 'active'
                                        : ''
                                }}"
                            >{{ !isset(app('request')->input()['ips_count']) ? __('custom.show_all') : __('custom.clear_filter') }}</a>
                        </li>
                        @foreach ($ips as $ip)
                            <li>
                                <a
                                    href="{{
                                        action(
                                            'Admin\HistoryController@history', array_merge(
                                                [
                                                    'ip'    => $ip,
                                                    'type'  => $view,
                                                    'page'  => 1,
                                                ],
                                                array_except(app('request')->input(), ['ip', 'page'])
                                            )
                                        )
                                    }}"
                                    class="{{
                                        isset(app('request')->input()['ip']) && $ip == $selectedIp
                                            ? 'active'
                                            : ''
                                    }}"
                                >{{ $ip }}</a>
                            </li>
                        @endforeach
                    </ul>
                </li>
            </ul>
        </div>
    </div>
    <div class="col-lg-9 col-sm-12 sort-links">
            <div class="col-sm-9 col-xs-12 m-t-lg m-b-md">{{ __('custom.order_by') }}</div>
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
    <div class="row">
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
                    <div class="col-sm-12 m-t-xl text-center no-info">
                        {{ __('custom.no_info') }}
                    </div>
                @endif
            </div>
            @include('partials.pagination')
            <div class="col-lg-12 text-right">
                <button
                    class="btn btn-primary add"
                    type="submit"
                    name="download"
                >{{ uctrans('custom.download') }}</button>
            </div>
        </form>
    </div>
</div>
@endsection
