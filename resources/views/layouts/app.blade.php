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
    <title>{{ !empty($title) ? $title : config('app.name', 'Open Data Portal') }}</title>
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
    @if (!empty(env('GA_TRACKING_ID')))
        <script async src="{{ 'https://www.googletagmanager.com/gtag/js?id='. env('GA_TRACKING_ID') }}"></script>
        <script>
            window.dataLayer = window.dataLayer || [];
            function gtag(){dataLayer.push(arguments);}
            gtag('js', new Date());

            gtag('config', '{{ env('GA_TRACKING_ID') }}');
        </script>
    @endif
</head>
<body class="{{ isset($class) ? 'theme-'. $class : 'theme-user' }}">
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
                            <a href="#"><img alt="Добро управление" src="{{ asset('img/upravlenie-logo.svg') }}"></a>
                        </div>
                        @if (!env('IS_TOOL'))
                            <div class="hamburger-trigger hidden-lg hidden-md hidden-sm pull-right">
                                <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#my-navbar">
                                    <span class="icon-bar"></span>
                                    <span class="icon-bar"></span>
                                    <span class="icon-bar"></span>
                                </button>
                            </div>
                        @endif
                        <div class="nav-controls text-right {{ env('IS_TOOL') ? null : 'hidden-xs' }} js-show-on-load">
                            @if (!env('IS_TOOL'))
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

                            @if (!env('IS_TOOL'))
                                <span class="social-icons">
                                    <a
                                        target="_blank"
                                        href="http://www.facebook.com/sharer.php?u={{ url('/') }}"
                                        class="fb"
                                    ><span class="fa fa-facebook"></span></a>
                                    <a
                                        target="_blank"
                                        href="http://twitter.com/home?status={{ url('/') }}"
                                        class="tw"
                                    ><span class="fa fa-twitter"></span></a>
                                    <a
                                        target="_blank"
                                        href="https://plus.google.com/share?url={{ url('/') }}"
                                        class="gp"
                                    ><span class="fa fa-google-plus"></span></a>
                                    <a
                                        target="_blank"
                                        href="https://www.linkedin.com/shareArticle?mini=true&url={{ url('/') }}" class="in"
                                    ><span class="fa fa-linkedin"></span></a>
                                </span>
                            @endif

                        </div>
                    </div>
                    <div class="collapse navbar-collapse" id="my-navbar">
                        <div class="hidden-lg hidden-md hidden-sm close-btn text-right">
                            <span><img class="js-close-navbar" src="{{ asset('img/close-btn.png') }}"></span>
                        </div>
                        <ul class="nav navbar-nav sections">
                            @if (env('IS_TOOL'))
                                <li class="index {{ empty(Request::segment(2)) ? 'active' : '' }}">
                                    <a href="{{ url('/tool') }}">{{ uctrans('custom.config') }}</a>
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
                                <li class="help {{ Request::segment(1) == 'help' ? 'active' : '' }}">
                                    <a href="{{ url('/help') }}">{{ __('custom.help_sections') }}</a>
                                </li>
                                @if (isset($activeSections))
                                    @foreach ($activeSections as $section)
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
                                    <a href="#" class="fb"><i class="fa fa-facebook"></i></a>
                                    <a href="#" class="tw"><i class="fa fa-twitter"></i></a>
                                    <a href="#" class="gp"><i class="fa fa-google-plus"></i></a>
                                </li>
                            @endif
                        </ul>
                    </div>
                </div>
                <div class="underline">
                    <div class="help-btn js-help">
                        @if (!empty($help))
                            <img class="js-open-help help-icon" src="{{ asset('/img/help-icon.svg') }}">
                            @include('components.help', ['help' => $help])
                        @elseif (\Auth::check() && App\Role::isAdmin())
                            <img class="js-open-help help-icon" src="{{ asset('/img/help-icon.svg') }}">
                            <div class="js-help-bar help-container hidden">
                                <div class="help-content">
                                    <div class="close"><span class="close-btn">X</span></div>
                                    <h3>{{ __('custom.no_help') }}</h3>
                                    <a
                                        class="btn-primary btn"
                                        href="{{
                                            route('addHelpPage', ['page' => env('APP_URL') == \Request::url()
                                                ? 'home'
                                                : \Request::getPathInfo()
                                            ])
                                        }}"
                                    >{{ __('custom.add') }}</a>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </nav>

            <div class="js-content">
                @yield('content')
            </div>

            <footer class="footer js-footer hidden">
                <div class="image-links text-right col-xs-12">
                    <a href="{{ url('/terms') }}">
                        <span class="svg-icons">
                            <svg class="puzzle" id="Layer_1" data-name="Layer 1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 25.99 25.99">
                                <path d="M25.62.75l0,0L25.44.56l0,0a1.69,1.69,0,0,0-1-.38H1.67A1.66,1.66,0,0,0,0,1.8V24.48A1.61,1.61,0,0,0,.46,25.6a.3.3,0,0,0,.08.08,1.65,1.65,0,0,0,1.13.45H24.35A1.65,1.65,0,0,0,26,24.48V1.8A1.59,1.59,0,0,0,25.62.75ZM22.06,10.87a2.87,2.87,0,0,0-.89-2.08,2.84,2.84,0,0,0-2-.81h-.1a2.89,2.89,0,0,0-2.8,2.81,3,3,0,0,0,.47,1.66h-3v-4A.69.69,0,0,0,13,7.73a2.13,2.13,0,0,0-1.31.46,1.52,1.52,0,0,1-1,.31h0a1.51,1.51,0,0,1-1-2.57,1.52,1.52,0,0,1,2-.15A2.22,2.22,0,0,0,13,6.24a.68.68,0,0,0,.68-.69v-4H24.35a.28.28,0,0,1,.27.28V12.45h-3A2.85,2.85,0,0,0,22.06,10.87ZM12.32,4.56a2.89,2.89,0,1,0-1.66,5.32h.07a2.88,2.88,0,0,0,1.59-.47v3h-4a.69.69,0,0,0-.68.71,2.16,2.16,0,0,0,.46,1.32,1.52,1.52,0,0,1-1.15,2.44H6.84a1.49,1.49,0,0,1-1-.42,1.51,1.51,0,0,1-.47-1.09,1.45,1.45,0,0,1,.32-.92,2.22,2.22,0,0,0,.46-1.35.69.69,0,0,0-.69-.69h-4V1.8a.29.29,0,0,1,.28-.28H12.32ZM1.39,13.83h3A2.82,2.82,0,0,0,4,15.41a2.87,2.87,0,0,0,.88,2.08,2.9,2.9,0,0,0,2,.81H7a2.89,2.89,0,0,0,2.8-2.81,2.85,2.85,0,0,0-.47-1.66h3v4a.7.7,0,0,0,.71.69,2.11,2.11,0,0,0,1.32-.47,1.58,1.58,0,0,1,1-.31h0a1.53,1.53,0,0,1,1.47,1.47,1.49,1.49,0,0,1-.42,1.11,1.5,1.5,0,0,1-1.09.46,1.55,1.55,0,0,1-.93-.31A2.17,2.17,0,0,0,13,20a.69.69,0,0,0-.69.69v4H1.67a.29.29,0,0,1-.28-.28Zm12.3,7.89a2.9,2.9,0,0,0,3.67-.42,2.9,2.9,0,0,0-2-4.91h-.09a3,3,0,0,0-1.58.47v-3h4a.7.7,0,0,0,.69-.72A2.13,2.13,0,0,0,18,11.8a1.49,1.49,0,0,1-.32-1,1.54,1.54,0,0,1,1.47-1.48h.06a1.5,1.5,0,0,1,1,.43,1.55,1.55,0,0,1,.46,1.09,1.5,1.5,0,0,1-.31.92,2.17,2.17,0,0,0-.47,1.35.69.69,0,0,0,.69.69h4V24.48a.28.28,0,0,1-.27.28H13.69Z" transform="translate(-0.01 -0.14)"/>
                            </svg>
                        </span>
                    </a>
                    <a href="{{ url('/accessibility') }}">
                        <span class="svg-icons">
                            <svg id="Layer_1" data-name="Layer 1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 53 26">
                                <g>
                                    <path d="M16.73,20.73H7.49L5.73,26H.12L9.65.41h4.89L24.12,26H18.51ZM8.91,16.46h6.4L12.09,6.88Z"/>
                                    <path d="M36.31,22.19H29.63L28.36,26H24.31L31.19,7.52h3.53L41.64,26h-4Zm-5.65-3.08h4.62L33,12.19Z"/>
                                    <path d="M49.15,23.66H45L44.26,26H41.77L46,14.63h2.17L52.44,26h-2.5Zm-3.47-1.9h2.84L47.09,17.5Z"/>
                                </g>
                            </svg>
                        </span>
                    </a>
                </div>
                <div class="copiright text-center col-xs-12">
                    <div class="row">
                        <strong>Copyright &copy; 2018 </strong>
                        {{ __('custom.by') }}
                        <a target="_blank" href="http://data.egov.bg/"> {{ __('custom.copyright') }}</a>
                    </div>
                </div>
            </footer>
        </div>
    </div>

    @include('partials.js-translations')
    <!-- Scripts -->
    <script src="{{ asset('js/app.js') }}"></script>
    <script src="{{ asset('js/jquery.nanoscroller.min.js') }}"></script>
    <script src="{{ asset('js/bootstrap-colorpicker.js') }}"></script>
    <script src="{{ asset('js/bootstrap-clockpicker.min.js') }}"></script>
    @yield('js')
</body>
</html>
