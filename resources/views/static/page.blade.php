@extends('layouts.app')

@section('content')
    <div class="container">
        @include('static.content.subsection-nav')
        @if (isset($pages))
            <div class="row static-pages m-b-lg m-t-md">
                <div class="col-xs-12 text-left section pages-list">
                    <div class="filter-content section-nav-bar">
                        <ul class="nav filter-type right-border {{ isset($class) ? $class : '' }}">
                            @foreach ($pages as $navPage)
                                <li>
                                    <a
                                        href="{{ isset($navPage->base_url) ? $navPage->base_url : '' }}"
                                        class="{{
                                            isset(app('request')->input()['item'])
                                            && app('request')->input()['item'] == $navPage->id
                                                ? 'active'
                                                : ''
                                        }}"
                                    >{{ $navPage->title }}</a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endif
        @if (isset($page))
            @include('static.content.page-view', ['page' => $page])
        @endif
    </div>
@endsection
