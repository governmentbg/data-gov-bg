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
                                        @if (isset($section) && !empty($section->subsections))
                                            @foreach ($section->subsections as $subsec)
                                                <li>
                                                    <a
                                                        href="{{ isset($subsec->base_url) ? $subsec->base_url : '' }}"
                                                        class="{{
                                                            isset(app('request')->input()['subsection'])
                                                            && app('request')->input()['subsection'] == $subsec->id
                                                                ? 'active'
                                                                : ''
                                                        }}"
                                                    >{{ $subsec->name }}</a>
                                                </li>
                                            @endforeach
                                        @endif
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @if (isset($section) && !empty($section->pages))
            @if (count($section->pages) == 1)
                @include('static.content.page-view', ['page' => $section->pages[0]])
            @else
                @include('static.content.pages-list', ['pages' => $section->pages])
            @endif
        @else
            @if (isset($discussion))
                <div class="row">
                    @include('vendor.chatter.discussion')
                </div>
            @endif
        @endif
    </div>
    @endsection
