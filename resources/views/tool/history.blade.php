@extends('layouts.app')

@section('content')
<div class="container tool-history p-l-r-none">
    @include('partials.alerts-bar')
    @include('partials.pagination')
    <div class="col-lg-4 col-md-5 col-sm-12 history-filters">
        <form method="GET" class="form-horisontal">
            <table>
                <tr>
                    <td>{{ uctrans('custom.time') }}:</td>
                    <td>
                        <div class="col-xs-6 js-clockpicker">
                            <input
                                type="text"
                                class="form-control"
                                autocomplete="off"
                                name="time_from"
                                value="{{ $time['from'] }}"
                                placeholder="{{ uctrans('custom.from') }}"
                            >
                        </div>
                        <div class="col-xs-6 js-clockpicker">
                            <input
                                type="text"
                                class="form-control"
                                autocomplete="off"
                                name="time_to"
                                value="{{ $time['to'] }}"
                                placeholder="{{ uctrans('custom.to') }}"
                            >
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>{{ uctrans('custom.date') }}:</td>
                    <td>
                        <div class="col-xs-6">
                            <input
                                class="datepicker input-border-r-12 form-control"
                                name="period_from"
                                value="{{ $range['from'] }}"
                                placeholder="{{ uctrans('custom.from') }}"
                            >
                        </div>
                        <div class="col-xs-6">
                            <input
                                class="datepicker input-border-r-12 form-control"
                                name="period_to"
                                value="{{ $range['to'] }}"
                                placeholder="{{ uctrans('custom.to') }}"
                            >
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>{{ uctrans('custom.connection') }}:</td>
                    <td>
                        <div class="col-xs-12">
                            <select
                                class="js-select form-control"
                                name="db_type"
                            >
                                <option value=""></option>
                                @foreach ($connectionTypes as $id => $dbType)
                                    <option
                                        value="{{ $id }}"
                                        {{ !empty($post['db_type']) && $post['db_type'] == $id ? 'selected' : '' }}
                                    >{{ $dbType }}</option>
                                @endforeach
                            </select>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>{{ uctrans('custom.query') }}:</td>
                    <td>
                        <div class="col-xs-12">
                            <input
                                type="text"
                                name="q"
                                class="input-border-r-12 form-control"
                                value="{{ !empty($post['q']) ? $post['q'] : '' }}"
                            >
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>{{ __('custom.type') }}:</td>
                    <td>
                        @foreach ($modules as $i => $name)
                            <div class="col-xs-6">
                                <label class="radio-label">
                                    <span>{{ uctrans('custom.'. $name) }}</span>
                                    <div class="js-check pull-right">
                                        <input
                                            type="radio"
                                            name="source_type"
                                            value="{{ $name }}"
                                            @if (!empty(old('source_type')) && old('source_type') == $name)
                                                {{ 'checked' }}
                                            @elseif (!empty($post['source_type']) && $post['source_type'] == $name)
                                                {{ 'checked' }}
                                            @elseif (empty(old('source_type')) && empty($post['source_type']) && $name == 'dbms')
                                                {{ 'checked' }}
                                            @endif
                                        >
                                    </div>
                                </label>
                            </div>
                        @endforeach
                    </td>
                </tr>
                <tr>
                    <td>{{ utrans('custom.status') }}:</td>
                    <td>
                        <div class="col-xs-6">
                            <label class="radio-label">
                                {{ uctrans('custom.success') }}
                                <div class="js-check pull-right">
                                    <input
                                        class="text-right"
                                        type="radio"
                                        name="status"
                                        value="1"
                                        {{ !empty($post['status']) && $post['status'] == 1 ? 'checked' : '' }}
                                    >
                                </div>
                            </label>
                        </div>
                        <div class="col-xs-6">
                            <label class="radio-label">
                                {{ uctrans('custom.failure') }}
                                <div class="js-check pull-right">
                                    <input
                                        class="text-right"
                                        type="radio"
                                        name="status"
                                        value="0"
                                        {{ isset($post['status']) && $post['status'] == 0 ? 'checked' : '' }}
                                    >
                                </div>
                            </label>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td></td>
                    <td>
                        <input
                            type="submit"
                            class="btn btn-primary pull-right save-btn"
                            value="{{ __('custom.search_button') }}"
                        >
                    </td>
                </tr>
            </table>
        </form>
    </div>
    <div class="col-lg-8 col-md-7 col-sm-12 m-b-lg">
        @if (count($history))
            @foreach ($history as $record)
                <div class="history-row">
                    <div>
                        {{
                            mb_ucfirst($actionTypes[$record->action]) .' '. __('custom.'. $record->module) .' "'. $record->action_object .'" '
                            . sprintf(
                                __('custom.at_x_time_on_date'),
                                date('H:i', strtotime($record->occurrence)),
                                date('d.m.Y', strtotime($record->occurrence))
                            )
                        }}
                    </div>
                    <div>{{ $record->action_msg }}</div>
                    <div class="{{ $record->status ? 'success' : 'error' }}">
                        {{ $record->status ? __('custom.success') : __('custom.failure') }}
                    </div>
                </div>
            @endforeach
        @else
            <div class="col-sm-12 m-t-md text-center no-info">
                {{ __('custom.no_info') }}
            </div>
        @endif
    </div>
    @include('partials.pagination')
</div>
@endsection
