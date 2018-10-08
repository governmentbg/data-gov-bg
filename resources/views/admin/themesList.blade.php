@extends('layouts.app')

@section('content')
    <div class="container">
        @include('partials.alerts-bar')
        @include('partials.admin-nav-bar', ['view' => 'topicsSubtopics'])
        <div class="col-xs-12 sidenav m-t-lg m-b-lg">
            <span class="my-profile m-l-sm">{{ __('custom.main_themes_list') }}</span>
        </div>
        <div class="m-b-md col-xs-12 m-t-lg text-right section">
            <div class="filter-content section-nav-bar">
                <ul class="nav filter-type right-border">
                    <li>
                        <a href="{{ url('admin/categories/list/') }}">
                            {{ __('custom.categories') }}
                        </a>
                    </li>
                </ul>
            </div>
        </div>
        <div class="row m-b-md">
            <div class="col-md-6 col-sm-8 col-xs-12 pull-right">
                <form method="GET" action="{{ url('/admin/themes/list') }}">
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
            <div class="col-xs-12 m-t-md text-right section">
                <span class="badge badge-pill long-badge">
                    <a href="{{ url('/admin/themes/add') }}">{{ __('custom.add') }}</a>
                </span>
            </div>
            @if (count($themes))
                <form method="POST" class="form-horizontal inline-block">
                    {{ csrf_field() }}
                    <div class="col-xs-12 m-l-sm">
                        <div class="m-t-md">
                            <div class="table-responsive opn-tbl text-center">
                                <table class="table">
                                    <thead>
                                        <th>{{ utrans('custom.name') }}</th>
                                        <th>{{ utrans('custom.active') }}</th>
                                        <th>{{ __('custom.action') }}</th>
                                    </thead>
                                    <tbody>
                                        @foreach ($themes as $theme)
                                            <tr>
                                                <td class="name">{{ $theme->name }}</td>
                                                <td>{{ $theme->active ? __('custom.yes') : __('custom.no') }}</td>
                                                <td class="buttons">
                                                    <a
                                                        class="link-action"
                                                        href="{{ url('admin/themes/edit/'. $theme->id) }}"
                                                    >{{ utrans('custom.edit') }}</a>
                                                    <a
                                                        class="link-action"
                                                        href="{{ url('admin/themes/view/'. $theme->id) }}"
                                                    >{{ utrans('custom.preview') }}</a>
                                                    <a
                                                        class="link-action red"
                                                        href="{{ url('admin/themes/delete/'. $theme->id) }}"
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
