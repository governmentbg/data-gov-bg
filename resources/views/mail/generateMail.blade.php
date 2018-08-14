@extends('layouts.mail')

@section('title')

<b>{{__('custom.reg_invite')}}</b>

@endsection

@section('content')

{{ __('custom.greeting_invite') }}
<br>{{__('custom.you_have_received')}} {{ $user }}.
<br>{{ __('custom.please_follow_link_update') }}.
<br>{{ __('custom.password') }}: {{ $pass }}
<br><a href="{{ route('preGenerated', ['username' => $username, 'pass' => $pass]) }}"> {{ __('custom.confirm') }}</a>

@endsection
