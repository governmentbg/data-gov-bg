@extends('layouts.mail')

@section('title')

<b>{{__('custom.reg_invite')}}</b>

@endsection

@section('content')
{{ __('custom.greeting_invite') }} {{ $user }}.<br/>
{{ __('custom.please_follow_link_register') }}<br/>

<a href="{{ route('registration', ['mail' => $mail]) }}"> {{ __('custom.confirm') }}</a>

@endsection
