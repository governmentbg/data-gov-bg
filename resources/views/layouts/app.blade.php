<!DOCTYPE html>
<?php
    $lang = App::getLocale();
    $altLang = $lang == 'bg' ? 'en' : 'bg';
?>
<html lang="{{ $lang }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @if (!empty($description))
        <meta name="description" content="{{ $description }}">
    @endif
    @if (!empty($keywords))
        <meta name="keywords" content="{{ $keywords }}">
    @endif
    <title>{{ !empty($title) ? $title : config('app.name') }}</title>
    <!-- Styles -->
    <link href="https://fonts.googleapis.com/css?family=Roboto&amp;subset=cyrillic,cyrillic-ext" rel="stylesheet">
    <link rel="stylesheet" href="/css/custom.css">
    <link href="{{ asset('fonts/vendor/font-awesome/css/font-awesome.min.css') }}" rel="stylesheet">
    <link href="{{ asset('css/select2.min.css') }}" rel="stylesheet">
    <link href="{{ asset('css/bootstrap-datepicker.min.css') }}" rel="stylesheet">
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <link href="{{ asset('css/nanoscroller.css') }}" rel="stylesheet">
    <link href="{{ asset('css/summernote/summernote.css') }}" rel="stylesheet">
    <link href="{{ asset('css/summernote/summernote-bs4.css') }}" rel="stylesheet">
    <link href="{{ asset('css/summernote/summernote.css') }}" rel="stylesheet">
    <link href="{{ asset('css/colorpicker.css') }}" rel="stylesheet">
    <link href="{{ asset('css/bootstrap-clockpicker.min.css') }}" rel="stylesheet">

    @if (isset($cssPaths))
        @foreach ($cssPaths as $path)
            <link href="{{ asset($path) }}" rel="stylesheet">
        @endforeach
    @endif

    @if (isset($link))
        <link rel="alternate" type="application/rss+xml" title="{{ $organisation->name }}" href="{{ url('/datasets/'. $organisation->uri .'/rss') }}"/>
    @endif
    @if (isset($datasetLink))
        <link rel="alternate" type="application/rss+xml" title="Datasets" href="{{ url('/datasets/rss') }}"/>
    @endif
    @if (isset($newsLink))
        <link rel="alternate" type="application/rss+xml" title="News" href="{{ url('/news/rss') }}"/>
    @endif
    @yield('css')
    <!-- Global site tag (gtag.js) - Google Analytics -->
    @if (!empty(config('app.GA_TRACKING_ID')))
        <script async src="{{ 'https://www.googletagmanager.com/gtag/js?id='. config('app.GA_TRACKING_ID') }}"></script>
        <script>
            window.dataLayer = window.dataLayer || [];
            function gtag(){ dataLayer.push(arguments); }
            gtag('js', new Date());

            gtag('config', '{{ config('app.GA_TRACKING_ID') }}');
        </script>
    @endif
    <!-- Google reCAPTCHA -->
    <script src="https://www.google.com/recaptcha/api.js?hl={{ $lang }}" async defer></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"
            integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4="
            crossorigin="anonymous"></script>
</head>
<body class="{{ isset($class) ? 'theme-'. $class : 'theme-user' }}">
    <div id="app" class="nano" data-lang="{{ $lang }}">
        <div class="nano-content js-nano-content">
            <nav class="navbar navbar-default navbar-static-top js-head">
                <div class="container">
                    <div class="navbar-header">
                        <div class="nav-logos col-xs-3 col-sm-2 col-md-1 col-lg-1 pull-left">
                            <a
                                href="{{ url('/') }}"
                            ><img alt="Лого на портала" src="{{ asset('img/opendata-logo-color.svg') }}"></a>
                        </div>
                        @if (!config('app.IS_TOOL'))
                            <div class="hamburger-trigger hidden-lg hidden-md hidden-sm col-xs-5 pull-right text-right">
                                @if (\Auth::check())
                                    <div class="mobile-icon {{ in_array(Request::segment(1), ['user', 'admin']) ? 'active-menu' : '' }}">
                                        <span>
                                            <a
                                                href="{{ url('/user') }}"
                                            >
                                                @if (\Auth::user()->is_admin)
                                                    <img class="mobile-img " src="{{ asset('img/admin.svg') }}">
                                                @else
                                                    <img class="mobile-img" src="{{ asset('img/user.svg') }}">
                                                @endif
                                            </a>
                                        </span>
                                    </div>
                                @else
                                    <span class="login-link">>
                                        <a href="{{ url('/login') }}">{{ __('custom.login') }}</a>
                                    </span>
                                @endif
                                <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#my-navbar">
                                    <span class="icon-bar"></span>
                                    <span class="icon-bar"></span>
                                    <span class="icon-bar"></span>
                                </button>
                            </div>
                        @endif
                        <div class="nav-name col-xs-12 hidden-sm {{ \Auth::check() ? 'col-md-4 col-lg-5' : 'col-md-5 col-lg-7' }}">
                            <b>{{ __('custom.od_portal_name') }}</b>
                            <br>
                            <p>{{ __('custom.od_portal_desc') }}</p>
                        </div>
                        <div class="
                            nav-controls
                            text-right
                            {{ config('app.IS_TOOL') ? null : 'hidden-xs' }}
                            js-show-on-load
                            {{ \Auth::check() ? 'col-sm-7 col-md-7 col-lg-6' : 'col-sm-6 col-md-5 col-lg-4'}}
                        ">
                            @if (!config('app.IS_TOOL'))
                                @if (\Auth::check())
                                    <span class="login-link username">
                                        <a href="{{ url('/user') }}">{{ \Auth::user()->username }}  </a>
                                    </span>
                                    <span class="user-icon {{ in_array(Request::segment(1), ['user', 'admin']) ? 'active' : '' }}">
                                        <a
                                            href="{{ url('/user') }}"
                                        >
                                            @if (\Auth::user()->is_admin)
                                                <img class="admin" src="{{ asset('img/admin.svg') }}">
                                            @else
                                                <img src="{{ asset('img/user.svg') }}">
                                            @endif
                                        </a>
                                    </span>
                                    <span class="login-link">
                                        <a
                                            href="{{ url('/logout') }}"
                                            class="js-ga-event"
                                            data-ga-action="logout"
                                            data-ga-label="logout attempt"
                                            data-ga-category="users"
                                        > {{ __('custom.logout') }}</a>
                                    </span>
                                @else
                                    <span class="login-link">>
                                        <a href="{{ url('/login') }}">{{ __('custom.login') }}</a>
                                    </span>
                                @endif
                                <span class="search-input">
                                    <form action="{{ action('DataController@list') }}" class="inline-block js-ga-event">
                                        <input
                                            type="text"
                                            name="q"
                                            placeholder="{{ __('custom.search') }}"
                                            data-ga-action="search"
                                            data-ga-label="data search"
                                            data-ga-category="data"
                                        >
                                    </form>
                                </span>
                            @endif

                            <span class="trans-link">
                                <a
                                    href="{{ route('lang.switch', $altLang) }}"
                                >{{ strtoupper($altLang) }}</a>
                            </span>

                            @if (!config('app.IS_TOOL'))
                                <span class="social-icons {{ isset($newsLink) || isset($datasetLink) || isset($link) ? 'rss-i' : '' }}">
                                    <a
                                        target="_blank"
                                        href="http://www.facebook.com/sharer.php?u={{ url()->current() }}"
                                        class="fb"
                                    ><span class="fa fa-facebook"></span></a>
                                    <a
                                        target="_blank"
                                        href="http://twitter.com/intent/tweet?text={{ url()->current() }}"
                                        class="tw"
                                    ><span class="fa fa-twitter"></span></a>
                                    <a
                                        target="_blank"
                                        href="https://www.linkedin.com/shareArticle?mini=true&url={{ url()->current() }}" class="in"
                                    ><span class="fa fa-linkedin"></span></a>
                                    @if (isset($newsLink))
                                        <a
                                            target="_blank"
                                            href="{{ url('/news/rss') }}" class="in"
                                        ><span class="fa fa-rss"></span></a>
                                    @endif
                                    @if (isset($datasetLink))
                                        <a
                                            target="_blank"
                                            href="{{ url('/datasets/rss') }}" class="in"
                                        ><span class="fa fa-rss"></span></a>
                                    @endif
                                    @if (isset($link))
                                        <a
                                            target="_blank"
                                            href="{{ url('/datasets/'. $organisation->uri .'/rss') }}" class="in"
                                        ><span class="fa fa-rss"></span></a>
                                    @endif
                                </span>
                            @endif
                        </div>
                        <div class="col-sm-12 hidden-xs hidden-md hidden-lg nav-name">
                            <b>{{ __('custom.od_portal_name') }}</b>
                            <br>
                            <p>{{ __('custom.od_portal_desc') }}</p>
                        </div>
                    </div>
                    <div class="collapse navbar-collapse" id="my-navbar">
                        <div class="hidden-lg hidden-md hidden-sm close-btn text-right">
                            <span><img class="js-close-navbar" src="{{ asset('img/close-btn.png') }}"></span>
                        </div>
                        <ul class="nav navbar-nav sections">
                            @if (config('app.IS_TOOL'))
                                <li class="index {{ empty(Request::segment(2)) || Request::segment(2) == 'configDbms' ? 'active' : '' }}">
                                    <a href="{{ url('/tool/configDbms') }}">{{ sprintf(__('custom.config'), __('custom.dbms')) }}</a>
                                </li>
                                <li class="index {{ Request::segment(2) == 'configFile' ? 'active' : '' }}">
                                    <a href="{{ url('/tool/configFile') }}">{{ sprintf(__('custom.config'), utrans('custom.file')) }}</a>
                                </li>
                                <li class="index {{ Request::segment(2) == 'chronology' ? 'active' : '' }}">
                                    <a href="{{ url('/tool/chronology') }}">{{ uctrans('custom.chronology') }}</a>
                                </li>
                            @else
                                <li class="index {{ Request::is('/') ? 'active' : '' }}">
                                    <a href="{{ url('/') }}">{{ uctrans('custom.home') }}</a>
                                </li>
                                <li class="data {{ Request::segment(1) == 'data' ? 'active' : '' }}">
                                    <a href="{{ url('/data') }}">{{ uctrans('custom.data') }}</a>
                                </li>
                                <li class="organisation {{ Request::segment(1) == 'organisation'  ? 'active' : '' }}">
                                    <a href="{{ url('/organisation') }}">{{ uctrans('custom.organisations', 2) }}</a>
                                </li>
                                <li class="request {{ Request::segment(1) == 'request' ? 'active' : '' }}">
                                    <a href="{{ url('/request') }}">{{ __('custom.data_requests') }}</a>
                                </li>
                                <li class="news {{ Request::segment(1) == 'news' ? 'active' : '' }}">
                                    <a href="{{ url('/news') }}">{{ __('custom.news_events') }}</a>
                                </li>
                                <li class="document {{ Request::segment(1) == 'document' ? 'active' : '' }}">
                                    <a href="{{ url('/document') }}">{{ __('custom.documents') }}</a>
                                </li>
                                @if (isset($activeSections))
                                    @foreach ($activeSections as $section)
                                        @if ($section->location == App\Section::LOCATION_MAIN_MENU)
                                            <li
                                                class="
                                                    {{
                                                        isset(app('request')->input()['section'])
                                                        && app('request')->input()['section'] == $section->id
                                                            ? 'active'
                                                            : ''
                                                    }}
                                                    {{ isset($section->class) ? $section->class : '' }}
                                                "
                                            >
                                                <a
                                                    href="{{
                                                        url(str_slug($section->name)) .
                                                        '?'.
                                                        http_build_query(['section' => $section->id])
                                                    }}"
                                                >{{ $section->name }}</a>
                                            </li>
                                        @endif
                                    @endforeach
                                @endif
                                <li
                                    class="hidden-lg hidden-md hidden-sm js-check-url {{ in_array(
                                        Request::segment(1),
                                        ['user', 'login', 'registration']
                                    ) ? 'active' : null }}"
                                >
                                    @if (!\Auth::check())
                                        <a href="{{ url('/login') }}">{{ uctrans('custom.login') }}</a>
                                    @else
                                        <a href="{{ url('/user') }}">{{ uctrans('custom.profile') }}</a>
                                    </li>
                                    <li class="hidden-lg hidden-md hidden-sm index">
                                        <a
                                            href="{{ url('/logout') }}"
                                            class="js-ga-event"
                                            data-ga-action="logout"
                                            data-ga-label="logout attempt"
                                            data-ga-category="users"
                                        >{{ uctrans('custom.logout') }}&nbsp;<i class="fa fa-sign-out"></i></a>
                                    @endif
                                </li>
                                <li class="hidden-lg hidden-md hidden-sm">
                                    <input
                                        type="text"
                                        placeholder="{{ __('custom.search') }}"
                                        class="form-control rounded-input input-long js-ga-event"
                                        data-ga-action="search"
                                        data-ga-label="data search"
                                        data-ga-category="data"
                                    >
                                </li>
                                <li class="hidden-lg hidden-md hidden-sm icons">
                                    <a
                                        href="{{ route('lang.switch', $altLang) }}"
                                    >{{ strtoupper($altLang) }}</a>
                                    <a href="http://www.facebook.com/sharer.php?u={{ url()->current() }}" class="fb"><i class="fa fa-facebook"></i></a>
                                    <a href="http://twitter.com/intent/tweet?text={{ url()->current() }}" class="tw"><i class="fa fa-twitter"></i></a>
                                    <a href="https://www.linkedin.com/shareArticle?mini=true&url={{ url()->current() }}" class="in"><i class="fa fa-linkedin"></i></a>
                                </li>
                            @endif
                        </ul>
                    </div>
                </div>
                <div class="underline">
                    <div class="container">
                        <div id="slideText" style="position: relative;">

                        </div>
                    </div>
                    <script type="text/javascript">
                        let keyValue = document.cookie.match('(^|;) ?slideText=([^;]*)(;|$)');
                        let value = keyValue ? keyValue[2] : null;
                        //console.log(value);
                        function SlideText() {
                            $('#slideText').animate({right: -200}, 2000);
                            $('#slideText').animate({left: -100}, 2000);
                            $('#slideText').animate({left: 0}, 2000);
                        }
                        function DeleteCoockie() {
                            document.cookie = "slideText=0;expires=Wed Apr 07 2020 18:11:49 GMT+0300";
                        }
                        $(function() {
                            $.get( "/msg", function( data ) {
                                //console.log(data);
                                if(data[0] == 1) {
                                    $("#slideText").html(data.msg);
                                }
                            });
                            if(value == null) {
                                SlideText();
                                let date = new Date();
                                date.setTime(date.getTime() + (30 * 1000));
                                console.log(date);
                                document.cookie = "slideText=1;expires="+date;
                            }
                            setInterval(function(){ SlideText() }, 12000);
                        });
                    </script>
                    @if (config('app.IS_TOOL'))
                       <div class="container">
                           <a
                                class="tool-version"
                                href="https://github.com/governmentbg/data-gov-bg/releases/tag/{{ exec('git describe') }}"
                            >{{ exec('git describe') }}</a>
                       </div>
                    @else
                        <div class="help-btn js-help">
                            @if (\Auth::check() && App\Role::isAdmin() && empty($help))
                                <img class="js-open-help help-icon" src="{{ asset('/img/help-icon.svg') }}">
                                <div class="js-help-bar help-container hidden">
                                    <div class="help-content">
                                    <img class="close-help close-btn" src="{{ asset('/img/X.svg') }}">
                                        <h3>{{ __('custom.no_help') }}</h3>
                                        <a
                                            class="btn-primary btn"
                                            href="{{
                                                route('addHelpPage', ['page' => config('app.APP_URL') == \Request::url()
                                                    ? 'home'
                                                    : \Request::getPathInfo()
                                                ])
                                            }}"
                                        >{{ __('custom.add') }}</a>
                                    </div>
                                </div>
                            @else
                                <img class="js-open-help help-icon" src="{{ asset('/img/help-icon.svg') }}">
                                @include('components.help', ['help' => !empty($help) ? $help : []])
                            @endif
                        </div>
                    @endif
                </div>
            </nav>

            <div class="js-content m-b-xl">
                @yield('content')
            </div>

            <footer>
                @if (!config('app.IS_TOOL'))
                    <div class="col-xs-12 m-t-xl p-t-sm text-center footer-sections">
                        <div class="row">
                            <div class="container">
                                @if (isset($activeSections))
                                    @foreach ($activeSections as $section)
                                        @if ($section->location == App\Section::LOCATION_FOOTER)
                                            <div
                                                class="
                                                    {{
                                                        isset(app('request')->input()['section'])
                                                        && app('request')->input()['section'] == $section->id
                                                            ? 's-active'
                                                            : ''
                                                    }}
                                                    {{ isset($section->class) ? $section->class : '' }}
                                                    js-footer-section
                                                    footer-section
                                                "
                                            >
                                                <a
                                                    class="section-link"
                                                    href="{{
                                                        url(str_slug($section->name)) .
                                                        '?'.
                                                        http_build_query(['section' => $section->id])
                                                    }}"
                                                >{{ $section->name }}</a><br><br>
                                                @if (isset($section->pages))
                                                    @foreach ($section->pages as $page)
                                                        <a
                                                            class="
                                                                {{
                                                                    isset(app('request')->input()['item'])
                                                                    && app('request')->input()['item'] == $page->id
                                                                        ? 'p-active'
                                                                        : ''
                                                                }}
                                                            "
                                                            href="{{
                                                                url(str_slug($section->name)) .
                                                                '?'.
                                                                http_build_query(['section' => $section->id, 'item' => $page->id])
                                                            }}"
                                                        >{{$page->title}}</a><br>
                                                    @endforeach
                                                @endif
                                            </div>
                                        @endif
                                    @endforeach
                                @endif
                            </div>
                        </div>
                    </div>
                @endif
                <div class="text-center col-xs-12 m-t-sm m-b-sm">
                    <div class="row">
                        <div class="container">
                            <div class="col-lg-5 col-md-5 col-sm-12 col-xs-12 text-left align-top m-t-md m-l-none img-wrapper">
                                <div class="col-xs-6 m-t-sm footer-img">
                                    <a href="https://europa.eu/european-union/index_bg" target="_blank">
                                        <img
                                            alt="Официална страница на Европейския съюз"
                                            src="{{ asset('img/euro-union.svg') }}"
                                        >
                                    </a>
                                </div>
                                <div class="col-xs-6 m-t-sm footer-img">
                                    <a class="m-l-r-md">
                                        <img
                                            alt="Добро управление"
                                            src="{{ asset('img/upravlenie-logo.svg') }}"
                                        >
                                    </a>
                                </div>
                            </div>
                            <div class="col-lg-7 col-md-7 col-sm-12 col-xs-12 footer-text">
                                <h6 class="text-justify">
                                     {{__('custom.footer_text')}}
                                </h6>
                            </div>
                        </div>
                    </div>
                </div>
            </footer>
        </div>
    </div>
    <div id="ajax_loader_backgr">&nbsp;</div>
    <div id="ajax_loader">
        <h2>Моля изчакайте</h2>
        <p class="text"></p>
        <div class="sk-cube-grid">
            <div class="sk-cube sk-cube1"></div>
            <div class="sk-cube sk-cube2"></div>
            <div class="sk-cube sk-cube3"></div>
            <div class="sk-cube sk-cube4"></div>
            <div class="sk-cube sk-cube5"></div>
            <div class="sk-cube sk-cube6"></div>
            <div class="sk-cube sk-cube7"></div>
            <div class="sk-cube sk-cube8"></div>
            <div class="sk-cube sk-cube9"></div>
        </div>
    </div>

    @include('partials.js-translations')
    <!-- Scripts -->
    @if (isset($jsPaths))
        @foreach ($jsPaths as $path)
            <script src="{{ asset($path) }}"></script>
        @endforeach
    @endif

    <script src="{{ asset('js/app.js') }}"></script>
    <script src="{{ asset('js/jquery.smartmenus.min.js') }}"></script>
    <script src="{{ asset('js/jquery.smartmenus.bootstrap.min.js') }}"></script>
    <script src="{{ asset('js/jquery.nanoscroller.min.js') }}"></script>
    <script src="{{ asset('js/bootstrap-colorpicker.js') }}"></script>
    <script src="{{ asset('js/bootstrap-clockpicker.min.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.9/summernote.js"></script>

    @if (isset($script))
        {!! $script !!}
    @endif

    @yield('js')
</body>
</html>
