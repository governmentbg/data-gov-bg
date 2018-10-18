
@extends('layouts.app')

@section('content')
    <div class="container">
        @include('partials.alerts-bar')
        @include('partials.admin-nav-bar', ['view' => 'dataRequests'])
        <div class="col-xs-12 m-t-lg m-b-md p-l-r-none">
            <span class="my-profile m-l-sm">{{uctrans('custom.datareq_list')}}</span>
        </div>
        <div class="row m-b-lg">
            @include('partials.admin-sidebar', [
                'action' => 'Admin\DataRequestController@listDataRequests',
                'options' => ['range', 'status', 'search']
            ])
            <div class="col-sm-9 col-xs-12">
                <div class="row">
                    <div class="col-xs-12 m-b-sm">{{ __('custom.order_by') }}:</div>
                    <div class="col-xs-12 order-documents">
                        <a
                            href="{{
                                action(
                                    'Admin\DataRequestController@listDataRequests',
                                    array_merge(
                                        ['order' => 'created_at'],
                                        array_except(app('request')->input(), ['order'])
                                    )
                                )
                            }}"

                            class="{{
                                isset(app('request')->input()['order'])
                                && app('request')->input()['order'] == 'created_at'
                                    ? 'active'
                                    : ''
                            }}"
                        >{{ uctrans('custom.req_creation_date') }}</a>
                        <a
                            href="{{
                                action(
                                    'Admin\DataRequestController@listDataRequests',
                                    array_merge(
                                        ['order' => 'status'],
                                        array_except(app('request')->input(), ['order'])
                                    )
                                )
                            }}"

                            class="{{
                                isset(app('request')->input()['order'])
                                && app('request')->input()['order'] == 'status'
                                    ? 'active'
                                    : ''
                            }}"
                        >{{ uctrans('custom.status') }}</a>
                        <a
                            href="{{
                                action(
                                    'Admin\DataRequestController@listDataRequests',
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
                                    'Admin\DataRequestController@listDataRequests',
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
                    @include('partials.pagination')
                    <div class="col-xs-12 m-b-lg">
                        <div class="row">
                            @if (count($dataRequests))
                                @foreach ($dataRequests as $dataRequest)
                                    <div class="col-xs-12 {{ $dataRequest->id ? 'm-t-lg' : '' }}">
                                        <div class="row">
                                            <div class="col-sm-9 col-xs-12">
                                                <h4>{{ utrans('custom.request') }} - {{ $dataRequest->id }} - {{ $dataRequest->created_at }}</h4>
                                            </div>
                                            <div
                                                class="col-sm-3 col-xs-12 text-right js-terms-req-preview"
                                                data-index="{{ $dataRequest->id }}"
                                                data-action="show"
                                            >
                                                <span class="badge badge-pill m-b-sm">{{ uctrans('custom.preview') }}</span>
                                            </div>
                                            <div class="hidden {{ 'js-terms-req-cont-'. $dataRequest->id }}">
                                                <div class="col-xs-12">{{ $dataRequest->contact_name }}</div>
                                                <div class="col-xs-12 m-t-md">{{ $dataRequest->email }}</div>
                                                <div class="col-xs-12 m-t-xs">{!! nl2br($dataRequest->description) !!}</div>
                                                <div class="col-md-3 col-sm-4 col-xs-6 terms-hr"><hr/></div>
                                                <div class="col-xs-12">{{ __('custom.created_at') }}: &nbsp; {{ $dataRequest->created_at }}</div>
                                                <div class="col-xs-12">{{ __('custom.created_by') }}: &nbsp; {{ $dataRequest->created_by }}</div>
                                                @if ($dataRequest->created_at != $dataRequest->updated_at)
                                                    <div class="col-xs-12">{{ __('custom.updated_at') }}: &nbsp; {{ $dataRequest->updated_at }}</div>
                                                    <div class="col-xs-12">{{ __('custom.updated_by') }}: &nbsp; {{ $dataRequest->updated_by }}</div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-xs-12 p-l-r-none m-l-sm text-right m-t-sm m-b-lg {{ 'js-terms-req-btns-'. $dataRequest->id }} hidden">
                                        <span
                                            class="badge badge-pill m-r-md m-b-sm js-terms-req-close"
                                            data-index="{{ $dataRequest->id }}"
                                            data-action="close"
                                        >{{ __('custom.close') }}</span>
                                        <span class="badge badge-pill m-r-md m-b-sm">
                                            <a
                                                href="{{ url('/admin/data-request/edit/'. $dataRequest->id) }}"
                                            >{{ uctrans('custom.edit') }}</a>
                                        </span>
                                        <span class="badge del-btn badge-pill m-r-md m-b-sm">
                                            <a
                                                href="{{ url('/admin/data-request/delete/'. $dataRequest->id) }}"
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
        </div>
    </div>
@endsection
