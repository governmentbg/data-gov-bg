@extends('layouts.app')

@section('content')
    <div class="container">
        @include('partials.alerts-bar')
        @include('partials.admin-nav-bar', ['view' => 'images'])
        @include('partials.pagination')
        <div class="col-xs-12 sidenav m-t-lg m-b-lg">
            <span class="my-profile m-l-sm">{{ __('custom.images_list') }}</span>
        </div>
        <div class="row m-b-lg">
            <div class="col-xs-12 text-right">
                <span class="badge badge-pill doc-badge">
                    <a href="{{ url('/admin/images/add') }}">{{ __('custom.add') }}</a>
                </span>
            </div>
        </div>
        <div class="row m-b-lg">
            @if (count($images))
                <form method="POST" class="form-horizontal">
                    {{ csrf_field() }}
                    <div class="col-xs-12 m-l-sm">
                        <div class="m-t-md">
                            <div class="table-responsive opn-tbl text-center">
                                <table class="table">
                                    <thead>
                                        <th>{{ utrans('custom.name') }}</th>
                                        <th>{{ utrans('custom.type') }}</th>
                                        <th>{{ utrans('custom.size') }}</th>
                                        <th>{{ utrans('custom.width') }}</th>
                                        <th>{{ utrans('custom.height') }}</th>
                                        <th>{{ utrans('custom.active') }}</th>
                                        <th>{{ utrans('custom.action') }}</th>
                                    </thead>
                                    <tbody>
                                        @foreach ($images as $img)
                                            <tr>
                                                <td class="name">{{ $img->name }}</td>
                                                <td>{{ $img->mime_type }}</td>
                                                <td>{{ $img->size }}</td>
                                                <td>{{ $img->width }}</td>
                                                <td>{{ $img->height }}</td>
                                                <td>{{ $img->active ? utrans('custom.yes') : utrans('custom.no') }}</td>
                                                <td class="buttons">
                                                    <a
                                                        class="link-action"
                                                        href="{{ url('/admin/images/edit/'. $img->id) }}"
                                                    >{{ utrans('custom.edit') }}</a>
                                                    <a
                                                        class="link-action"
                                                        href="{{ url('/admin/images/view/'. $img->id) }}"
                                                    >{{ utrans('custom.preview') }}</a>
                                                    <a
                                                        class="link-action red"
                                                        href="{{ url('/admin/images/delete/'. $img->id) }}"
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
