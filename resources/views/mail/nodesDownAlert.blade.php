@extends('layouts.mail')

@section('title')

    <b>{{__('custom.cluster_nodes_down_alert')}}</b>

@endsection

@section('content')

    {{ __('custom.greetings') }},<br/><br/><br/>

    {{ __('custom.cluster_nodes_down')}}:
    @foreach($nodesDownIps as $nodeIp)
        <p><b>{{ $nodeIp }}</b></p>
    @endforeach

@endsection
