@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-xs-12 p-h-sm p-l-r-none">
                <div class="filter-content">
                    <div class="col-md-12">
                        @if (isset($page) && !empty($page->subsection))
                            <div class="row">
                                <div class="col-md-12 text-center p-l-none">
                                    <div>
                                        <ul class="nav filter-type right-border">
                                            <li>
                                                <a
                                                    href="{{ isset($page->subsection->base_url)
                                                        ? $page->subsection->base_url
                                                        : ''
                                                    }}"
                                                >{{ $page->subsection->name }}</a>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        @endif
                        @if (isset($page))
                            <div class="row">
                                <div class="col-md-12 text-center p-l-none">
                                    <div>
                                        <ul class="nav filter-type right-border">
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
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        @endif
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
