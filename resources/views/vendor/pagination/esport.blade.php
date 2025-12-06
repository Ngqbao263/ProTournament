@if ($paginator->hasPages())
    <ul class="pagination d-flex justify-content-center align-items-center gap-2">
        {{-- Previous --}}
        @if ($paginator->onFirstPage())
            <li class="disabled btn btn-sm btn-secondary rounded-pill px-3">‹</li>
        @else
            <li><a class="btn btn-sm btn-success rounded-pill px-3" href="{{ $paginator->previousPageUrl() }}">‹</a></li>
        @endif

        {{-- Page Numbers --}}
        @foreach ($elements as $element)
            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <li><span
                                class="btn btn-sm btn-light text-dark fw-bold rounded-pill px-3">{{ $page }}</span>
                        </li>
                    @else
                        <li><a class="btn btn-sm btn-outline-success rounded-pill px-3"
                                href="{{ $url }}">{{ $page }}</a></li>
                    @endif
                @endforeach
            @endif
        @endforeach

        {{-- Next --}}
        @if ($paginator->hasMorePages())
            <li><a class="btn btn-sm btn-success rounded-pill px-3" href="{{ $paginator->nextPageUrl() }}">›</a></li>
        @else
            <li class="disabled btn btn-sm btn-secondary rounded-pill px-3">›</li>
        @endif
    </ul>
@endif
