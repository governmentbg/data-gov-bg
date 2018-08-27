@extends('layouts.app')

@section('content')
<div class="container admin">
    @include('partials.alerts-bar')
    @include('partials.admin-nav-bar', ['view' => 'termsConditions'])
    <div class="col-xs-12 sidenav m-t-lg">
        <span class="my-profile m-l-sm">{{ __('custom.terms_and_conditions') }}</span>
    </div>

    <div class="row m-b-lg">
        <form method="POST" class="form-horizontal">
            {{ csrf_field() }}
            <div class="col-lg-12">
                <a class="btn btn-primary pull-right add" href="{{ url('/admin/terms-of-use/add') }}">{{ uctrans('custom.add') }}</a>
            </div>
            <div class="col-lg-12 m-l-sm">
                <div class="table-responsive opn-tbl text-center">
                    <table class="table">
                        <thead>
                            <th>{{ utrans('custom.name') }}</th>
                            <th>{{ __('custom.activity') }}</th>
                            <th>{{ __('custom.by_default') }}</th>
                            <th>{{ __('custom.action') }}</th>
                        </thead>
                        <tbody>
                            @foreach ($terms as $term)
                                <tr>
                                    <td class="name">{{ $term->name }}</td>
                                    <td>{{ $term->active ? __('custom.active') : __('custom.not_active') }}</td>
                                    <td>{{ $term->is_default ? utrans('custom.yes') : utrans('custom.no') }}</td>
                                    <td class="buttons">
                                        <a
                                            class="link-action"
                                            href="{{ url('/admin/terms-of-use/edit/'. $term->id) }}"
                                        >{{ utrans('custom.edit') }}</a>
                                        <a
                                            class="link-action"
                                            href="{{ url('/admin/terms-of-use/view/'. $term->id) }}"
                                        >{{ utrans('custom.preview') }}</a>
                                        <a
                                            class="link-action red"
                                            href="{{ url('/admin/terms-of-use/delete/'. $term->id) }}"
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
    @if (isset($pagination))
        <div class="row">
            <div class="col-xs-12 text-center">
                {{ $pagination->render() }}
            </div>
        </div>
    @endif
</div>
@endsection
