@extends('layouts.app')

@section('content')
    <div class="container">
        @include('partials.alerts-bar')
        @include('partials.admin-nav-bar', ['view' => 'documents'])
        <div class="col-xs-12 m-t-lg m-b-md p-l-r-none">
            <span class="my-profile m-l-sm">{{ __('custom.document_list') }}</span>
        </div>
        <div class="row m-b-md">
            <div class="col-md-3 col-sm-4 col-xs-12 text-left"></div>
            <div class="col-md-9 col-sm-8 col-xs-12 search-field admin">
                <form method="GET" action="{{ url('/admin/documents/search') }}">
                    <input
                        type="text"
                        class="input-border-r-12 form-control js-ga-event"
                        placeholder="{{ __('custom.search') }}"
                        value="{{ isset($search) ? $search : '' }}"
                        name="q"
                        data-ga-action="search"
                        data-ga-label="data search"
                        data-ga-category="data"
                    >
                </form>
            </div>
        </div>
        @include('partials.pagination')
        <div class="row m-b-lg">
            @include('partials.admin-sidebar', [
                'action' => 'Admin\DocumentController@list',
                'options' => ['range', 'dateOf']
            ])
            <div class="col-sm-9 col-xs-12">
                <div class="row">
                    <div class="col-xs-12 m-b-sm">{{ __('custom.order_by_c') }}:</div>
                    <div class="col-xs-12 order-documents">
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
                        >{{ uctrans('custom.order_asc') }}</a>
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
                        >{{ uctrans('custom.order_desc') }}</a>
                    </div>
                    <div class="col-xs-12 text-right">
                        <a
                            class="btn btn-primary add pull-right"
                            href="{{ url('/admin/documents/add') }}"
                        >{{ __('custom.add') }}</a> 
                    </div>
                </div>
                <div class="row m-b-lg">
                    @if (count($documents))
                        <form method="POST" class="form-horizontal">
                            {{ csrf_field() }}
                            <div class="col-xs-12">
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
