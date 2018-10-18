@extends('layouts.app')

@section('content')
<div class="container admin">
    @include('partials.alerts-bar')
    @include('partials.admin-nav-bar', ['view' => 'termsConditions'])
    <div class="col-xs-12 m-t-lg p-l-r-none">
        <span class="my-profile m-l-sm">{{ uctrans('custom.terms_and_conditions_list') }}</span>
    </div>
    @include('partials.pagination')
    <div class="row m-b-lg">
        <form method="POST" class="form-horizontal">
            {{ csrf_field() }}
            <div class="col-xs-12">
                <a
                    class="btn btn-primary {{ count($terms) ? 'pull-right' : 'pull-left m-l-sm' }} add"
                    href="{{ url('/admin/terms-of-use/add') }}"
                >{{ uctrans('custom.add') }}</a>
            </div>
            <div class="col-xs-12 m-l-sm">
                @if (count($terms))
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
                @else
                    <div class="col-sm-12 m-t-xl m-b-xl text-center no-info">
                        {{ __('custom.no_info') }}
                    </div>
                @endif
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
