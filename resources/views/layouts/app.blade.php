<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Styles -->
    <link href="https://fonts.googleapis.com/css?family=Roboto&amp;subset=cyrillic,cyrillic-ext" rel="stylesheet">
    <link href="{{ asset('fonts/vendor/font-awesome/css/font-awesome.min.css') }}" rel="stylesheet">
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
</head>
<body>
    <div id="app">
        <nav class="navbar navbar-default navbar-static-top">
            <div class="container">
                <div class="navbar-header">
                    <div class="nav-logos col-sm-6">
                        <a href="#"><img src="img/open-data.png"></a>
                        <a href="#"><img src="img/euro-union.png"></a>
                        <a href="#"><img src="img/logo3.png"></a>
                        <a href="#"><img src="img/logo4.png"></a>
                        <a href="#"><img src="img/logo5.png"></a>
                    </div>
                    <div class="nav-controls col-sm-6 text-right">
                        <span class="search-input">
                            <input type="text" placeholder="Search..">
                        </span>
                        <span class="trans-link">
                            <a href="#">EN</a>
                        </span>
                        <span class="social-icons">
                            <a href="#" class="fb"><span class="fa fa-facebook"></span></a>
                            <a href="#" class="tw"><span class="fa fa-twitter"></span></a>
                            <a href="#" class="gp"><span class="fa fa-google-plus"></span></a>
                        </span>
                    </div>
                </div>
                <ul class="nav navbar-nav">
                    <li><a href="/">Начало</a></li>
                    <li class="active"><a href="/">Данни</a></li>
                    <li><a href="/">Организации</a></li>
                    <li><a href="/">Заявки за данни</a></li>
                    <li><a href="/">Визуализации</a></li>
                    <li><a href="/">Новини и събития</a></li>
                    <li><a href="/">Документи</a></li>
                    <li><a href="/">Контакти</a></li>
                </ul>
            </div>
            <div class="underline"></div>
        </nav>

        @yield('content')
    </div>

    <footer class="footer">
        <div class="copiright">
            <strong>Copyright &copy; 2018</strong> by
            <a target="_blank" href="https://opendata.government.bg/">Портал за отворени данни на Република България</a>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="{{ asset('js/app.js') }}"></script>
</body>
</html>
