
@extends('layouts.app')

@section('content')
    <div class="container">
        @include('partials.alerts-bar')
        @include('partials.admin-nav-bar', ['view' => 'termsConditionsReq'])
        <div class="col-xs-12 m-t-lg m-b-md p-l-r-none">
            <span class="my-profile m-l-sm">{{ uctrans('custom.terms_of_use_list') }}</span>
        </div>
        <div class="row m-t-md">
            @include('partials.admin-sidebar', [
                'action' => 'Admin\TermsOfUseRequestController@list',
                'options' => ['range', 'state', 'search']
            ])
            <div class="col-sm-9 col-xs-12 p-l-r-none">
                <div class="row">
                    <div class="col-xs-12 m-t-sm m-b-sm p-l-lg">{{ __('custom.order_by') }}:</div>
                    <div class="col-xs-12 p-l-lg order-documents m-b-md">
                        <a
                            href="{{
                                action(
                                    'Admin\TermsOfUseRequestController@list',
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
                                    'Admin\TermsOfUseRequestController@list',
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
                                    'Admin\TermsOfUseRequestController@list',
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
                                    'Admin\TermsOfUseRequestController@list',
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
                    @if (count($terms))
                        @foreach ($terms as $index => $request)
                            <div class="col-xs-12 {{ $index ? 'm-t-lg' : '' }}">
                                <div class="col-sm-9 col-xs-12"><h4>{{ utrans('custom.request') }} - {{ $request->created_by }} - {{ $request->created_at }}</h4></div>
                                <div
                                    class="col-sm-3 col-xs-12 text-right js-terms-req-preview"
                                    data-index="{{ $index }}"
                                    data-action="show"
                                >
                                    <span class="badge badge-pill m-b-sm">{{ uctrans('custom.preview') }}</span>
                                </div>
                                <div class="hidden {{ 'js-terms-req-cont-'. $index }}">
                                    <div class="col-xs-12">{{ $request->firstname .' '. $request->lastname }}</div>
                                    <div class="col-xs-12 m-t-md">{{ $request->email }}</div>
                                    <div class="col-xs-12 m-t-xs">{{ $request->description }}</div>
                                    <div class="col-md-3 col-sm-4 col-xs-6 terms-hr"><hr/></div>
                                    <div class="col-xs-12">{{ __('custom.created_at') }}: &nbsp; {{ $request->created_at }}</div>
                                    <div class="col-xs-12">{{ __('custom.created_by') }}: &nbsp; {{ $request->created_by }}</div>
                                    <div class="col-xs-12">{{ __('custom.updated_at') }}: &nbsp; {{ $request->updated_at }}</div>
                                    <div class="col-xs-12">{{ __('custom.updated_by') }}: &nbsp; {{ $request->updated_by }}</div>
                                </div>
                            </div>
                            <div class="col-xs-12 m-l-sm text-right m-t-xs m-b-lg {{ 'js-terms-req-btns-'. $index }} hidden">
                                <span
                                    class="badge badge-pill m-r-md m-b-sm js-terms-req-close"
                                    data-index="{{ $index }}"
                                    data-action="close"
                                >{{ __('custom.close') }}</span>
                                <span class="badge badge-pill m-r-md m-b-sm">
                                    <a
                                        href="{{ url('/admin/terms-of-use-request/edit/'. $request->id) }}"
                                    >{{ uctrans('custom.edit') }}</a>
                                </span>
                                <span class="badge del-btn badge-pill m-r-md m-b-sm">
                                    <a
                                        href="{{ url('/admin/terms-of-use-request/delete/'. $request->id) }}"
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
@endsection
