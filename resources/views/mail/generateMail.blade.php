@extends('layouts.elayout')
@section('content')

{{ __('custom.greeting_invite') }} {{ $user }}.
<br>{{ __('custom.please_follow_link_update') }}.
<br>{{ __('custom.password') }}:  {{ $pass }}
<br><a href="{{url('/preGenerated?username='. $username .'&pass=' .$pass) }}">{{ __('custom.confirm') }}</a>

@endsection
