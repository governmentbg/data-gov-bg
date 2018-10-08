@extends('layouts.app')

@section('content')
<div class="container p-l-r-none tool-history">
    @include('partials.alerts-bar')
    <div class="col-sm-4 sidenav col-xs-12 m-t-md history-filters">
        <form method="post" class="form-horisontal">
            {{ csrf_field() }}
            <div class="row form-group m-b-sm">
                <label class="col-sm-3 date title">{{ uctrans('custom.hour') }}:</label>
                <div class="col-sm-4 js-clockpicker p-l-none p-r-none">
                    <input
                        type="text"
                        class="form-control"
                        autocomplete="off"
                        name="time_from"
                        value="{{ $time['from'] }}"
                        placeholder="{{ uctrans('custom.from') }}"
                    >
                </div>
                <div class="col-sm-5 js-clockpicker">
                    <input
                        type="text"
                        class="form-control"
                        autocomplete="off"
                        name="time_to"
                        value="{{ $time['to'] }}"
                        placeholder="{{ uctrans('custom.to') }}"
                    >
                </div>
            </div>
            <div class="row form-group m-b-sm">
                <label class="col-sm-3 date title">{{ uctrans('custom.date') }}:</label>
                <div class="col-sm-4 p-l-none p-r-none">
                    <input
                        class="datepicker input-border-r-12 form-control"
                        name="period_from"
                        value="{{ $range['from'] }}"
                        placeholder="{{ uctrans('custom.from') }}"
                    >
                </div>
                <div class="col-sm-5">
                    <input
                        class="datepicker input-border-r-12 form-control"
                        name="period_to"
                        value="{{ $range['to'] }}"
                        placeholder="{{ uctrans('custom.to') }}"
                    >
                </div>
            </div>
            <div class="row form-group m-b-sm">
                <label class="col-xs-3 title">{{ uctrans('custom.connection') }}:</label>
                <div class="col-xs-9 p-l-none">
                    <select
                        class="js-select form-control"
                        name="db_type"
                    >
                        <option value="0"></option>
                        @foreach ($connectionTypes as $id => $dbType)
                            <option
                                value="{{ $id }}"
                                {{ !empty($post['db_type']) && $post['db_type'] == $id ? 'selected' : '' }}
                            >{{ $dbType }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="row form-group m-b-sm">
                <label class="col-xs-3 title">{{ uctrans('custom.query') }}:</label>
                <div class="col-xs-9 p-l-none">
                    <input type="text" name="q" class="input-border-r-12 form-control" value="{{ !empty($post['q']) ? $post['q'] : '' }}">
                </div>
            </div>
            <div class="row form-group">
                <label class="col-sm-2 col-xs-12 col-form-label m-b-sm title">{{ __('custom.type') }}:</label>
                @foreach ($modules as $i => $name)
                    <div class="col-sm-5 col-xs-6">
                        <label class="radio-label {{ $i != 0 ? 'pull-right last' : null }}">
                            <span>{{ uctrans('custom.'. $name) }}</span>
                            <div class="js-check">
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
            </div>
            <div class="row form-group">
                <label class="col-sm-2 col-xs-12 col-form-label m-b-sm">{{ utrans('custom.status') }}:</label>
                <div class="col-sm-10 col-xs-6">
                    <label class="radio-label succ-lbl">
                        {{ uctrans('custom.successfull') }}
                        <div class="js-check m-b-sm">
                            <input
                                class="text-right"
                                type="radio"
                                name="status"
                                value="1"
                                {{ !empty($post['status']) && $post['status'] == 1 ? 'checked' : ''}}
                            >
                        </div>
                    </label>
                    <label class="radio-label m-l-lg pull-right last">
                        {{ uctrans('custom.unsuccessfull') }}
                        <div class="js-check">
                            <input
                                class="text-right"
                                type="radio"
                                name="status"
                                value="0"
                                {{ isset($post['status']) && $post['status'] == 0 ? 'checked' : ''}}
                            >
                        </div>
                    </label>
                </div>
            </div>
            <div class="row form-group last">
                <input type="submit" class="btn btn-primary pull-right save-btn" value="{{ __('custom.search') }}">
            </div>
        </form>
    </div>
    <div class="col-sm-1 col-xs-12">
    </div>
    <div class="col-sm-7 col-xs-12 m-t-lg">
        @if (count($history))
            @foreach ($history as $record)
                <div class="row history-row">
                    <span>
                        {{
                            $actionTypes[$record->action] .' '. __('custom.'. $record->module) .' "'. $record->action_object .'" '
                            . sprintf(
                                __('custom.at_x_time_on_date'),
                                date('H:i', strtotime($record->occurrence)),
                                date('d.m.Y', strtotime($record->occurrence))
                            ) .' - '
                        }}
                        {{ $record->status ? __('custom.successfull') : __('custom.insuccessfull') }}
                    </span>
                </div>
            @endforeach
        @else
            <div class="col-sm-12 m-t-xl text-center no-info">
                {{ __('custom.no_info') }}
            </div>
        @endif
    </div>
</div>
@endsection
