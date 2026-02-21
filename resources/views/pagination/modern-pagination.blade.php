@if ($paginator->hasPages())
<div class="flex flex-col items-center gap-4 py-8">
    {{-- Summary --}}
    <div class="text-sm text-gray-500">
        Affichage de <span class="font-semibold text-gray-900">{{ $paginator->firstItem() }}</span> à <span
            class="font-semibold text-gray-900">{{ $paginator->lastItem() }}</span> sur <span
            class="font-semibold text-gray-900">{{ $paginator->total() }}</span> résultats
    </div>

    <nav role="navigation" aria-label="Pagination Navigation" class="flex items-center gap-2">
        {{-- Previous Page Link --}}
        @if ($paginator->onFirstPage())
        <span class="p-2 text-gray-300 cursor-not-allowed">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
        </span>
        @else
        <a href="{{ $paginator->previousPageUrl() }}" rel="prev"
            class="p-2 text-gray-600 hover:text-orange-500 transition-colors">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
        </a>
        @endif

        {{-- Pagination Elements --}}
        <div class="flex items-center gap-1">
            @foreach ($elements as $element)
            {{-- "Three Dots" Separator --}}
            @if (is_string($element))
            <span class="px-3 py-1 text-gray-400">{{ $element }}</span>
            @endif

            {{-- Array Of Links --}}
            @if (is_array($element))
            @foreach ($element as $page => $url)
            @if ($page == $paginator->currentPage())
            <span
                class="w-10 h-10 flex items-center justify-center rounded-xl bg-gradient-to-r from-orange-500 to-pink-500 text-white font-bold shadow-md shadow-orange-200">
                {{ $page }}
            </span>
            @else
            <a href="{{ $url }}"
                class="w-10 h-10 flex items-center justify-center rounded-xl text-gray-600 hover:bg-orange-50 hover:text-orange-600 transition-all">
                {{ $page }}
            </a>
            @endif
            @endforeach
            @endif
            @endforeach
        </div>

        {{-- Next Page Link --}}
        @if ($paginator->hasMorePages())
        <a href="{{ $paginator->nextPageUrl() }}" rel="next"
            class="p-2 text-gray-600 hover:text-orange-500 transition-colors">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
        </a>
        @else
        <span class="p-2 text-gray-300 cursor-not-allowed">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
        </span>
        @endif
    </nav>
</div>
@endif