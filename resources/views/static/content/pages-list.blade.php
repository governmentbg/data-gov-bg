@if (isset($pages))
    @include('partials.pagination')
    <div class="row m-t-md">
        @foreach ($pages as $page)
            <div class="col-xs-4 text-center pages-list">
                <div class="col-xs-11 text-center page-item {{ isset($class) ? $class : '' }}">
                    <a
                        href="{{ isset($page->base_url) ? $page->base_url : '' }}"
                    ><div class="elipsis-1">{{ $page->title }}</div></a>
                </div>
            </div>
        @endforeach
    </div>
    @include('partials.pagination')
    @if (isset($discussion))
        <div class="row">
            @include('vendor.chatter.discussion')
        </div>
    @endif
@endif
