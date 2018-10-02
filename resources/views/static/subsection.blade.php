@extends('layouts.app')

@section('content')
    <div class="container">
        @include('partials.alerts-bar')
        <div class="row">
            <div class="col-xs-12 p-h-sm p-l-r-none">
                <div class="filter-content">
                    <div class="col-md-12">
                        <div class="row">
                            <div class="col-md-12 text-center p-l-r-none">
                                <div>
                                    <ul class="nav filter-type right-border">
                                        @if (isset($subsection))
                                            <li>
                                                <a
                                                    href="{{ isset($subsection->base_url) ? $subsection->base_url : '' }}"

                                                    class="{{
                                                        isset(app('request')->input()['subsection'])
                                                        && app('request')->input()['subsection'] == $subsection->id
                                                            ? 'active'
                                                            : ''
                                                    }}"
                                                >{{ $subsection->name }}</a>
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
        @if (isset($subsection) && !empty($subsection->pages))
            @if (count($subsection->pages) == 1)
                @include('static.content.page-view', ['page' => $subsection->pages[0]])
            @else
                @include('static.content.pages-list', ['pages' => $subsection->pages])
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
