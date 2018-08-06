@extends('layouts.elayout')
@section('content')

<h1>{{__('custom.reg_invite')}}</h1>
{{ __('custom.greeting_invite') }} {{ $user }}.
{{ __('custom.please_follow_link_register') }}<br/>
<a href="{{ url('/registration?mail='). $mail }}">{{ __('custom.confirm') }}</a>

@endsection
