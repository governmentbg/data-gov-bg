@extends('layouts.app')

@section('content')
    <div class="container">
        @if (isset($section) && !empty($section->subsections))
            @include('static.content.subsection-nav', ['subsections' => $section->subsections])
        @endif
        @if (isset($section) && !empty($section->pages))
            @if (count($section->pages) == 1)
                @include('static.content.page-view', ['page' => $section->pages[0]])
            @else
                @include('static.content.pages-list', ['pages' => $section->pages])
            @endif
        @else
            @if (isset($discussion))
                <div class="row discussion">
                    @include('vendor.chatter.discussion')
                </div>
            @endif
        @endif
    </div>
@endsection
