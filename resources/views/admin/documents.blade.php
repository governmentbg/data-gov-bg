@extends('layouts.app')

@section('content')
    <div class="container">
        @include('partials.alerts-bar')
        @include('partials.admin-nav-bar', ['view' => 'documents'])
        @include('partials.pagination')
        <div class="col-xs-12 sidenav m-t-lg m-b-lg">
            <span class="my-profile m-l-sm">{{ __('custom.document_list') }}</span>
        </div>
        <div class="row m-b-lg">
            <div class="col-md-3 col-sm-5 sidenav p-l-r-none col-xs-12 m-t-md m-l-sm">
                 <form
                    method="GET"
                    action="{{ action('Admin\DocumentController@list', []) }}"
                >
                    <div class="row m-b-sm">
                        <div class="col-xs-3 p-l-lg from-to">{{ uctrans('custom.from') }}:</div>
                        <div class="col-md-7 col-sm-8 text-left search-field admin">
                            <input class="js-from-filter datepicker input-border-r-12 form-control" name="from" value="{{ isset($range['from']) ? $range['from'] : '' }}">
                        </div>
                    </div>
                    <div class="row m-b-sm">
                        <div class="col-xs-3 p-l-lg from-to">{{ uctrans('custom.to') }}:</div>
                        <div class="col-md-7 col-sm-8 text-left search-field admin">
                            <input class="js-to-filter datepicker input-border-r-12 form-control" name="to" value="{{ isset($range['to']) ? $range['to'] : '' }}">
                        </div>
                    </div>
                    @if (isset(app('request')->input()['dtype']))
                        <input type="hidden" name="status" value="{{ app('request')->input()['dtype'] }}">
                    @endif
                    @if (isset(app('request')->input()['order']))
                        <input type="hidden" name="order" value="{{ app('request')->input()['order'] }}">
                    @endif
                </form>
                <ul class="nav">
                    <li class="js-show-submenu">
                        <a href="#" class="clicable"><i class="fa fa-angle-down"></i>&nbsp;&nbsp;{{ __('custom.date_of') }}</a>
                        <ul class="sidebar-submenu m-b-md">
                            <li>
                                <a
                                    href="{{
                                        action(
                                            'Admin\DocumentController@list',
                                            array_merge(
                                                ['dtype' => \App\Document::DATE_TYPE_CREATED],
                                                array_except(app('request')->input(), ['dtype', 'q', 'page'])
                                            )
                                        )
                                    }}"
                                    class="{{
                                        isset(app('request')->input()['dtype'])
                                        && app('request')->input()['dtype'] == \App\Document::DATE_TYPE_CREATED
                                            ? 'active'
                                            : ''
                                    }}"
                                >{{ utrans('custom.creation') }}</a>
                            </li>
                            <li>
                                <a
                                    href="{{
                                        action(
                                            'Admin\DocumentController@list',
                                            array_merge(
                                                ['dtype' => \App\Document::DATE_TYPE_UPDATED],
                                                array_except(app('request')->input(), ['dtype', 'q', 'page'])
                                            )
                                        )
                                    }}"
                                    class="{{
                                        isset(app('request')->input()['dtype'])
                                        && app('request')->input()['dtype'] == \App\Document::DATE_TYPE_UPDATED
                                            ? 'active'
                                            : ''
                                    }}"
                                >{{ utrans('custom.edit_date') }}</a>
                            </li>
                            <li>
                                <a
                                    href="{{
                                        action(
                                            'Admin\DocumentController@list',
                                            array_except(app('request')->input(), ['dtype', 'q'])
                                        )
                                    }}"
                                >{{ __('custom.by_default') }}</a>
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
        <div class="row m-b-lg">
            <div class="col-lg-8 col-md-6 col-sm-5 col-xs-5 m-t-md p-l-lg">{{ __('custom.order_by_c') }}</div>
            <div class="col-lg-4 col-md-6 col-sm-7 col-xs-7 admin">
                <form method="GET" action="{{ url('/admin/documents/search') }}">
                    <input
                        type="text"
                        class="input-border-r-12 form-control"
                        placeholder="{{ __('custom.search') }}"
                        value="{{ isset($search) ? $search : '' }}"
                        name="q"
                    >
                </form>
            </div>
        </div>
        <div class="row m-b-lg">
            <div class="col-sm-8 col-xs-12 p-l-lg order-documents">
                <a
                    href="{{
                        action(
                            'Admin\DocumentController@list',
                            array_merge(
                                ['order' => 'name'],
                                array_except(app('request')->input(), ['order', 'q'])
                            )
                        )
                    }}"

                    class="{{
                        isset(app('request')->input()['order'])
                        && app('request')->input()['order'] == 'name'
                            ? 'active'
                            : ''
                    }}"
                >{{ utrans('custom.name') }}</a><a
                    href="{{
                        action(
                            'Admin\DocumentController@list',
                            array_merge(
                                ['order' => 'created_at'],
                                array_except(app('request')->input(), ['order', 'q'])
                            )
                        )
                    }}"

                    class="{{
                        isset(app('request')->input()['order'])
                        && app('request')->input()['order'] == 'created_at'
                            ? 'active'
                            : ''
                    }}"
                >{{ __('custom.date_created') }}</a><a
                    href="{{
                        action(
                            'Admin\DocumentController@list',
                            array_merge(
                                ['order' => 'updated_at'],
                                array_except(app('request')->input(), ['order', 'q'])
                            )
                        )
                    }}"

                    class="{{
                        isset(app('request')->input()['order'])
                        && app('request')->input()['order'] == 'updated_at'
                            ? 'active'
                            : ''
                    }}"
                >{{ __('custom.date_updated') }}</a>
                <a
                    href="{{
                        action(
                            'Admin\DocumentController@list',
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
                >{{uctrans('custom.order_asc') }}</a>
                <a
                    href="{{
                        action(
                            'Admin\DocumentController@list',
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
                >{{uctrans('custom.order_desc') }}</a>
            </div>
            <div class="col-sm-4 col-xs-12 text-right">
                <span class="badge badge-pill doc-badge">
                    <a href="{{ url('/admin/documents/add') }}">{{ __('custom.add') }}</a>
                </span>
            </div>
        </div>
        <div class="row m-b-lg">
            @if (count($documents))
                <form method="POST" class="form-horizontal">
                    {{ csrf_field() }}
                    <div class="col-xs-12 m-l-sm">
                        <div class="m-t-md">
                            <div class="table-responsive opn-tbl text-center">
                                <table class="table">
                                    <thead>
                                        <th>{{ utrans('custom.name') }}</th>
                                        <th>{{ __('custom.date_created') }}</th>
                                        <th>{{ __('custom.date_updated') }}</th>
                                        <th>{{ __('custom.action') }}</th>
                                    </thead>
                                    <tbody>
                                        @foreach ($documents as $doc)
                                            <tr>
                                                <td class="name">{{ $doc->name }}</td>
                                                <td>{{ $doc->created_at }}</td>
                                                <td>{{ $doc->updated_at }}</td>
                                                <td class="buttons">
                                                    <a
                                                        class="link-action"
                                                        href="{{ url('/admin/documents/edit/'. $doc->id) }}"
                                                    >{{ utrans('custom.edit') }}</a>
                                                    <a
                                                        class="link-action"
                                                        href="{{ url('/admin/documents/view/'. $doc->id) }}"
                                                    >{{ utrans('custom.preview') }}</a>
                                                    <a
                                                        class="link-action red"
                                                        href="{{ url('/admin/documents/delete/'. $doc->id) }}"
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
