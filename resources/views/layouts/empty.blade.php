<!DOCTYPE html>
<?php
    $lang = App::getLocale();
?>
<html lang="{{ $lang }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name') }}</title>
    <!-- Styles -->
    <link href="https://fonts.googleapis.com/css?family=Roboto&amp;subset=cyrillic,cyrillic-ext" rel="stylesheet">
    <link rel="stylesheet" href="/css/custom.css">
    <link href="{{ asset('fonts/vendor/font-awesome/css/font-awesome.min.css') }}" rel="stylesheet">
    <link href="{{ asset('css/select2.min.css') }}" rel="stylesheet">
    <link href="{{ asset('css/bootstrap-datepicker.min.css') }}" rel="stylesheet">
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <link href="{{ asset('css/nanoscroller.css') }}" rel="stylesheet">
</head>
<body class="theme-{{ $class }}">
    <div id="app" class="nano" data-lang="{{ $lang }}">
        <div class="nano-content">
            <nav class="navbar navbar-default navbar-static-top js-head">
                <div class="container">
                    <div class="navbar-header">
                        <div class="nav-logos">
                            <a
                                href="{{ url('/') }}"
                            ><img alt="Лого на портала" src="{{ asset('img/opendata-logo-color.svg') }}"></a>
                            <a href="https://europa.eu/european-union/index_bg" target="_blank">
                                <img
                                    alt="Официална страница на Европейския съюз"
                                    src="{{ asset('img/euro-union.svg') }}"
                                >
                            </a>
                            <a href="{{ url('/') }}"><img alt="Добро управление" src="{{ asset('img/upravlenie-logo.svg') }}"></a>
                        </div>
                    </div>
                </div>
                <div class="underline"></div>
            </nav>
            <div class="js-content">
                @yield('content')
            </div>
        </div>
    </div>
    <!-- Scripts -->
    <script src="{{ asset('js/app.js') }}"></script>
    <script src="{{ asset('js/jquery.nanoscroller.min.js') }}"></script>
</body>
</html>
