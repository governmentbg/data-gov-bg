@extends('layouts.app')

@section('content')
    <div class="container">
        @include('partials.alerts-bar')
        <div class="row">
            <div class="col-xs-12 p-h-sm p-l-r-none">
                <div class="filter-content">
                    <div class="col-md-12">
                        <div class="row">
                            <div class="col-md-12 text-center p-l-none">
                                <div>
                                    <ul class="nav filter-type right-border">
                                        @if (isset($page) && !empty($page->subsection))
                                            <li>
                                                <a
                                                    href="{{ isset($page->subsection->base_url)
                                                        ? $page->subsection->base_url
                                                        : ''
                                                    }}"
                                                >{{ $page->subsection->name }}</a>
                                            </li>
                                        @endif
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12 text-center p-l-none">
                                <div>
                                    <ul class="nav filter-type right-border">
                                        @if (isset($page))
                                            <li>
                                                <a href=""
                                                    class="{{
                                                        isset(app('request')->input()['item'])
                                                        && app('request')->input()['item'] == $page->id
                                                            ? 'active'
                                                            : ''
                                                    }}"
                                                >{{ $page->title }}</a>
                                            </li>
                                        @endif
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @if (isset($organisations))
            @include('partials.pagination')
        @endif

        @if (isset($page))
            @include('static.content.page-view', ['page' => $page])
        @endif

        @if (isset($organisations))
            @include('partials.pagination')
        @endif
    </div>
@endsection
