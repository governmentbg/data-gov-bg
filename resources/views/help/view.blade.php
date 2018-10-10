@extends('layouts.app')

@section('content')
    <div class="container help">
        <div class="row">
            <div class="row m-l-lg m-r-lg m-b-lg">
                <h2><img class="icon m-r-sm" src="{{ asset('/img/help_section.svg') }}">{{ utrans('custom.help_sections') }}</h2>
                <form method="GET" action="{{ url('help/search') }}">
                    <input
                        type="text"
                        class="input-border-r-12 form-control js-ga-event"
                        placeholder="{{ __('custom.search') }}"
                        value="{{ isset($search) ? $search : '' }}"
                        name="q"
                        data-ga-action="search"
                        data-ga-label="data search"
                        data-ga-category="data"
                    >
                </form>
            </div>
            <div class="row">
                <div class="result-cont">
                    <h2>{{ $section->title }}</h2>
                    <hr>
                    <div class="result-wrap">
                        <div class="nano">
                            <div class="nano-content">
                                @foreach ($subsections as $sub)
                                    <ul class="nav">
                                        <li class="js-show-submenu">
                                            <a
                                                href="#"
                                                class="clicable subsection"
                                            >{{ $sub->title }}&nbsp;&nbsp;<i class="fa fa-angle-down"></i></a>
                                            @foreach ($sub->pages as $page)
                                                <ul class="sidebar-submenu">
                                                    <li class="js-show-submenu">
                                                        <a href="#" class="clicable page">{{ $page->title }}&nbsp;&nbsp;<i class="fa fa-angle-down"></i></a>
                                                        <ul class="sidebar-submenu">
                                                            <li>
                                                                {!!  $page->body !!}
                                                            </li>
                                                        </ul>
                                                    </li>
                                                </ul>
                                            @endforeach
                                        </li>
                                    </ul>
                                @endforeach
                                @foreach ($pages as $page)
                                    @if ($page->section_id == $section->id)
                                        <ul class="nav">
                                            <li class="js-show-submenu">
                                                <a href="#" class="clicable">{{ $page->title }}&nbsp;&nbsp;<i class="fa fa-angle-down"></i></a>
                                                <ul class="sidebar-submenu">
                                                    <li>
                                                        {!!  $page->body !!}
                                                    </li>
                                                </ul>
                                            </li>
                                        </ul>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
