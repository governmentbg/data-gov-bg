@extends('layouts.app')

@section('content')
<div class="container">
    @include('partials.alerts-bar')
    @include('partials.admin-nav-bar', ['view' => 'dataset'])
    <div class="col-xs-12 m-t-lg p-l-r-none">
        <div class="col-xs-4 p-l-r-none">
            <span class="my-profile m-l-sm">{{ uctrans('custom.datasets_list') }}</span>
        </div>
        <div class="col-xs-8">
            <div class="filter-content org-nav-bar">
                <div class="row">
                    <div class="col-md-12">
                        <ul class="nav filter-type right-border">
                            <li>
                                <a
                                    class="{{ $view == 'datasets' ? 'active' : null }}"
                                    href="{{ url('/admin/datasets') }}"
                                >{{ ultrans('custom.datasets') }}</a>
                            </li>
                            <li>
                                <a
                                    class="{{ $view == 'deletedDatasets' ? 'active' : null }}"
                                    href="{{ url('/admin/datasetsDeleted') }}"
                                >{{ ultrans('custom.deleted_datasets') }}</a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-3 col-sm-4 col-xs-12 text-left">
            <span class="badge badge-pill m-t-lg new-data user-add-btn"><a href="{{ url('/admin/dataset/add') }}">{{ __('custom.add_new_dataset') }}</a></span>
        </div>
        <div class="col-lg-8 col-md-9 col-sm-8 col-xs-12 search-field admin">
            <form method="GET">
                <input
                    type="text"
                    class="m-t-md input-border-r-12 form-control js-ga-event"
                    placeholder="{{ __('custom.search') }}.."
                    value="{{ isset($search) ? $search : '' }}"
                    name="q"
                    data-ga-action="search"
                    data-ga-label="data search"
                    data-ga-category="data"
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
    <div class="row m-t-md">
        @include('partials.admin-sidebar', [
            'action' => 'Admin\DataSetController@listDatasets',
            'options' => ['range', 'organisation', 'group', 'user', 'category', 'tag', 'format', 'terms', 'signal']
        ])
        <div class="col-sm-9 col-xs-12 p-sm page-content p-t-25-xs">
            <div class="row">
                <div class="col-xs-12">{{ __('custom.order_by') }}:</div>
                <div class="col-xs-12 order-datasets">
                    <a
                        href="{{
                            action(
                                'Admin\DataSetController@listDatasets',
                                array_merge(
                                    ['order_field' => 'name'],
                                    array_except(app('request')->input(), ['order_field'])
                                )
                            )
                        }}"

                        class="
                        {{
                            isset(app('request')->input()['order_field'])
                            && app('request')->input()['order_field'] == 'name'
                                ? 'active'
                                : ''
                        }}"
                    >{{ __('custom.relevance') }}</a>
                    <a
                        href="{{
                            action(
                                'Admin\DataSetController@listDatasets',
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
                                'Admin\DataSetController@listDatasets',
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
                    <a
                        href="{{
                            action(
                                'Admin\DataSetController@listDatasets',
                                array_merge(
                                    ['order_field' => 'updated_at'],
                                    array_except(app('request')->input(), ['order_field'])
                                )
                            )
                        }}"

                        class="{{
                            isset(app('request')->input()['order_field'])
                            && app('request')->input()['order_field'] == 'updated_at'
                                ? 'active'
                                : ''
                        }}"
                    >{{ __('custom.last_update') }}</a>
                </div>
            </div>
            @include('partials.pagination')
            <div class="articles m-t-md">
                @if (count($datasets))
                    @foreach ($datasets as $set)
                        <div class="article m-b-lg col-xs-12 user-dataset p-l-r-none">
                            <div>{{ __('custom.date_added') }}: {{ $set->created_at }}</div>
                            <div class="col-xs-12 p-l-r-none">
                                <a href="{{ url('/admin/dataset/view/'. $set->uri) }}">
                                    <h2 class="m-t-xs {{$set->reported ? 'error' : '' }}">{{ $set->name }}</h2>
                                </a>
                                @if ($set->status == App\DataSet::STATUS_DRAFT)
                                    <span>({{ __('custom.draft') }})</span>
                                @else
                                    <span>({{ __('custom.published') }})</span>
                                @endif
                                <div class="desc">
                                    {!! nl2br(e($set->descript)) !!}
                                </div>
                                <div class="col-sm-12 p-l-none btns">
                                    <div class="pull-left row">
                                        <div class="col-xs-6">
                                            <span class="badge badge-pill m-r-md m-b-sm">
                                                <a href="{{ url('/admin/dataset/edit/'. $set->uri) }}">{{ uctrans('custom.edit') }}</a>
                                            </span>
                                        </div>
                                        <div class="col-xs-6">
                                            <form method="POST">
                                                {{ csrf_field() }}
                                                <input type="hidden" name="dataset_uri" value="{{ $set->uri }}">
                                                <div class="col-xs-6 text-right">
                                                    <button
                                                        class="badge badge-pill m-b-sm del-btn"
                                                        type="submit"
                                                        name="delete"
                                                        data-confirm="{{ __('custom.remove_data') }}"
                                                    >{{ uctrans('custom.remove') }}</button>
                                                </div>
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
                    <div class="col-sm-12 m-t-md text-center no-info">
                        {{ __('custom.no_info') }}
                    </div>
                @endif
            </div>
            @include('partials.pagination')
        </div>
    </div>
</div>
@endsection
