@extends('layouts.mail')
@section('content')

{{ __('custom.greeting_invite') }} {{ $user }}.
<br>{{ __('custom.please_follow_link_update') }}.
<br>{{ __('custom.password') }}:  {{ $pass }}
<br><a href="{{ route('preGenerated', ['username' => $username, 'pass' => $pass]) }}"> {{ __('custom.confirm') }}</a>

@endsection
