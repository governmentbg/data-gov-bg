@extends('layouts.mail')

@section('title')

<b>{{__('custom.signal_title')}}</b>

@endsection

@section('content')

{{ __('custom.greetings') }}, {{ $user }}!
<br>
{{ sprintf(__('custom.you_have_received_signal'), $resource_name) }}:
<br><a href="{{ route('dataView', ['uri' => $dataset_uri]) }}">{{ $dataset_name }}</a>

@endsection
