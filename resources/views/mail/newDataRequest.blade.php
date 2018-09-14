@extends('layouts.mail')

@section('title')

<b>{{__('custom.new_data_request')}}</b>

@endsection

@section('content')
{{ __('custom.greetings') }},<br/>
{{ __('custom.your_org')}}: <br/>
<b>{{ __('custom.description')}}</b>: {{$description}} <br/>
<b>{{ __('custom.published_url')}}</b>: {{isset($published_url) ? $published_url : __('custom.no_info_provided')}} <br/>
<b>{{ uctrans('custom.from')}}</b>: {{isset($contact_name) ? $contact_name : __('custom.no_contact_provided')}} <br/>
<b>{{ uctrans('custom.notes')}}</b>: {{isset($notes) ? $notes : __('custom.no_info_provided')}} <br/>

<b>{{ __('custom.contact_email') }}</b> : {{ ($email=='') ? __('custom.no_email_provided') : $email}}<br/>

@endsection
