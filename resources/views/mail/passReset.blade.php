@extends('layouts.elayout')

@section('content')
{{ __('custom.hello') .', '. $user }}
<br/>{{ __('custom.reset_pass_info') }}
<br/>{{ __('custom.reset_pass_link_info') }}<br/>
<a href="{{ url('/password/reset?hash='. $hash .'&username='. $username) }}">
{{ url('/password/reset?hash='. $hash .'&username='. $username) }}
</a>
@endsection
