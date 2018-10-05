@extends('layouts.app')

@section('content')
<div class="container admin">
    @include('partials.alerts-bar')
    @include('partials.admin-nav-bar', ['view' => 'help'])
    <h3>{{ uctrans('custom.help_sections') .' / '. uctrans('custom.pages') }}</h3>
    <div class="row">
        <div class="col-xs-12 m-t-lg m-b-md text-right section">
            <div class="filter-content section-nav-bar">
                <ul class="nav filter-type right-border">
                    <li>
                        <a
                            href="{{ url('/admin/help/sections/list') }}"
                        >{{ __('custom.topics_sections') }}</a>
                    </li>
                    <li>
                        <a
                            class="active"
                            href="{{ url('/admin/help/pages/list') }}"
                        >{{ __('custom.topics_pages') }}</a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-4 col-sm-12 pull-right m-b-md search-field">
            <form method="GET" action="{{ url('admin/help/pages/list') }}">
                <input
                    type="text"
                    class="m-t-md input-border-r-12 form-control js-ga-event"
                    placeholder="{{ __('custom.search') }}"
                    value="{{ isset($getParams['search']) ? $getParams['search'] : '' }}"
                    name="search"
                    data-ga-action="search"
                    data-ga-label="data search"
                    data-ga-category="data"
                >
            </form>
        </div>
    </div>
    <div class="row m-b-sm">
        <div class="col-xs-12 text-right">
            <span class="badge badge-pill long-badge">
                <a href="{{ url('/admin/help/pages/add') }}">{{ __('custom.add') }}</a>
            </span>
        </div>
    </div>
    <div class="row">
        <form method="POST" class="form-horizontal">
            @include('partials.pagination')
            {{ csrf_field() }}
            <div class="col-lg-12">
                @if (!empty($helpPages))
                    <div class="table-responsive opn-tbl text-center">
                        <table class="table">
                            <thead>
                                <th>{{ utrans('custom.name') }}</th>
                                <th>{{ uctrans('custom.unique_identificator') }}</th>
                                <th>{{ utrans('custom.section') }}</th>
                                <th>{{ utrans('custom.active') }}</th>
                                <th>{{ uctrans('custom.ordering') }}</th>
                                <th>{{ __('custom.action') }}</th>
                            </thead>
                            <tbody>
                                @foreach ($helpPages as $record)
                                    <tr>
                                        <td>{{ $record->title }}</td>
                                        <td>{{ $record->name }}</td>
                                        <td>{{ $record->section_name }}</td>
                                        <td>{{ $record->active ? __('custom.yes') : __('custom.no') }}</td>
                                        <td>{{ App\Category::getOrdering()[$record->ordering] }}</td>
                                        <td class="buttons">
                                            <a
                                                class="link-action"
                                                href="{{ url('admin/help/page/edit/'. $record->id) }}"
                                            >{{ utrans('custom.edit') }}</a>
                                            <a
                                                class="link-action"
                                                href="{{ url('admin/help/page/view/'. $record->id) }}"
                                            >{{ utrans('custom.preview') }}</a>
                                            <a
                                                class="link-action red"
                                                href="{{ url('/admin/help/page/delete/'. $record->id) }}"
                                                data-confirm="{{ __('custom.remove_data') }}"
                                            >{{ __('custom.delete') }}</a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="col-sm-12 m-t-xl text-center no-info">
                        {{ __('custom.no_info') }}
                    </div>
                @endif
            </div>
            @include('partials.pagination')
        </form>
    </div>
</div>
@endsection
