@extends('layouts.mail')

@section('title')

<b>{{__('custom.signal_title')}}</b>

@endsection

@section('content')

@if(!isset($sendToAdmin))
{{ __('custom.greetings') }}, {{ $user }}!
<br>
<br>
{{ sprintf(__('custom.you_have_received_signal'), $resource_name) }}:
<br><a href="{{ route('dataView', ['uri' => $dataset_uri]) }}">{{ $dataset_name }}</a>
@else
{{ __('custom.greetings') }},
<br>
<br>
{{ sprintf(__('custom.you_have_received_signal_admin'), $resource_name, $user) }}:
<br><a href="{{ route('dataView', ['uri' => $dataset_uri]) }}">{{ $dataset_name }}</a>
@endif

@endsection
