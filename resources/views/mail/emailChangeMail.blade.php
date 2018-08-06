@extends('layouts.elayout')
@section('content')

{{ __('custom.greetings') }}, {{ $user }}.
{{ __('custom.have_changed') }}.
{{ __('custom.to_confirm') }}: <br/>
<a href="{{url('/mailConfirmation?hash='. $hash .'&mail='. $mail) }}">{{ __('custom.confirm') }}</a>

@endsection
