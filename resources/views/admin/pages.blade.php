@extends('layouts.app')

@section('content')
    <div class="container">
        @include('partials.alerts-bar')
        @include('partials.admin-nav-bar', ['view' => 'pages'])
        <div class="row">
            <div class="col-xs-6 m-t-lg p-l-r-none">
                <span class="my-profile m-l-sm">{{ uctrans('custom.pages_list') }}</span>
            </div>
            <div class="col-xs-6 m-t-lg text-right section">
                <div class="filter-content section-nav-bar">
                    <ul class="nav filter-type right-border">
                        <li>
                            <a
                                href="{{ url('/admin/sections/list') }}"
                            >{{ uctrans('custom.topics_sections') }}</a>
                        </li>
                        <li>
                            <a
                                class="active"
                                href="{{ url('/admin/pages/list') }}"
                            >{{ __('custom.topics_pages') }}</a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="row">
            @include('partials.admin-sidebar', [
                'action' => 'Admin\PageController@list',
                'options' => ['active', 'section']
            ])
            <div class="col-sm-9 col-xs-12">
                @include('partials.pagination')
                <div class="row">
                    <div class="col-xs-12 text-right">
                        <a
                            class="btn btn-primary add pull-right"
                            href="{{ url('admin/pages/add') }}"
                        >{{ __('custom.add') }}</a>
                    </div>
                    <div class="col-xs-12 m-b-lg">
                        @if (count($pages))
                            <form method="POST" class="form-horizontal">
                                {{ csrf_field() }}
                                <div class="m-t-md">
                                    <div class="table-responsive opn-tbl text-center">
                                        <table class="table">
                                            <thead>
                                                <th>{{ utrans('custom.title') }}</th>
                                                <th>{{ __('custom.section') }}</th>
                                                <th>{{ utrans('custom.forum') }}</th>
                                                <th>{{ utrans('custom.active') }}</th>
                                                <th>{{ __('custom.valid_from') }}</th>
                                                <th>{{ __('custom.valid_to') }}</th>
                                                <th>{{ __('custom.action') }}</th>
                                            </thead>
                                            <tbody>
                                                @foreach ($pages as $page)

                                                    <tr>
                                                        <td class="name">{{ $page->title }}</td>
                                                        <td>
                                                            {{
                                                                count($sections) && isset($sections[$page->section_id])
                                                                    ? $sections[$page->section_id]
                                                                    : $page->section_id
                                                            }}
                                                        </td>
                                                        <td>
                                                            {{
                                                                !empty($page->forum_link)
                                                                    ? __('custom.yes')
                                                                    :  __('custom.no')
                                                            }}
                                                        </td>
                                                        <td>
                                                            {{
                                                                !empty($page->active)
                                                                    ? __('custom.yes')
                                                                    :  __('custom.no')
                                                            }}
                                                        </td>
                                                        <td>{{ $page->valid_from }}</td>
                                                        <td>{{ $page->valid_to }}</td>
                                                        <td class="buttons">
                                                            <a
                                                                class="link-action"
                                                                href="{{ url('admin/pages/edit/'. $page->id) }}"
                                                            >{{ utrans('custom.edit') }}</a>
                                                            <a
                                                                class="link-action"
                                                                href="{{ url('admin/pages/view/'. $page->id) }}"
                                                            >{{ utrans('custom.preview') }}</a>
                                                            <a
                                                                class="link-action red"
                                                                href="{{ url('admin/pages/delete/'. $page->id) }}"
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
                        @else
                            <div class="col-sm-12 m-t-md text-center no-info">
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
            </div>
        </div>
    </div>
@endsection
