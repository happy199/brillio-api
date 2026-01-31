@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Pagination Navigation" class="flex justify-between">
        {{-- Previous Page Link --}}
        @if ($paginator->onFirstPage())
            <span
                class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-gray-400 bg-gray-100 border border-transparent cursor-default leading-5 rounded-lg">
                &laquo; Précédent
            </span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev"
                class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-indigo-600 border border-transparent leading-5 rounded-lg hover:bg-indigo-700 focus:outline-none focus:ring ring-indigo-300 active:bg-indigo-800 transition ease-in-out duration-150 shadow-sm hover:shadow">
                &laquo; Précédent
            </a>
        @endif

        {{-- Next Page Link --}}
        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next"
                class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-indigo-600 border border-transparent leading-5 rounded-lg hover:bg-indigo-700 focus:outline-none focus:ring ring-indigo-300 active:bg-indigo-800 transition ease-in-out duration-150 shadow-sm hover:shadow">
                Suivant &raquo;
            </a>
        @else
            <span
                class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-gray-400 bg-gray-100 border border-transparent cursor-default leading-5 rounded-lg">
                Suivant &raquo;
            </span>
        @endif
    </nav>
@endif