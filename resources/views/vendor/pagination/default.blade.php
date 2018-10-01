@if ($paginator->hasPages())
    <ul class="pagination">
        {{-- Previous Page Link --}}

        @if ($paginator->onFirstPage())
            <li class="disabled"><span>&laquo;</span></li>
        @else
            <li><a href="{{ url(parse_url($paginator->previousPageUrl(), PHP_URL_PATH) .'?'. parse_url($paginator->previousPageUrl(), PHP_URL_QUERY)) }}" rel="prev">&laquo;</a></li>
        @endif

        {{-- Pagination Elements --}}
        @foreach ($elements as $element)
            {{-- "Three Dots" Separator --}}
            @if (is_string($element))
                <li class="disabled"><span>{{ $element }}</span></li>
            @endif

            {{-- Array Of Links --}}
            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <li class="active"><span>{{ $page }}</span></li>
                    @else
                        <li><a href="{{ url(parse_url($url, PHP_URL_PATH) .'?'. parse_url($url, PHP_URL_QUERY)) }}">{{ $page }}</a></li>
                    @endif
                @endforeach
            @endif
        @endforeach

        {{-- Next Page Link --}}
        @if ($paginator->hasMorePages())
            <li><a href="{{ url(parse_url($paginator->nextPageUrl(), PHP_URL_PATH) .'?'. parse_url($paginator->nextPageUrl(), PHP_URL_QUERY)) }}" rel="next">&raquo;</a></li>
        @else
            <li class="disabled"><span>&raquo;</span></li>
        @endif
    </ul>
@endif
