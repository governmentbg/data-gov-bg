@extends('layouts.app')

@section('content')
    <div class="container">
        @include('partials.alerts-bar')
        @include('partials.admin-nav-bar', ['view' => 'sections'])
        <div class="col-xs-5 sidenav m-t-lg m-b-lg">
            <span class="my-profile m-l-sm">{{uctrans('custom.sections')}}</span>
        </div>

        <div class="col-xs-12 m-t-lg text-right section">
            <div class="filter-content section-nav-bar">
                <ul class="nav filter-type right-border">
                    <li>
                        <a
                            class="active"
                            href="{{ url('/admin/sections/list') }}"
                        >{{ __('custom.topics_sections') }}</a>
                    </li>
                    <li>
                        <a
                        href="{{ url('/admin/pages/list') }}"
                        >{{ __('custom.topics_pages') }}</a>
                    </li>
                </ul>
            </div>
        </div>
        @include('partials.pagination')
        <div class="row">
            <div class="col-xs-12 p-l-lg m-b-lg text-left">{{ __('custom.order_by') }}</div>
            <div class="col-xs-12 p-l-lg order-documents">
                <a
                    href="{{
                        action(
                            'Admin\SectionController@list',
                            array_merge(
                                ['order' => 'asc'],
                                array_except(app('request')->input(), ['order'])
                            )
                        )
                    }}"

                    class="{{
                        isset(app('request')->input()['order'])
                        && app('request')->input()['order'] == 'asc'
                            ? 'active'
                            : ''
                    }}"
                >{{ uctrans('custom.order_asc') }}</a><a
                    href="{{
                        action(
                            'Admin\SectionController@list',
                            array_merge(
                                ['order' => 'desc'],
                                array_except(app('request')->input(), ['order'])
                            )
                        )
                    }}"

                    class="{{
                        isset(app('request')->input()['order'])
                        && app('request')->input()['order'] == 'desc'
                            ? 'active'
                            : ''
                    }}"
                >{{ uctrans('custom.order_desc') }}</a>
            </div>
        </div>
        <div class="row m-b-sm">
            <div class="col-xs-12 text-right">
                <span class="badge badge-pill long-badge">
                    <a href="{{ url('/admin/sections/add') }}">{{ __('custom.add') }}</a>
                </span>
            </div>
        </div>
        <div class="row m-b-lg">
            @if (count($sections))
                <form method="POST" class="form-horizontal">
                    {{ csrf_field() }}
                    <div class="col-xs-12 m-l-sm">
                        <div class="m-t-md">
                            <div class="table-responsive opn-tbl text-center">
                                <table class="table">
                                    <thead>
                                        <th>{{ utrans('custom.name') }}</th>
                                        <th>{{ utrans('custom.active') }}</th>
                                        <th>{{ utrans('custom.forum') }}</th>
                                        <th>{{ __('custom.action') }}</th>
                                    </thead>
                                    <tbody>
                                        @foreach ($sections as $section)
                                            <tr>
                                                <td class="name">{{ $section->name }}</td>
                                                <td>{{ $section->active ? __('custom.yes') : __('custom.no') }}</td>
                                                <td>{{ !is_null($section->forum_link) ? __('custom.yes') : __('custom.no') }}</td>
                                                <td class="buttons">
                                                    <a
                                                        class="link-action"
                                                        href="{{ url('/admin/sections/edit/'. $section->id) }}"
                                                    >{{ utrans('custom.edit') }}</a>
                                                    <a
                                                        class="link-action"
                                                        href="{{ url('/admin/sections/view/'. $section->id) }}"
                                                    >{{ utrans('custom.preview') }}</a>
                                                    <a
                                                        class="link-action red"
                                                        href="{{ url('/admin/sections/delete/'. $section->id) }}"
                                                        data-confirm="{{ __('custom.remove_data') }}"
                                                    >{{ __('custom.delete') }}</a>
                                                    <a
                                                        class="link-action"
                                                        href="{{ url('/admin/subsections/list/'. $section->id) }}"
                                                    >{{ uctrans('custom.subsections') }}</a>
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
