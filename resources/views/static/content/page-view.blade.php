@if (isset($page))
    @if (!isset(app('request')->input()['item']))
        @include('partials.pagination')
        <div class="row m-t-md">
            <div class="col-md-12 text-center">
                <ul class="p-l-r-none">
                    <li>
                        <a
                            href="{{ isset($page->base_url) ? $page->base_url : '' }}"
                        >{{ $page->title }}</a>
                    </li>
                </ul>
            </div>
        </div>
    @endif
    <div class="row">
        <div class="text-left">{!! nl2br($page->body) !!}</div>
    </div>
    @include('partials.pagination')
    @if (isset($discussion))
        <div class="row">
            @include('vendor.chatter.discussion')
        </div>
    @endif
@endif

