@extends('layouts.app')

@section('content')
    <div class="container">
        @include('partials.alerts-bar')
        @include('partials.admin-nav-bar', ['view' => 'sections'])
        @include('partials.pagination')
        <div class="col-lg-6 col-sm-5 col-xs-2 m-t-lg">
            <span class="my-profile head elipsis">{{ isset($sectionName) ? $sectionName : '' }}</span>
        </div>
        <div class="col-lg-6 col-sm-7 col-xs-10 m-t-lg text-right section">
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
        <div class="col-xs-12 m-t-lg m-b-lg">
            <span class="my-profile head">{{ __('custom.subsections_list') }}</span>
        </div>
        <div class="row m-b-sm">
            <div class="col-xs-12 text-right">
                <span class="badge badge-pill long-badge">
                    <a href="{{ url('/admin/subsections/add/'. $sectionId) }}">{{ __('custom.add') }}</a>
                </span>
            </div>
        </div>
        <div class="row m-b-lg">
            @if (count($subsections))
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
                                        @foreach ($subsections as $section)
                                            <tr>
                                                <td class="name">{{ $section->name }}</td>
                                                <td>{{ $section->active ? __('custom.yes') : __('custom.no') }}</td>
                                                <td>{{ !is_null($section->forum_link) ? __('custom.yes') : __('custom.no') }}</td>
                                                <td class="buttons">
                                                    <a
                                                        class="link-action"
                                                        href="{{ url('/admin/subsections/edit/'. $section->id) }}"
                                                    >{{ utrans('custom.edit') }}</a>
                                                    <a
                                                        class="link-action"
                                                        href="{{ url('/admin/subsections/view/'. $section->id) }}"
                                                    >{{ utrans('custom.preview') }}</a>
                                                    <a
                                                        class="link-action red"
                                                        href="{{ url('/admin/subsections/delete/'. $section->id) }}"
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
