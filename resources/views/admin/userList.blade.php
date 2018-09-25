@extends('layouts.app')

@section('content')
<div class="container admin">
    @include('partials.alerts-bar')
    @include('partials.admin-nav-bar', ['view' => 'users'])
    <div class="row">
        <div class="col-xs-12 sidenav m-t-lg m-b-lg">
            <span class="my-profile m-l-sm">{{uctrans('custom.users_list')}}</span>
        </div>
        <div class="col-lg-8 col-md-7 col-sm-12 col-xs-12 search-field admin">
            <form method="GET">
                <input
                    type="text"
                    class="m-t-md input-border-r-12 form-control"
                    placeholder="{{ __('custom.search') }}"
                    value="{{ isset($search) ? $search : '' }}"
                    name="q"
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
        <div class="col-sm-3 sidenav p-l-r-none hidden-xs">
            <ul class="nav">
                <li class="js-show-submenu">
                    <a href="#" class="clicable"><i class="fa fa-angle-down"></i>&nbsp;&nbsp;{{ utrans('custom.organisations') }}</a>
                    <ul class="sidebar-submenu">
                        <li>
                            <a
                                href="{{
                                    !isset(app('request')->input()['orgs_count'])
                                    ? action(
                                        'Admin\UserController@list', array_merge(
                                            ['orgs_count' => $orgDropCount, 'page' => 1],
                                            array_except(app('request')->input(), ['orgs_count'])
                                        )
                                    )
                                    : action(
                                        'Admin\UserController@list', array_merge(
                                            [
                                                'orgs_count'    => null,
                                                'org'           => [],
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
                                                'Admin\UserController@list', array_merge(
                                                    ['org' => array_merge([$id], $selectedOrgs), 'page' => 1],
                                                    array_except(app('request')->input(), ['org', 'page'])
                                                )
                                            )
                                            : action(
                                                'Admin\UserController@list', array_merge(
                                                    ['org' => array_diff($selectedOrgs, [$id]), 'page' => 1],
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
            <ul class="nav">
                <li class="js-show-submenu">
                    <a href="#" class="clicable"><i class="fa fa-angle-down"></i>&nbsp;&nbsp;{{ uctrans('custom.roles') }}</a>
                    <ul class="sidebar-submenu">
                        @foreach ($roles as $role)
                            <li>
                                <a
                                    href="{{
                                        !in_array($role->id, $selectedRoles)
                                        ? action(
                                            'Admin\UserController@list', array_merge(
                                                ['role' => array_merge([$role->id], $selectedRoles), 'page' => 1],
                                                array_except(app('request')->input(), ['role', 'page'])
                                            )
                                        )
                                        : action(
                                            'Admin\UserController@list', array_merge(
                                                ['role' => array_diff($selectedRoles, [$role->id]), 'page' => 1],
                                                array_except(app('request')->input(), ['role', 'page'])
                                            )
                                        )
                                    }}"
                                    class="{{
                                        isset($selectedRoles) && in_array($role->id, $selectedRoles)
                                            ? 'active'
                                            : ''
                                    }}"
                                >{{ $role->name }}</a>
                            </li>
                        @endforeach
                    </ul>
                </li>
            </ul>
            <ul class="nav">
                <li class="js-show-submenu">
                    <a href="#" class="clicable"><i class="fa fa-angle-down"></i>&nbsp;&nbsp;{{ uctrans('custom.approved_side') }}</a>
                    <ul class="sidebar-submenu">
                        <li>
                            <a
                                href="{{
                                    action(
                                        'Admin\UserController@list',
                                        array_merge(
                                            ['approved' => 1, 'page' => 1],
                                            array_except(app('request')->input(), ['approved', 'page'])
                                        )
                                    )
                                }}"
                                class="{{
                                    isset(app('request')->input()['approved']) && app('request')->input()['approved']
                                        ? 'active'
                                        : ''
                                }}"
                            >{{ __('custom.show_approved') }}</a>
                        </li>
                        <li>
                            <a
                                href="{{
                                    action(
                                        'Admin\UserController@list',
                                        array_merge(
                                            ['approved' => 0, 'page' => 1],
                                            array_except(app('request')->input(), ['approved', 'page'])
                                        )
                                    )
                                }}"
                                class="{{
                                    isset(app('request')->input()['approved']) && !app('request')->input()['approved']
                                        ? 'active'
                                        : ''
                                }}"
                            >{{ __('custom.hide_approved') }}</a>
                        </li>
                        <li>
                            <a
                                href="{{
                                    action(
                                        'Admin\UserController@list',
                                        array_except(
                                            app('request')->input(),
                                            ['approved']
                                        )
                                    )
                                }}"
                            >{{ __('custom.clear_filter') }}</a>
                        </li>
                    </ul>
                </li>
            </ul>
            <ul class="nav">
                <li class="js-show-submenu">
                    <a href="#" class="clicable"><i class="fa fa-angle-down"></i>&nbsp;&nbsp;{{ __('custom.active_side') }}</a>
                    <ul class="sidebar-submenu m-b-md">
                        <li>
                            <a
                                href="{{
                                    action(
                                        'Admin\UserController@list',
                                        array_merge(
                                            ['active' => 1, 'page' => 1],
                                            array_except(app('request')->input(), ['active', 'page'])
                                        )
                                    )
                                }}"
                                class="{{
                                    isset(app('request')->input()['active']) && app('request')->input()['active']
                                        ? 'active'
                                        : ''
                                }}"
                            >{{ __('custom.show_active') }}</a>
                        </li>
                        <li>
                            <a
                                href="{{
                                    action(
                                        'Admin\UserController@list',
                                        array_merge(
                                            ['active' => 0, 'page' => 1],
                                            array_except(app('request')->input(), ['active', 'page'])
                                        )
                                    )
                                }}"
                                class="{{
                                    isset(app('request')->input()['active']) && !app('request')->input()['active']
                                        ? 'active'
                                        : ''
                                }}"
                            >{{ __('custom.hide_active') }}</a>
                        </li>
                        <li>
                            <a
                                href="{{
                                    action(
                                        'Admin\UserController@list',
                                        array_except(app('request')->input(), ['active'])
                                    )
                                }}"
                            >{{ __('custom.clear_filter') }}</a>
                        </li>
                    </ul>
                </li>
            </ul>
            <form method="GET" class="inline-block">
                <div class="form-group adm-filter">
                    <label for="is_admin" class="col-lg-8 col-sm-8 col-xs-12">{{ __('custom.admin') }}:</label>
                    <div class="col-lg-4 col-sm-4 col-xs-12">
                        <input
                            type="checkbox"
                            class="js-check js-submit form-control"
                            id="is_admin"
                            name="is_admin"
                            value="1"
                            {{ $adminFilter ? 'checked' : '' }}
                        >
                        @foreach (app('request')->except(['is_admin', 'page']) as $key => $value)
                            @if (is_array($value))
                                @foreach ($value as $innerValue)
                                    <input name="{{ $key }}[]" type="hidden" value="{{ $innerValue }}">
                                @endforeach
                            @else
                                <input name="{{ $key }}" type="hidden" value="{{ $value }}">
                            @endif
                        @endforeach
                    </div>
                </div>
            </form>
        </div>
        <div class="col-xs-9 m-t-md list-orgs user-orgs">
            <a
                class="pull-right badge cust-btn badge-pill m-b-sm"
                data-toggle="modal"
                data-target="#invite"
            >{{ __('custom.invite_by_mail') }}</a>
            <a
                class="pull-right badge cust-btn badge-pill m-b-sm"
                href="{{ url('/admin/users/create') }}"
            >{{ __('custom.new_user') }}</a>

            @if (!empty($users))
                @include('partials.pagination')
                @foreach ($users as $member)
                    <div class="col-xs-12 p-l-none">
                        <h3 class="m-b-md">{{
                            empty($member->firstname)
                            ? $member->username
                            : $member->firstname .' '. $member->lastname
                        }}</h3>
                        @if ($isAdmin)
                            <div class="js-member-admin-controls">
                                <a
                                    class="badge cust-btn badge-pill m-b-sm"
                                    href="{{ url('/admin/users/edit/'. $member->id) }}"
                                >{{ utrans('custom.edit') }}</a>
                            </div>
                        @endif
                    </div>
                @endforeach
                @include('partials.pagination')
            @else
                <div class="row">
                    <div class="col-sm-12 m-t-xl text-center no-info">
                        {{ __('custom.no_info') }}
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<div class="modal inmodal fade" id="invite" tabindex="-1" role="dialog"  aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="frame">
                <div class="p-w-md">
                    <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">{{ __('custom.close') }}</span></button>
                    <h2>{{ __('custom.add_user') }}</h2>
                </div>
                <div class="modal-body">
                    <form method="POST" class="form-horisontal">
                        {{ csrf_field() }}
                        <div class="form-group row m-b-md m-t-md">
                            <label for="email" class="col-lg-2 col-form-label">{{ __('custom.email') }}: </label>
                            <div class="col-lg-10">
                                <input
                                    id="email"
                                    name="email"
                                    type="email"
                                    class="input-border-r-12 form-control"
                                >
                            </div>
                        </div>
                        <div class="form-group row m-b-md m-t-md">
                            <label for="approved" class="col-lg-3 col-sm-8 col-xs-12">{{ utrans('custom.approved') }}:</label>
                            <div class="col-lg-9 col-sm-4 col-xs-12 text-right">
                                <input
                                    type="checkbox"
                                    class="js-check form-control"
                                    id="approved"
                                    name="approved"
                                    value="1"
                                >
                            </div>
                        </div>
                        <div class="form-group row">
                            <div class="col-sm-12 text-right">
                                <button type="button" class="m-l-md btn btn-danger" data-dismiss="modal">{{ __('custom.close') }}</button>
                                <button type="submit" name="invite" class="m-l-md btn btn-custom">{{ __('custom.send') }}</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
