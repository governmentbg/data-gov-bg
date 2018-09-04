@extends('layouts.app')

@section('content')
<div class="container">
    @include('partials.alerts-bar')
    @include('partials.admin-nav-bar', ['view' => 'dataset'])
    <div class="row">
        <div class="col-sm-3 col-xs-12 text-left">
            <span class="badge badge-pill m-t-lg new-data user-add-btn"><a href="{{ url('/admin/dataset/add') }}">{{ __('custom.add_new_dataset') }}</a></span>
        </div>
        <div class="col-lg-8 col-md-7 col-sm-12 col-xs-12 search-field">
            <form method="GET">
                <input
                    type="text"
                    class="m-t-md input-border-r-12 form-control"
                    placeholder="{{ __('custom.search') }}.."
                    value="{{ isset($search) ? $search : '' }}"
                    name="q"
                >
            </form>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-3 sidenav col-xs-12 m-t-md">
            <form
                method="GET"
                action="{{ action('Admin\DataSetController@listDatasets', []) }}"
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
                @if (isset(app('request')->input()['order']))
                    <input type="hidden" name="order" value="{{ app('request')->input()['order'] }}">
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
                                        'Admin\DataSetController@listDatasets', array_merge(
                                            ['orgs_count' => $orgDropCount],
                                            array_except(app('request')->input(), ['orgs_count'])
                                        )
                                    )
                                    : action(
                                        'Admin\DataSetController@listDatasets', array_merge(
                                            [
                                                'orgs_count'    => null,
                                                'org'           => [],
                                            ],
                                            array_except(app('request')->input(), ['org', 'orgs_count'])
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
                                                'Admin\DataSetController@listDatasets', array_merge(
                                                    ['org' => array_merge([$id], $selectedOrgs)],
                                                    array_except(app('request')->input(), ['org'])
                                                )
                                            )
                                            : action(
                                                'Admin\DataSetController@listDatasets', array_merge(
                                                    ['org' => array_diff($selectedOrgs, [$id])],
                                                    array_except(app('request')->input(), ['org'])
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
                    <a href="#" class="clicable"><i class="fa fa-angle-down"></i>&nbsp;&nbsp;{{ utrans('custom.groups') }}</a>
                    <ul class="sidebar-submenu">
                        <li>
                            <a
                                href="{{
                                    !isset(app('request')->input()['groups_count'])
                                    ? action(
                                        'Admin\DataSetController@listDatasets', array_merge(
                                            ['groups_count' => $groupDropCount],
                                            array_except(app('request')->input(), ['groups_count'])
                                        )
                                    )
                                    : action(
                                        'Admin\DataSetController@listDatasets', array_merge(
                                            [
                                                'groups_count'  => null,
                                                'group'         => [],
                                            ],
                                            array_except(app('request')->input(), ['group', 'groups_count'])
                                        )
                                    )
                                }}"
                                class="{{
                                    isset(app('request')->input()['groups_count']) && app('request')->input()['groups_count'] == $groupDropCount
                                        ? 'active'
                                        : ''
                                }}"
                            >{{ !isset(app('request')->input()['groups_count']) ? __('custom.show_all') : __('custom.clear_filter') }}</a>
                        </li>
                        @foreach ($groups as $id => $group)
                            <li>
                                <a
                                    href="{{
                                        !in_array($id, $selectedGroups)
                                            ? action(
                                                'Admin\DataSetController@listDatasets', array_merge(
                                                    ['group' => array_merge([$id], $selectedGroups)],
                                                    array_except(app('request')->input(), ['group'])
                                                )
                                            )
                                            : action(
                                                'Admin\DataSetController@listDatasets', array_merge(
                                                    ['group' => array_diff($selectedGroups, [$id])],
                                                    array_except(app('request')->input(), ['group'])
                                                )
                                            )
                                    }}"
                                    class="{{
                                        isset($selectedGroups) && in_array($id, $selectedGroups)
                                            ? 'active'
                                            : ''
                                    }}"
                                >{{ $group }}</a>
                            </li>
                        @endforeach
                    </ul>
                </li>
            </ul>
        </div>
        <div class="col-xs-9">
            <div class="articles m-t-lg">
                @if (count($datasets))
                    @foreach ($datasets as $set)
                        <div class="article m-b-lg col-xs-12 user-dataset">
                            <div>{{ __('custom.date_added') }}: {{ $set->created_at }}</div>
                            <div class="col-sm-12 p-l-none">
                                <a href="{{ url('/admin/dataset/view/'. $set->uri) }}">
                                    <h2 class="m-t-xs">{{ $set->name }}</h2>
                                </a>
                                @if ($set->status == 1)
                                    <span>({{ __('custom.draft') }})</span>
                                @else
                                    <span>({{ __('custom.published') }})</span>
                                @endif
                                <div class="desc">
                                    {{ $set->descript }}
                                </div>
                                <div class="col-sm-12 p-l-none btns">
                                    <div class="pull-left row">
                                        <div class="col-xs-6">
                                            <span class="badge badge-pill m-r-md m-b-sm">
                                                <a href="{{ url('/admin/dataset/edit/'. $set->uri) }}">{{ uctrans('custom.edit') }}</a>
                                            </span>
                                        </div>
                                        <div class="col-xs-6">
                                            <form method="POST" action="{{ url('/admin/dataset/delete') }}">
                                                {{ csrf_field() }}
                                                <div class="col-xs-6 text-right">
                                                    <button
                                                        class="badge badge-pill m-b-sm del-btn"
                                                        type="submit"
                                                        name="delete"
                                                        data-confirm="{{ __('custom.remove_data') }}"
                                                    >{{ uctrans('custom.remove') }}</button>
                                                </div>
                                                <input type="hidden" name="dataset_uri" value="{{ $set->uri }}">
                                            </form>
                                        </div>
                                    </div>
                                    <div class="pull-right">
                                        <span><a href="{{ url('/admin/dataset/view/'. $set->uri) }}">{{ __('custom.see_more') }}</a></span>
                                    </div>
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
            <div class="col-xs-12 text-center pagination">
                {{ $pagination->render() }}
            </div>
        </div>
    @endif
</div>
@endsection
