@extends('layouts.app')

@section('content')
    <div class="container">
        @include('partials.alerts-bar')
        @include('partials.admin-nav-bar', ['view' => 'forum'])
        <div class="col-xs-12">
            <div class="row">
                <div class="col-xs-2 m-t-md p-l-r-none">
                    <span class="my-profile head">{{ utrans('custom.forum') }}</span>
                </div>
                <div class="col-xs-10 m-t-md m-b-md text-right section">
                    <div class="filter-content section-nav-bar">
                        <ul class="nav filter-type right-border">
                            <li>
                                <a
                                    href="{{ url('/admin/forum/discussions/list') }}"
                                >{{ __('custom.discussions') }}</a>
                            </li>
                            <li>
                                <a
                                    class="active"
                                    href="{{ url('/admin/forum/categories/list') }}"
                                >{{ __('custom.categories') }}</a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-xs-12 text-right">
                <span class="badge badge-pill long-badge">
                    <a href="{{ url('/admin/forum/categories/add') }}">{{ __('custom.add') }}</a>
                </span>
            </div>
        </div>
        <div class="row m-b-lg">
            @include('partials.pagination')
            @if (count($categories))
                <form method="POST" class="form-horizontal">
                    {{ csrf_field() }}
                    <div class="col-xs-12">
                        <div class="m-t-md">
                            <div class="table-responsive opn-tbl text-center">
                                <table class="table">
                                    <thead>
                                        <th>{{ utrans('custom.name') }}</th>
                                        <th>{{ utrans('custom.color') }}</th>
                                        <th>{{ utrans('custom.order') }}</th>
                                        <th>{{ __('custom.created_at') }}</th>
                                        <th>{{ __('custom.updated_at') }}</th>
                                        <th>{{ __('custom.action') }}</th>
                                    </thead>
                                    <tbody>
                                        @foreach ($categories as $category)
                                            <tr>
                                                <td class="name">{{ $category->name }}</td>
                                                <td class="name">{{ $category->color }}</td>
                                                <td class="name">{{ $category->order }}</td>
                                                <td class="name">{{ $category->created_at }}</td>
                                                <td class="name">{{ $category->updated_at }}</td>
                                                <td class="buttons">
                                                    <a
                                                        class="link-action"
                                                        href="{{ url('admin/forum/categories/edit/'. $category->id) }}"
                                                    >{{ utrans('custom.edit') }}</a>
                                                    <a
                                                        class="link-action"
                                                        href="{{ url('admin/forum/categories/view/'. $category->id) }}"
                                                    >{{ utrans('custom.preview') }}</a>
                                                    <a
                                                        class="link-action red"
                                                        data-confirm="{{ __('custom.remove_data') }}"
                                                        href="{{ url('admin/forum/categories/delete/'. $category->id) }}"
                                                    >{{ __('custom.delete') }}</a>
                                                    <a
                                                        class="link-action"
                                                        href="{{ url('/admin/forum/subcategories/list/'. $category->id) }}"
                                                    >{{ utrans('custom.subcategories') }}</a>
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
@endsection
