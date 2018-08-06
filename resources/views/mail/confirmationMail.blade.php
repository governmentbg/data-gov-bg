@extends('layouts.elayout')
@section('content')

{{ __('custom.greetings') }}, {{ $user }}!
{{ __('custom.register_success') }}.
{{ __('custom.to_activate') }}:<br/>
<a href="{{ url('/confirmation?hash='. $hash)}}">{{ __('custom.confirm') }}</a>

@endsection
