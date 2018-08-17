@extends('layouts.app')

@section('content')
<div class="container role">
    @include('partials.alerts-bar')
    @include('partials.admin-nav-bar', ['view' => 'manageRoles'])
    <div class="row">
        <h3>{{ __('custom.role_list') }}</h3>
    </div>

    <div class="row">
        <form method="POST" class="form-horizontal">
            {{ csrf_field() }}
            <div class="row">
                <a class="btn btn-primary pull-right add" href="{{ url('/admin/roles/add') }}">{{ __('custom.add') }}</a>
            </div>
            <div class="row">
                <div class="table-responsive opn-tbl text-center">
                    <table class="table">
                        <thead>
                            <th>{{ __('custom.role_name') }}</th>
                        <th>{{ __('custom.activity') }}</th>
                            <th>{{ __('custom.by_default') }}</th>
                            <th>{{ __('custom.action') }}</th>
                        </thead>
                        <tbody>
                            @foreach ($roles as $role)
                            <tr>
                                <td>{{ $role->name }}</td>
                                <td>{{ $role->active ? __('custom.active') : __('custom.not_active') }}</td>
                                <td>не разбирам</td>
                                <td>
                                    <div class="inline-block">
                                        <button
                                            class="link-action"
                                            type="submit"
                                            name="delete"
                                            onclick="return confirm('Изтриване на данните?');"
                                            >{{ __('custom.delete') }}</button>
                                        <a
                                            class="link-action"
                                            href="{{ url('/admin/roles/edit/'. $role->id) }}"
                                        >{{ utrans('custom.edit') }}</a>
                                        <button
                                            class="link-action"
                                            href="#"
                                        >{{ utrans('custom.preview') }}</a>
                                    </div>
                                </td>
                            </tr>
                            <input type="hidden" name="id" value="{{ $role->id }}">
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
