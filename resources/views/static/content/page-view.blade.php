@if (isset($page))
    @if (!isset(app('request')->input()['item']))
        @include('partials.pagination')
        <div class="col-xs-12 text-left section pages-list static-pages m-b-lg m-t-md">
            <div class="filter-content section-nav-bar">
                <ul class="nav filter-type right-border {{ isset($class) ? $class : '' }}">
                    <li>
                        <a
                            href="{{ isset($page->base_url) ? $page->base_url : '' }}"
                        >{{ $page->title }}</a>
                    </li>
                </ul>
            </div>
        </div>
    @endif
    <div class="row static-pages">
        <div class="text-left">{!! nl2br($page->body) !!}</div>
    </div>
    @include('partials.pagination')
    @if (isset($discussion))
        <div class="row discussion">
            @include('vendor.chatter.discussion')
        </div>
    @endif
@endif

