@extends('layouts.app')

@section('content')
<div class="container admin">
    @include('partials.alerts-bar')
    @include('partials.admin-nav-bar', ['view' => 'help'])
    <div class="col-xs-12 m-t-lg p-l-r-none">
        <span class="my-profile head">{{ uctrans('custom.help_sections') .' / '. uctrans('custom.sections') }}</span>
    </div>
    <div class="row">
        <div class="col-xs-12 m-b-md text-right section">
            <div class="filter-content section-nav-bar p-w-sm">
                <ul class="nav filter-type right-border">
                    <li>
                        <a
                            class="active"
                            href="{{ url('/admin/help/sections/list') }}"
                        >{{ __('custom.topics_sections') }}</a>
                    </li>
                    <li>
                        <a
                            href="{{ url('/admin/help/pages/list') }}"
                        >{{ __('custom.topics_pages') }}</a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
    <div class="row m-b-sm">
        <div class="col-xs-12 text-right">
            <span class="badge badge-pill long-badge">
                <a href="{{ url('/admin/help/section/add') }}">{{ __('custom.add') }}</a>
            </span>
        </div>
    </div>
    <div class="row">
        <form method="POST" class="form-horizontal m-t-md">
            @include('partials.pagination')
            {{ csrf_field() }}
            <div class="col-lg-12">
                @if (!empty($helpSections))
                    <div class="table-responsive opn-tbl text-center">
                        <table class="table">
                            <thead>
                                <th>{{ utrans('custom.section') }}</th>
                                <th>{{ utrans('custom.title') }}</th>
                                <th>{{ utrans('custom.active') }}</th>
                                <th>{{ uctrans('custom.ordering') }}</th>
                                <th>{{ __('custom.action') }}</th>
                            </thead>
                            <tbody>
                                @foreach ($helpSections as $record)
                                    <tr>
                                        <td>{{ $record->name }}</td>
                                        <td>{{ $record->title }}</td>
                                        <td>{{ $record->active ? __('custom.yes') : __('custom.no') }}</td>
                                        <td>{{  $record->ordering }}</td>
                                        <td class="buttons">
                                            <a
                                                class="link-action"
                                                href="{{ url('admin/help/section/edit/'. $record->id) }}"
                                            >{{ utrans('custom.edit') }}</a>
                                            <a
                                                class="link-action"
                                                href="{{ url('admin/help/section/view/'. $record->id) }}"
                                            >{{ utrans('custom.preview') }}</a>
                                            <a
                                                class="link-action red"
                                                href="{{ url('/admin/help/section/delete/'. $record->id) }}"
                                                data-confirm="{{ __('custom.delete_help_subsection') }}"
                                            >{{ __('custom.delete') }}</a>
                                            <a
                                                class="link-action"
                                                href="{{ url('/admin/help/subsections/list/'. $record->id) }}"
                                            >{{ utrans('custom.subsections') }}</a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="col-sm-12 m-t-md text-center no-info">
                        {{ __('custom.no_info') }}
                    </div>
                @endif
            </div>
            @include('partials.pagination')
        </form>
    </div>
</div>
@endsection
