@extends('layouts.app')

@section('content')
<form method="post" class="form-horisontal">
    {{ csrf_field() }}
    <div class="container p-l-r-none">
        @include('partials.alerts-bar')
        <div class="col-sm-5 sidenav col-xs-12 m-t-md">
            <div class="row m-b-sm">
                <div class="col-xs-3 p-l-lg from-to">{{ uctrans('custom.from') }}:</div>
                <div class="col-md-7 col-sm-8 text-left search-field admin">
                    <input class="datepicker input-border-r-12 form-control" name="period_from" value="{{ $range['from'] }}">
                </div>
            </div>
            <div class="row m-b-sm">
                <div class="col-xs-3 p-l-lg from-to">{{ uctrans('custom.to') }}:</div>
                <div class="col-md-7 col-sm-8 text-left search-field admin">
                    <input class="datepicker input-border-r-12 form-control" name="period_to" value="{{ $range['to'] }}">
                </div>
            </div>
            <div class="row m-b-sm">
                <label class="col-xs-3">{{ uctrans('custom.query') }} :</label>
                <div class="col-xs-7">
                    <input type="text" name="q" class="input-border-r-12 form-control" value="{{ !empty($post['q']) ? $post['q'] : '' }}">
                </div>
            </div>
            <div class="row">
                <label class="col-sm-2 col-xs-12 col-form-label m-b-sm">{{ __('custom.type') }}:</label>
                @foreach ($modules as $i => $name)
                    <div class="col-sm-4 col-xs-6 m-b-md">
                        <label class="radio-label {{ $i == 2 ? 'pull-right m-r-sm' : null }}">
                            {{ uctrans('custom.'. $name) }}
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
            <div class="row">
                <label class="col-sm-2 col-xs-12 col-form-label m-b-sm">{{ __('custom.status') }}:</label>
                <div class="col-sm-10 col-xs-6 m-b-md">
                    <label class="radio-label">
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
                    <label class="radio-label m-l-lg">
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
            <div class="row">
                <input type="submit" class="btn btn-primary" value="{{ __('custom.search') }}">
            </div>
        </div>
        <div class="col-sm-7 col-xs-12 m-t-lg">
            @if (count($history))
                @foreach ($history as $record)
                    <div class="row m-t-lg">
                        <span class="">
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
</form>
@endsection
