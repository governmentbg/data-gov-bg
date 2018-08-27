@extends('layouts.app')

@section('content')
    <div class="container">
        @include('partials.alerts-bar')
        @include('partials.admin-nav-bar', ['view' => 'organisation'])
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
                        class="m-t-md input-border-r-12 form-control"
                        placeholder="{{ __('custom.search') }}"
                        value="{{ isset($search) ? $search : '' }}"
                        name="q"
                    >
                </form>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-3 sidenav hidden-xs">
                <ul class="nav">
                    <li class="js-show-submenu">
                        <a href="#" class="clicable"><i class="fa fa-angle-down"></i>&nbsp;&nbsp;{{ uctrans('custom.approved_side') }}</a>
                        <ul class="sidebar-submenu">
                            <li>
                                <a
                                    href="{{
                                        action(
                                            'Admin\OrganisationController@list',
                                            array_merge(
                                                ['approved' => 1],
                                                array_except(app('request')->input(), ['approved', 'q'])
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
                                            'Admin\OrganisationController@list',
                                            array_merge(
                                                ['approved' => 0],
                                                array_except(app('request')->input(), ['approved', 'q'])
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
                                            'Admin\OrganisationController@list',
                                            array_except(
                                                app('request')->input(),
                                                ['approved', 'q']
                                            )
                                        )
                                    }}"
                                >{{ __('custom.show_all') }}</a>
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
                                            'Admin\OrganisationController@list',
                                            array_merge(
                                                ['active' => 1],
                                                array_except(app('request')->input(), ['active', 'q'])
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
                                            'Admin\OrganisationController@list',
                                            array_merge(
                                                ['active' => 0],
                                                array_except(app('request')->input(), ['active', 'q'])
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
                                            'Admin\OrganisationController@list',
                                            array_except(app('request')->input(), ['active', 'q'])
                                        )
                                    }}"
                                >{{ __('custom.show_all') }}</a>
                            </li>
                        </ul>
                    </li>
                </ul>
                <div class="form-group">
                    <div class="col-sm-10 search-field admin">
                        <form
                            method="GET"
                            class="form-horisontal"
                            action="{{ action('Admin\OrganisationController@list', []) }}"
                        >
                            <div class="form-group row m-b-lg m-t-md">
                                <div class="col-lg-10">
                                    <select
                                        class="js-ajax-autocomplete-org form-control js-parent-org-filter"
                                        data-url="{{ url('/api/searchOrganisations') }}"
                                        data-post="{{ json_encode(['api_key' => \Auth::user()->api_key]) }}"
                                        data-placeholder="{{__('custom.main_org')}}"
                                        name="parent"
                                    >
                                        <option></option>
                                        @if (isset($selectedOrg) && !is_null($selectedOrg))
                                            <option value="{{ $selectedOrg->uri }}" selected>{{ $selectedOrg->name }}</option>
                                        @endif
                                    </select>
                                </div>
                            </div>
                            @if (isset(app('request')->input()['active']))
                                <input type="hidden" name="active" value="{{ app('request')->input()['active'] }}">
                            @endif
                            @if (isset(app('request')->input()['approved']))
                                <input type="hidden" name="approved" value="{{ app('request')->input()['approved'] }}">
                            @endif
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-xs-9 m-t-md list-orgs user-orgs">
                <div class="row">
                    @if (count($organisations))
                        @foreach ($organisations as $key => $organisation)
                            <div class="col-md-4 col-sm-12 org-col">
                                <div class="col-xs-12 m-t-lg">
                                    <a href="{{ url('/admin/organisations/view/'. $organisation->uri) }}">
                                        <img class="img-responsive logo" src="{{ $organisation->logo }}"/>
                                    </a>
                                </div>
                                <div class="col-xs-12">
                                    <a href="{{ url('/admin/organisations/view/'. $organisation->uri) }}"><h3 class="org-name">{{ $organisation->name }}</h3></a>
                                    <div class="org-desc">{{ $organisation->description }}</div>
                                    <p class="text-right show-more">
                                        <a href="{{ url('/admin/organisations/view/'. $organisation->uri) }}" class="view-profile">{{ __('custom.see_more') }}</a>
                                    </p>
                                </div>
                                <div class="col-xs-12 ch-del-btns">
                                    <div class="row">
                                        @if (\App\Role::isAdmin($organisation->id))
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
