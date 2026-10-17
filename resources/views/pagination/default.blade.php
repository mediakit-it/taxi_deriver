@if ($paginator->hasPages())
    <div class="pagination-prd">
        <div class="pagination-container">
            <nav class="pagination">
                {{-- Кнопка "Предыдущая страница" --}}
                @if ($paginator->onFirstPage())
                    <span class="prev disabled"><a href="#" title=""><i class="fa fa-angle-left"></i></a></span>
                @else
                    <span class="prev"><a href="{{ $paginator->previousPageUrl() }}" title=""><i class="fa fa-angle-left"></i></a></span>
                @endif

                {{-- Список номеров страниц --}}
                @foreach ($elements as $element)
                    @if (is_string($element))
                        <p>{{ $element }}</p>
                    @endif

                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            @if ($page == $paginator->currentPage())
                                <span class="page active"><a href="#" title="">{{ $page }}</a></span>
                            @else
                                <span class="page"><a href="{{ $url }}" title="">{{ $page }}</a></span>
                            @endif
                        @endforeach
                    @endif
                @endforeach

                {{-- Кнопка "Следующая страница" --}}
                @if ($paginator->hasMorePages())
                    <span class="next"><a href="{{ $paginator->nextPageUrl() }}" title=""><i class="fa fa-angle-right"></i></a></span>
                @else
                    <span class="next disabled"><a href="#" title=""><i class="fa fa-angle-right"></i></a></span>
                @endif
            </nav>
        </div>
    </div>
@endif
