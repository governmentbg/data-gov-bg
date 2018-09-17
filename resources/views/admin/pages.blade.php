@extends('layouts.app')

@section('content')
    <div class="container">
        @include('partials.alerts-bar')
        @include('partials.admin-nav-bar', ['view' => 'pages'])
        @include('partials.pagination')
        <div class="col-xs-12 sidenav m-t-lg">
            <span class="my-profile m-l-sm">{{ __('custom.pages') }}</span>
        </div>
        <div class="row">
            <div class="col-md-3 col-sm-5 sidenav p-l-r-none col-xs-12 m-t-md m-l-md">
                <ul class="nav">
                    <li class="js-show-submenu">
                        <a href="#" class="clicable"><i class="fa fa-angle-down"></i>&nbsp;&nbsp;{{ __('custom.active') }}</a>
                        <ul class="sidebar-submenu m-b-md">
                            <li>
                                <a
                                    href="{{
                                        action(
                                            'Admin\PageController@list',
                                            array_merge(
                                                ['active' => 1],
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
                                            'Admin\PageController@list',
                                            array_merge(
                                                ['active' => 0],
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
                                            'Admin\PageController@list',
                                            array_except(app('request')->input(), ['active', 'page'])
                                        )
                                    }}"
                                >{{ __('custom.show_all') }}</a>
                            </li>
                        </ul>
                    </li>
                </ul>
                <ul class="nav">
                    <li class="js-show-submenu">
                        <a href="#" class="clicable"><i class="fa fa-angle-down"></i>&nbsp;&nbsp;{{ ultrans('custom.section') }}</a>
                        <ul class="sidebar-submenu m-b-md">
                            <li>
                                <a
                                    href="{{
                                        action(
                                            'Admin\PageController@list',
                                            array_except(app('request')->input(), ['section', 'page'])
                                        )
                                    }}"
                                >{{ __('custom.show_all') }}</a>
                            </li>
                            @if (count($sections))
                                @foreach ($sections as $id => $sec)
                                    <li>
                                        <a
                                            href="{{
                                                action(
                                                    'Admin\PageController@list',
                                                    array_merge(
                                                        ['section' => $id],
                                                        array_except(app('request')->input(), ['section', 'page'])
                                                    )
                                                )
                                            }}"
                                            class="{{
                                                isset(app('request')->input()['section']) && app('request')->input()['section'] == $id
                                                    ? 'active'
                                                    : ''
                                            }}"
                                        >{{ $sec }}</a>
                                    </li>
                                @endforeach
                            @endif
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
        <div class="row">
            <div class="col-xs-12 text-right">
                <span class="badge badge-pill m-t-md new-data user-add-btn">
                    <a href="{{ url('admin/pages/add') }}">{{ __('custom.add') }}</a>
                </span>
            </div>
        </div>
        <div class="row m-b-lg">
            @if (count($pages))
                <form method="POST" class="form-horizontal">
                    {{ csrf_field() }}
                    <div class="col-xs-12 m-l-sm">
                        <div class="m-t-md">
                            <div class="table-responsive opn-tbl text-center">
                                <table class="table">
                                    <thead>
                                        <th>{{ utrans('custom.title') }}</th>
                                        <th>{{ __('custom.section') }}</th>
                                        <th>{{ utrans('custom.forum') }}</th>
                                        <th>{{ utrans('custom.active') }}</th>
                                        <th>{{ __('custom.valid_from') }}</th>
                                        <th>{{ __('custom.valid_to') }}</th>
                                        <th>{{ __('custom.action') }}</th>
                                    </thead>
                                    <tbody>
                                        @foreach ($pages as $page)

                                            <tr>
                                                <td class="name">{{ $page->title }}</td>
                                                <td>
                                                    {{
                                                        count($sections) && isset($sections[$page->section_id])
                                                            ? $sections[$page->section_id]
                                                            : $page->section_id
                                                    }}
                                                </td>
                                                <td>
                                                    {{
                                                        !empty($page->forum_link)
                                                            ? __('custom.yes')
                                                            :  __('custom.no')
                                                    }}
                                                </td>
                                                <td>
                                                    {{
                                                        !empty($page->active)
                                                            ? __('custom.yes')
                                                            :  __('custom.no')
                                                    }}
                                                </td>
                                                <td>{{ $page->valid_from }}</td>
                                                <td>{{ $page->valid_to }}</td>
                                                <td class="buttons">
                                                    <a
                                                        class="link-action"
                                                        href="{{ url('admin/pages/edit/'. $page->id) }}"
                                                    >{{ utrans('custom.edit') }}</a>
                                                    <a
                                                        class="link-action"
                                                        href="{{ url('admin/pages/view/'. $page->id) }}"
                                                    >{{ utrans('custom.preview') }}</a>
                                                    <a
                                                        class="link-action red"
                                                        href="{{ url('admin/pages/delete/'. $page->id) }}"
                                                        data-confirm="{{ __('custom.remove_data') }}"
                                                    >{{ __('custom.delete') }}</a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </form>
            @else
                <div class="col-sm-12 m-t-xl text-center no-info">
                    {{ __('custom.no_info') }}
                </div>
            @endif
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
