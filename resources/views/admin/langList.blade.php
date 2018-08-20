@extends('layouts.app')

@section('content')
<div class="container admin">
    @include('partials.alerts-bar')
    @include('partials.admin-nav-bar', ['view' => 'languages'])
    <div class="row">
        <h3>{{ __('custom.language_list') }}</h3>
    </div>

    <div class="row">
        <form method="POST" class="form-horizontal">
            {{ csrf_field() }}
            <div class="row">
                <a class="btn btn-primary pull-right add" href="{{ url('/admin/languages/add') }}">{{ __('custom.add') }}</a>
            </div>
            <div class="row">
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
                                            onclick="return confirm('Изтриване на данните?');"
                                        >{{ __('custom.delete') }}</a>
                                        <a
                                            class="link-action"
                                            href="{{ url('/admin/languages/edit/'. $lang->locale)}}"
                                        >{{ utrans('custom.edit') }}</a>
                                        <a
                                            class="link-action"
                                            href="#"
                                        >{{ utrans('custom.export') }}</a>
                                        <a
                                            class="link-action"
                                            href="#"
                                        >{{ utrans('custom.import') }}</a>
                                        <a
                                            class="link-action"
                                            href="#"
                                        >{{ utrans('custom.preview') }}</a>
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
