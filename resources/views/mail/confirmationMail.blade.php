@extends('layouts.mail')

@section('title')

<b>{{__('custom.register_confirmation')}}</b>

@endsection

@section('content')

{{ __('custom.greetings') }}, {{ $user }}!<br>
{{ __('custom.register_success') }}.<br>
{{ __('custom.to_activate') }}:<br>
@if (!empty($pass))
    {{ __('custom.password') }}: {{ $pass }}<br>
@endif
<a href="{{ route('confirmation', ['hash' => $hash, 'id' => $id]) }}"> {{ __('custom.confirm') }}</a>

@endsection
