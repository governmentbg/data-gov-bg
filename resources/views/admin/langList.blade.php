@extends('layouts.app')

@section('content')
<div class="container admin">
    @include('partials.alerts-bar')
    @include('partials.admin-nav-bar', ['view' => 'languages'])
    <h3>{{ __('custom.language_list') }}</h3>

    <div class="row">
        <form method="POST" class="form-horizontal">
            {{ csrf_field() }}
            <div class="col-lg-12">
                <a class="btn btn-primary pull-right add" href="{{ url('/admin/languages/add') }}">{{ ultrans('custom.add') }}</a>
            </div>
            <div class="col-lg-12">
                <div class="table-responsive opn-tbl text-center">
                    <table class="table">
                        <thead>
                            <th>{{ __('custom.language') }}</th>
                            <th>{{ __('custom.code') }}</th>
                            <th>{{ __('custom.activity') }}</th>
                            <th>{{ __('custom.action') }}</th>
                        </thead>
                        <tbody>
                            @foreach ($languages as $lang)
                                <tr>
                                    <td>{{ $lang->name }}</td>
                                    <td>{{ $lang->locale }}</td>
                                    <td>{{ $lang->active ? __('custom.active') : __('custom.not_active') }}</td>
                                    <td class="buttons">
                                        <a
                                            class="link-action"
                                            href="{{ url('/admin/languages/delete/'. $lang->locale) }}"
                                            data-confirm="Изтриване на данните?"
                                        >{{ __('custom.delete') }}</a>
                                        <a
                                            class="link-action"
                                            href="{{ url('/admin/languages/edit/'. $lang->locale) }}"
                                        >{{ utrans('custom.edit') }}</a>
                                        <button
                                            class="link-action"
                                            href="#"
                                        >{{ utrans('custom.preview') }}</button>
                                    </td>
                                </tr>
                                <input type="hidden" name="id" value="{{ $lang->locale }}">
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
