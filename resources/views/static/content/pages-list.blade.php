@if (isset($pages))
    @include('partials.pagination')
    <div class="col-xs-12 text-left section pages-list static-pages m-t-md">
        <div class="filter-content section-nav-bar">
            <ul class="nav filter-type right-border {{ isset($class) ? $class : '' }}">
                @foreach ($pages as $page)
                    <li>
                        <a
                            href="{{ isset($page->base_url) ? $page->base_url : '' }}"
                        >{{ $page->title }}</a>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
    @include('partials.pagination')
    @if (isset($discussion))
        <div class="row discussion">
            @include('vendor.chatter.discussion')
        </div>
    @endif
@endif
