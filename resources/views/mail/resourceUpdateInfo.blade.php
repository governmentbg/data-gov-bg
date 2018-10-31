@extends('layouts.mail')

@section('title')
    <b>{{ __('custom.resource_update_mail') }}</b>
@endsection

@section('content')
    {{ __('custom.greetings') }}, {{ $username }}!<br>
    <div>
        @if (!empty($post['connection_name']) && !empty($post['connection_query']))
            <div>
                {{ sprintf(__('custom.mail_conn_info'), $post['connection_name'], $post['connection_query']) }}
            </div>
            <br>
            <div>{{ $datetime }}</div>
            <br>
        @endif

        @if ($success)
            <div style="color: green;">{{ __('custom.changes_success_update') }}</div>
            <br>
            @if (!empty($info))
                <div style="color: orange;">{{ $info }}</div>
            @endif
        @else
            <div style="color: red;">{{ __('custom.update_resource_fail') }}</div>
            <br>
            @if (!empty($info))
                <div style="color: red;">{{ print_r($info, true) }}</div>
            @endif
        @endif
    </div>
@endsection
