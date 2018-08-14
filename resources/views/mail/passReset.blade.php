@extends('layouts.mail')

@section('title')

<b>{{__('custom.pass_reset_confirm')}}</b>

@endsection

@section('content')
{{ __('custom.hello') .', '. $user }}
<br/>{{ __('custom.reset_pass_info') }}
<br/>{{ __('custom.reset_pass_link_info') }}<br/>
<a href="{{ route('passReset', ['hash' => $hash, 'username' => $username]) }}">
    {{ url('/password/reset?hash='. $hash .'&username='. $username) }}
</a>
@endsection
