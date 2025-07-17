@extends('layouts.app')

@section('title', 'Ideas - Suitmedia')

@section('description', 'Where all our great things begin - Explore innovative ideas and insights from Suitmedia')

@section('content')
<!-- Banner Section -->
<section class="banner" id="banner">
    <div class="banner-content">
        <h1 class="banner-title">Ideas</h1>
        <p class="banner-subtitle">Where all our great things begin</p>
    </div>
    <div class="banner-slope"></div>
</section>

<!-- Ideas Section -->
<section class="ideas-section">
    <div class="container">
        <!-- Controls -->
        <div class="ideas-controls">
            <div class="showing-info">
                <span>Showing <span id="showing-start">{{ ($ideas['meta']['from'] ?? 0) ?: 0 }}</span> - <span id="showing-end">{{ ($ideas['meta']['to'] ?? 0) ?: 0 }}</span> of <span id="total-items">{{ $ideas['meta']['total'] ?? 0 }}</span></span>
            </div>
            <div class="controls-group">
                <div class="control-item">
                    <label for="per-page">Show per page:</label>
                    <select id="per-page">
                        <option value="10" {{ $perPage == 10 ? 'selected' : '' }}>10</option>
                        <option value="20" {{ $perPage == 20 ? 'selected' : '' }}>20</option>
                        <option value="50" {{ $perPage == 50 ? 'selected' : '' }}>50</option>
                    </select>
                </div>
                <div class="control-item">
                    <label for="sort">Sort by:</label>
                    <select id="sort">
                        <option value="newest" {{ $sort == 'newest' ? 'selected' : '' }}>Newest</option>
                        <option value="oldest" {{ $sort == 'oldest' ? 'selected' : '' }}>Oldest</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Ideas Grid -->
        <div class="ideas-grid" id="ideas-grid">
            @if(!empty($ideas['data']))
                @foreach($ideas['data'] as $idea)
                    <div class="idea-card">
                        <div class="idea-thumbnail">
                            @php
                                $imageUrl = $idea['first_image'] ??
                                    ($idea['medium_image'][0]['url'] ??
                                    ($idea['small_image'][0]['url'] ??
                                    'https://via.placeholder.com/400x200?text=No+Image'))
                            @endphp
                            <img
                                data-src="{{ $imageUrl }}"
                                alt="{{ $idea['title'] }}"
                                src="data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjgwIiBoZWlnaHQ9IjIwMCIgdmlld0JveD0iMCAwIDI4MCAyMDAiIGZpbGw9Im5vbmUiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PHJlY3Qgd2lkdGg9IjI4MCIgaGVpZ2h0PSIyMDAiIGZpbGw9IiNmMGYwZjAiLz48L3N2Zz4="
                                loading="lazy"
                                class="lazy-load"
                            />
                        </div>
                        <div class="idea-content">
                            <div class="idea-date">
                                {{ \Carbon\Carbon::parse($idea['published_at'])->format('F j, Y') }}
                            </div>
                            <h3 class="idea-title">{{ $idea['title'] }}</h3>
                            <p class="idea-excerpt">
                                {{ Str::limit(strip_tags($idea['content']), 100) }}
                            </p>
                            <a href="#" class="idea-read-more">Read More</a>
                        </div>
                    </div>
                @endforeach
            @else
                <div class="no-ideas">
                    <h3>No ideas found</h3>
                    <p>Please try again later or adjust your search criteria.</p>
                </div>
            @endif
        </div>

        <!-- Pagination -->
        <div class="pagination" id="pagination">
            @if(isset($ideas['meta']) && $ideas['meta']['last_page'] > 1)
                @php
                    $currentPage = $ideas['meta']['current_page'];
                    $lastPage = $ideas['meta']['last_page'];
                @endphp

                <!-- Previous Button -->
                <button
                    class="pagination-btn"
                    data-page="{{ $currentPage - 1 }}"
                    {{ $currentPage == 1 ? 'disabled' : '' }}
                >
                    &laquo; Prev
                </button>

                <!-- Page Numbers -->
                @php
                    $startPage = max(1, $currentPage - 2);
                    $endPage = min($lastPage, $currentPage + 2);
                @endphp

                @if($startPage > 1)
                    <button class="pagination-btn" data-page="1">1</button>
                    @if($startPage > 2)
                        <span class="pagination-ellipsis">...</span>
                    @endif
                @endif

                @for($i = $startPage; $i <= $endPage; $i++)
                    <button
                        class="pagination-btn {{ $i == $currentPage ? 'active' : '' }}"
                        data-page="{{ $i }}"
                    >
                        {{ $i }}
                    </button>
                @endfor

                @if($endPage < $lastPage)
                    @if($endPage < $lastPage - 1)
                        <span class="pagination-ellipsis">...</span>
                    @endif
                    <button class="pagination-btn" data-page="{{ $lastPage }}">{{ $lastPage }}</button>
                @endif

                <!-- Next Button -->
                <button
                    class="pagination-btn"
                    data-page="{{ $currentPage + 1 }}"
                    {{ $currentPage == $lastPage ? 'disabled' : '' }}
                >
                    Next &raquo;
                </button>
            @endif
        </div>
    </div>
</section>
@endsection

@section('scripts')
<script>
    // Pass initial data to JavaScript
    window.initialData = {
        page: {{ $page }},
        perPage: {{ $perPage }},
        sort: '{{ $sort }}',
        ideas: @json($ideas)
    };
</script>
<script src="{{ asset('js/script.js') }}"></script>
@endsection
