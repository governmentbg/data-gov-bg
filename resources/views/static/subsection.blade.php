@extends('layouts.app')

@section('content')
    <div class="container">
        @include('static.content.subsection-nav')
        @if (isset($subsection) && !empty($subsection->pages))
            @if (count($subsection->pages) == 1)
                @include('static.content.page-view', ['page' => $subsection->pages[0]])
            @else
                @include('static.content.pages-list', ['pages' => $subsection->pages])
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
