@extends('layouts.mail')
@section('content')

{{ __('custom.greetings') }}, {{ $user }}.
{{ __('custom.have_changed') }}.
{{ __('custom.to_confirm') }}: <br/>
<a href="{{ route('mailConfirmation', ['hash' => $hash, 'mail' => $mail]) }}"> {{ __('custom.confirm') }}</a>

@endsection
