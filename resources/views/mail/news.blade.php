@extends('layouts.mail')

@section('title')
    <b>{{ __('custom.newsletter') }}</b>
@endsection

@section('content')
    {{ __('custom.greetings') }}, {{ $user }}!<br>
    <div>
        @if (isset($actions) && count($actions))
            @foreach ($actions as $action)
                <div style="width: 100%;">
                    <div>
                        <div>{{ __('custom.date') }}: {{ date('d.m.Y', strtotime($action->occurrence)) }}</div>
                        <h3 style="font-family: inherit; font-weight: 800; line-height: 1.1;">
                            <a
                                href="{{ isset($action->user_profile) ? $action->user_profile : '' }}"
                                style="color: black; text-decoration: none;"
                            >{{ isset($action->user_firstname) ? $action->user_firstname .' '. $action->user_lastname : '' }}</a>
                        </h3>
                        <p>
                            @if (isset($action->url) && isset($action->object) && isset($action->text))
                                {{ $action->text }}
                                <a
                                    href="{{ $action->url }}"
                                    style="text-decoration: none; color: black;"
                                >
                                    <b>{{ $action->object }}</b>
                                </a>
                            @endif
                            -
                            @if (isset($action->time))
                                {{ $action->time }}
                            @endif
                        </p>
                    </div>
                </div>
            @endforeach
        @else
            <div>
                {{ __('custom.no_info') }}
            </div>
        @endif
    </div>
@endsection
