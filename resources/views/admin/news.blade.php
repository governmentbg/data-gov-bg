@extends('layouts.app')

@section('content')
    <div class="container">
        @include('partials.alerts-bar')
        @include('partials.admin-nav-bar', ['view' => 'news'])
        @include('partials.pagination')
        <div class="col-xs-12 sidenav m-t-lg">
            <span class="my-profile m-l-sm">{{ uctrans('custom.news') }}</span>
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
                                            'Admin\NewsController@list',
                                            array_merge(
                                                ['active' => 1],
                                                array_except(app('request')->input(), ['active', 'news'])
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
                                            'Admin\NewsController@list',
                                            array_merge(
                                                ['active' => 0],
                                                array_except(app('request')->input(), ['active', 'news'])
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
                                            'Admin\NewsController@list',
                                            array_except(app('request')->input(), ['active', 'news'])
                                        )
                                    }}"
                                >{{ __('custom.show_all') }}</a>
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
        <div class="col-sm-3 sidenav col-xs-12 m-t-md">
            <form
                method="GET"
                action="{{ url('admin/news/list/') }}"
            >
                <div class="row m-b-sm">
                    <div class="col-xs-3 p-l-lg from-to">{{ uctrans('custom.from') }}:</div>
                    <div class="col-md-7 col-sm-8 text-left search-field admin">
                        <input class="js-from-filter datepicker input-border-r-12 form-control" name="date_from" value="{{ $range['from'] }}">
                    </div>
                </div>
                <div class="row m-b-sm">
                    <div class="col-xs-3 p-l-lg from-to">{{ uctrans('custom.to') }}:</div>
                    <div class="col-md-7 col-sm-8 text-left search-field admin">
                        <input class="js-to-filter datepicker input-border-r-12 form-control" name="date_to" value="{{ $range['to'] }}">
                    </div>
                </div>
            </form>
        </div>
        <div class="row">
            <div class="col-xs-12 text-right">
                <span class="badge badge-pill m-t-md new-data user-add-btn">
                    <a href="{{ url('admin/news/add') }}">{{ __('custom.add') }}</a>
                </span>
            </div>
        </div>
        <div class="row m-b-lg">
            @if (count($news))
                <form method="POST" class="form-horizontal">
                    {{ csrf_field() }}
                    <div class="col-xs-12 m-l-sm">
                        <div class="m-t-md">
                            <div class="table-responsive opn-tbl text-center">
                                <table class="table">
                                    <thead>
                                        <th>{{ utrans('custom.title') }}</th>
                                        <th>{{ utrans('custom.forum') }}</th>
                                        <th>{{ utrans('custom.active') }}</th>
                                        <th>{{ __('custom.valid_from') }}</th>
                                        <th>{{ __('custom.valid_to') }}</th>
                                        <th>{{ __('custom.action') }}</th>
                                    </thead>
                                    <tbody>
                                        @foreach ($news as $signleNews)

                                            <tr>
                                                <td class="name">{{ $signleNews->title }}</td>
                                                <td>
                                                    {{
                                                        !empty($signleNews->forum_link)
                                                            ? __('custom.yes')
                                                            :  __('custom.no')
                                                    }}
                                                </td>
                                                <td>
                                                    {{
                                                        !empty($signleNews->active)
                                                            ? __('custom.yes')
                                                            :  __('custom.no')
                                                    }}
                                                </td>
                                                <td>{{ $signleNews->valid_from }}</td>
                                                <td>{{ $signleNews->valid_to }}</td>
                                                <td class="buttons">
                                                    <a
                                                        class="link-action"
                                                        href="{{ url('admin/news/edit/'. $signleNews->id) }}"
                                                    >{{ utrans('custom.edit') }}</a>
                                                    <a
                                                        class="link-action"
                                                        href="{{ url('admin/news/view/'. $signleNews->id) }}"
                                                    >{{ utrans('custom.preview') }}</a>
                                                    <a
                                                        class="link-action red"
                                                        href="{{ url('admin/news/delete/'. $signleNews->id) }}"
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
