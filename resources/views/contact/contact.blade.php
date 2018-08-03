@extends('layouts.app')

@section('content')
<div class="container">
    <div class="col-xs-12 p-lg">
        <div>
            <p class="request-data">
            {!! __('custom.contact_info') !!}
            </p>
            <span>{{ __('custom.contact_person')}}</span></br></br>
            <span>Нуша Иванова</span></br>
            <span>дирекция &#8222;Модернизация на администрацията&#8220;</span></br>
            <span>в Администрацията на Министерски съвет</span></br></br>
            <span>тел. 02/940 2445</span></br>
            <span>e-mail: ivanova@bgpost.org</span></br></br>
        </div>
    </div>
</div>
@endsection
