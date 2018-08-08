@extends('layouts.mail')

@section('content')
{{ __('custom.hello') .', '. $user }}
<br/>{{ __('custom.reset_pass_info') }}
<br/>{{ __('custom.reset_pass_link_info') }}<br/>
<a href="{{ route('passReset', ['hash' => $hash, 'username' => $username]) }}">
    {{ url('/password/reset?hash='. $hash .'&username='. $username) }}
</a>
@endsection
