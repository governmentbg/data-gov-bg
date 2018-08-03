@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-xs-12">
            <div class="col-sm-offset-3 filter-content">
                <div class="col-md-12">
                    <div class="row">
                        <div class="col-xs-12 p-l-r-none">
                            <div>
                                <ul class="nav filter-type right-border">
                                    <li><a class="p-l-none" href="{{ url('/organisation/profile') }}">{{ __('custom.profile') }}</a></li>
                                    <li><a href="{{ url('/organisation/datasets') }}">{{ __('custom.data') }}</a></li>
                                    <li><a class="active" href="{{ url('/organisation/chronology') }}">{{ __('custom.chronology') }}</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-xs-12 p-sm chronology">
                    <div class="row">
                        <div class="col-xs-1 info-icon">
                            <img class="img-thumnail" src="{{ asset('img/test-img/dataset.png') }}"/>
                        </div>
                        <div class="col-xs-11 p-h-sm">
                            <div class="col-md-1 col-xs-2 logo-img">
                                <img class="img-responsive" src="{{ asset('img/test-img/logo-org-4.jpg') }}"/>
                            </div>
                            <div class="col-md-10 col-xs-10">
                                <p>
                                    Иван Иванов добави набор от данни "Списък на регистрираните представители за внос на фуранжи от трети страни" в {{ date('H:i', strtotime('12:00')) }} на
                                    {{ date('d.m.Y', strtotime('2018-05-23')) }}г-Успешно.
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-xs-1 info-icon">
                            <img class="img-thumnail" src="{{ asset('img/test-img/dataset.png') }}"/>
                        </div>
                        <div class="col-xs-11 p-h-sm">
                            <div class="col-md-1 col-xs-2 logo-img">
                                <img class="img-responsive" src="{{ asset('img/test-img/logo-org-4.jpg') }}"/>
                            </div>
                            <div class="col-md-10 col-xs-10">
                                <p>
                                    Иван Иванов обнови набор от данни "Списък на регистрираните представители за внос на фуранжи от трети страни" в {{ date('H:i', strtotime('12:00')) }} на
                                    {{ date('d.m.Y', strtotime('2018-05-23')) }}г-Успешно.
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-xs-1 info-icon">
                            <img class="img-thumnail" src="{{ asset('img/test-img/resource.png') }}"/>
                        </div>
                        <div class="col-xs-11 p-h-sm">
                            <div class="col-md-1 col-xs-2 logo-img">
                                <img class="img-responsive" src="{{ asset('img/test-img/logo-org-4.jpg') }}"/>
                            </div>
                            <div class="col-md-10 col-xs-10">
                                <p>
                                    Иван Иванов добави ресурс "Списък на регистрираните представители за внос на фуранжи от трети страни" в {{ date('H:i', strtotime('12:00')) }} на
                                    {{ date('d.m.Y', strtotime('2018-05-23')) }}г-Успешно.
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-xs-1 info-icon">
                            <img class="img-thumnail" src="{{ asset('img/test-img/resource.png') }}"/>
                        </div>
                        <div class="col-xs-11 p-h-sm">
                            <div class="col-md-1 col-xs-2 logo-img">
                                <img class="img-responsive" src="{{ asset('img/test-img/logo-org-4.jpg') }}"/>
                            </div>
                            <div class="col-md-10 col-xs-10">
                                <p>
                                    Иван Иванов обнови ресурс "Списък на регистрираните представители за внос на фуранжи от трети страни" в {{ date('H:i', strtotime('12:00')) }} на
                                    {{ date('d.m.Y', strtotime('2018-05-23')) }}г-Успешно.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
