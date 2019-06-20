@extends('layouts.app')

@section('content')
    <div class="container help">
        <div class="row">
            <div class="row m-l-lg m-r-lg">
                <h2><img class="icon m-r-sm" src="{{ asset('/img/help_section.svg') }}">{{ utrans('custom.help_sections') }}</h2>
                <form method="GET" action="{{ url('help/search') }}">
                    <input
                        type="text"
                        class="input-border-r-12 form-control js-ga-event m-t-lg"
                        placeholder="{{ __('custom.search') }}"
                        value="{{ isset($search) ? $search : '' }}"
                        name="q"
                        data-ga-action="search"
                        data-ga-label="data search"
                        data-ga-category="data"
                    >
                </form>
            </div>
            @if (!empty($sections))
                @include('partials.pagination')
                    <div class="row content">
                        @foreach ($sections as $section)
                            <div class="section-cont col-sm-4">
                                <h3>{{ $section->title }}</h3>
                                <hr>
                                <div class="link-wrap">
                                    <div class="nano">
                                        <div class="nano-content">
                                            @foreach ($subsections as $sub)
                                                @if ($sub->parent_id == $section->id)
                                                    <ul class="nav">
                                                        <li class="js-show-submenu">
                                                            <a
                                                                href="#"
                                                                class="clicable subsection-cont"
                                                            >{{ $sub->title }}&nbsp;&nbsp;<i class="fa fa-angle-down"></i></a>
                                                            @foreach ($pages as $page)
                                                                @if ($page->section_id == $sub->id)
                                                                    <ul class="sidebar-submenu">
                                                                        <li>
                                                                            <a href="{{ url('help/view/'. $section->id .'/'. $page->id) }}">{{ $page->title }}</a>
                                                                        </li>
                                                                    </ul>
                                                                @endif
                                                            @endforeach
                                                        </li>
                                                    </ul>
                                                @endif
                                            @endforeach
                                            @foreach ($pages as $page)
                                                @if ($page->active)
                                                    @if ($page->section_id == $section->id)
                                                        <ul class="nav">
                                                            <li>
                                                                <a href="{{ url('help/view/'. $section->id .'/'. $page->id) }}">{{ $page->title }}</a>
                                                            </li>
                                                        </ul>
                                                    @endif
                                                @endif
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @include('partials.pagination')
            @endif
        </div>
    </div>
@endsection
