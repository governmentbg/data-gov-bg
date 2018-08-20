@extends('layouts.app')

@section('content')
<div class="container admin">
    @include('partials.alerts-bar')
    @include('partials.admin-nav-bar', ['view' => 'termsConditions'])
    <h3>Условия за ползване</h3>

    <div class="row">
        <form method="POST" class="form-horizontal">
            {{ csrf_field() }}
            <div class="col-lg-12">
                <a class="btn btn-primary pull-right add" href="{{ url('/admin/terms-of-use/add') }}">{{ ultrans('custom.add') }}</a>
            </div>
            <div class="col-lg-12">
                <div class="table-responsive opn-tbl text-center">
                    <table class="table">
                        <thead>
                            <th>Наименование</th>
                            <th>{{ __('custom.activity') }}</th>
                            <th>{{ __('custom.by_default') }}</th>
                            <th>{{ __('custom.action') }}</th>
                        </thead>
                        <tbody>
                            @foreach ($terms as $term)
                                <tr>
                                    <td>{{ $term->name }}</td>
                                    <td>{{ $term->active ? __('custom.active') : __('custom.not_active') }}</td>
                                    <td>{{ $term->is_default ? 'Да' : 'Не' }}</td>
                                    <td class="buttons">
                                        <a
                                            class="link-action"
                                            href="{{ url('/admin/terms-of-use/delete/'. $term->id) }}"
                                            data-confirm="Изтриване на данните?"
                                        >{{ __('custom.delete') }}</a>
                                        <a
                                            class="link-action"
                                            href="{{ url('/admin/terms-of-use/edit/'. $term->id) }}"
                                        >{{ utrans('custom.edit') }}</a>
                                        <a
                                            class="link-action"
                                            href="{{ url('/admin/terms-of-use/view/'. $term->id) }}"
                                        >{{ utrans('custom.preview') }}</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
