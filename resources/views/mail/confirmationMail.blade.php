@extends('layouts.mail')

@section('title')

<b>{{__('custom.register_confirmation')}}</b>

@endsection

@section('content')

{{ __('custom.greetings') }}, {{ $user }}!<br>
{{ __('custom.register_success') }}.<br>
{{ __('custom.to_activate') }}:<br/>
<a href="{{ route('confirmation', ['hash' => $hash]) }}"> {{ __('custom.confirm') }}</a>

@endsection
