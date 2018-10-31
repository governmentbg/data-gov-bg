@extends('layouts.app')

@section('content')
    <div class="container">
        @include('partials.alerts-bar')
        @include('partials.admin-nav-bar', ['view' => 'topicsSubtopics'])
        <div class="row">
            <div class="col-xs-6 sidenav m-t-lg">
                <span class="my-profile m-l-sm">{{ __('custom.categories_list') }}</span>
            </div>
            <div class="col-xs-6 m-t-lg text-right section">
                <div class="filter-content section-nav-bar">
                    <ul class="nav filter-type right-border">
                        <li>
                            <a
                                class="active"
                                href="{{ url('admin/categories/list/') }}"
                            > {{ __('custom.categories') }} </a>
                        </li>
                        <li>
                            <a
                                href="{{ url('/admin/themes/list') }}"
                            >{{ __('custom.main_themes') }}</a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        @include('partials.pagination')
        <div class="col-xs-12 p-l-r-none">
            <div class="col-md-6 col-sm-8 col-xs-12 p-l-none">
                <form method="GET" action="{{ url('admin/categories/search') }}">
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
            <div class="col-md-4"></div>
            <div class="col-md-2 col-sm-4 col-xs-12 p-r-none">
                <a
                    class="btn btn-primary add pull-right"
                    href="{{ url('admin/categories/add') }}"
                >{{ __('custom.add') }}</a>
            </div>
        </div>
        @if (count($themes))
            <div class="row m-b-lg">
                <form method="POST" class="form-horizontal">
                    {{ csrf_field() }}
                    <div class="col-xs-12 m-t-md">
                        <div class="table-responsive opn-tbl text-center">
                            <table class="table">
                                <thead>
                                    <th>{{ utrans('custom.name') }}</th>
                                    <th>{{ __('custom.action') }}</th>
                                </thead>
                                <tbody>
                                    @foreach ($themes as $theme)
                                        <tr>
                                            <td class="name">{{ $theme->name }}</td>
                                            <td class="buttons">
                                                <a
                                                    class="link-action"
                                                    href="{{ url('admin/categories/edit/'. $theme->id) }}"
                                                >{{ utrans('custom.edit') }}</a>
                                                <a
                                                    class="link-action"
                                                    href="{{ url('admin/categories/view/'. $theme->id) }}"
                                                >{{ utrans('custom.preview') }}</a>
                                                <a
                                                    class="link-action red"
                                                    href="{{ url('admin/categories/delete/'. $theme->id) }}"
                                                    data-confirm="{{ __('custom.remove_data') }}"
                                                >{{ __('custom.delete') }}</a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </form>
            </div>
        @else
            <div class="col-xs-12 m-t-md text-center no-info">
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
@endsection
