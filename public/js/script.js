class IdeasPage {
    constructor() {
        this.currentPage = window.initialData?.page || 1;
        this.perPage = window.initialData?.perPage || 10;
        this.sort = window.initialData?.sort || 'newest';
        this.isLoading = false;
        this.lastScrollTop = 0;

        this.init();
    }

    init() {
        this.setupEventListeners();
        this.setupParallax();
        this.renderIdeas(window.initialData?.ideas || { data: [], meta: {} });
        this.updateURL();
    }

    setupEventListeners() {
        // Header scroll behavior
        window.addEventListener('scroll', this.handleScroll.bind(this));

        // Controls
        const perPageSelect = document.getElementById('per-page');
        const sortSelect = document.getElementById('sort');

        if (perPageSelect) {
            perPageSelect.addEventListener('change', (e) => {
                this.perPage = parseInt(e.target.value);
                this.currentPage = 1;
                this.loadIdeas();
            });
        }

        if (sortSelect) {
            sortSelect.addEventListener('change', (e) => {
                this.sort = e.target.value;
                this.currentPage = 1;
                this.loadIdeas();
            });
        }

        // Pagination
        const pagination = document.getElementById('pagination');
        if (pagination) {
            pagination.addEventListener('click', (e) => {
                if (e.target.classList.contains('pagination-btn')) {
                    const page = parseInt(e.target.dataset.page);
                    if (page && page !== this.currentPage) {
                        this.currentPage = page;
                        this.loadIdeas();
                    }
                }
            });
        }

        // Browser back/forward
        window.addEventListener('popstate', (e) => {
            if (e.state) {
                this.currentPage = e.state.page || 1;
                this.perPage = e.state.perPage || 10;
                this.sort = e.state.sort || 'newest';
                this.updateControls();
                this.loadIdeas(false);
            }
        });
    }

    setupParallax() {
        const banner = document.getElementById('banner');
        if (!banner) return;

        const bannerContent = banner.querySelector('.banner-content');

        window.addEventListener('scroll', () => {
            const scrollTop = window.pageYOffset;
            const rate = scrollTop * -0.5;
            const rate2 = scrollTop * -0.3;

            banner.style.transform = `translateY(${rate}px)`;
            if (bannerContent) {
                bannerContent.style.transform = `translateY(${rate2}px)`;
            }
        });
    }

    handleScroll() {
        const header = document.getElementById('header');
        if (!header) return;

        const scrollTop = window.pageYOffset;

        if (scrollTop > this.lastScrollTop && scrollTop > 100) {
            header.classList.add('hidden');
        } else {
            header.classList.remove('hidden');
        }

        if (scrollTop > 50) {
            header.classList.add('scrolled');
        } else {
            header.classList.remove('scrolled');
        }

        this.lastScrollTop = scrollTop;
    }

    async loadIdeas(pushState = true) {
        if (this.isLoading) return;

        this.isLoading = true;
        this.showLoading();

        try {
            const response = await fetch(`/ideas?page=${this.currentPage}&per_page=${this.perPage}&sort=${this.sort}`, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);

            let data = await response.json();

            if (!data.data) {
                data = {
                    data: [],
                    meta: {
                        current_page: this.currentPage,
                        last_page: 1,
                        per_page: this.perPage,
                        total: 0
                    }
                };
            }

            this.renderIdeas(data);

            if (pushState) {
                this.updateURL();
            }

        } catch (error) {
            console.error('Error loading ideas:', error);
            this.showError();

            this.renderIdeas({
                data: [],
                meta: {
                    current_page: this.currentPage,
                    last_page: 1,
                    per_page: this.perPage,
                    total: 0
                }
            });
        } finally {
            this.isLoading = false;
        }
    }

    renderIdeas(data) {
        const grid = document.getElementById('ideas-grid');
        const pagination = document.getElementById('pagination');

        if (!grid) return;

        const meta = data.meta || {};
        const total = meta.total || 0;
        const currentPage = meta.current_page || this.currentPage;
        const perPage = meta.per_page || this.perPage;

        const start = total > 0 ? ((currentPage - 1) * perPage) + 1 : 0;
        const end = Math.min(currentPage * perPage, total);

        const showingStart = document.getElementById('showing-start');
        const showingEnd = document.getElementById('showing-end');
        const totalItems = document.getElementById('total-items');

        if (showingStart) showingStart.textContent = start;
        if (showingEnd) showingEnd.textContent = end;
        if (totalItems) totalItems.textContent = total;

        if (data.data && data.data.length > 0) {
            grid.innerHTML = data.data.map(idea => this.createIdeaCard(idea)).join('');
            this.setupLazyLoading();
        } else {
            grid.innerHTML = '<div class="no-results">No ideas found</div>';
        }

        if (pagination) {
            this.renderPagination(meta, pagination);
        }
    }

    createIdeaCard(idea) {
        const publishedAt = idea.published_at ? new Date(idea.published_at).toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        }) : '';

        const imageUrl = idea.thumbnail || 'https://via.placeholder.com/400x200?text=No+Image';

        const title = idea.title || 'Untitled Idea';
        const contentPreview = idea.content ? this.stripHtml(idea.content).substring(0, 100) + '...' : '';

        return `
            <div class="idea-card">
                <div class="idea-thumbnail">
                    <img data-src="${imageUrl}" alt="${this.escapeHtml(title)}" class="lazy-load" />
                </div>
                <div class="idea-content">
                    <div class="idea-date">${publishedAt}</div>
                    <h3 class="idea-title">${this.escapeHtml(title)}</h3>
                    <p class="idea-excerpt">${this.escapeHtml(contentPreview)}</p>
                    <a href="#" class="idea-read-more">Read More</a>
                </div>
            </div>
        `;
    }

    stripHtml(html) {
        const tmp = document.createElement('div');
        tmp.innerHTML = html;
        return tmp.textContent || tmp.innerText || '';
    }

    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    renderPagination(meta, container) {
        const currentPage = meta.current_page || 1;
        const lastPage = meta.last_page || 1;

        if (lastPage <= 1) {
            container.innerHTML = '';
            return;
        }

        let html = '';

        html += `<button class="pagination-btn" data-page="${currentPage - 1}" ${currentPage === 1 ? 'disabled' : ''}>&laquo; Prev</button>`;

        const maxVisiblePages = 5;
        let startPage = Math.max(1, currentPage - Math.floor(maxVisiblePages / 2));
        let endPage = Math.min(lastPage, startPage + maxVisiblePages - 1);

        if (endPage - startPage + 1 < maxVisiblePages) {
            startPage = Math.max(1, endPage - maxVisiblePages + 1);
        }

        if (startPage > 1) {
            html += `<button class="pagination-btn" data-page="1">1</button>`;
            if (startPage > 2) {
                html += '<span class="pagination-ellipsis">...</span>';
            }
        }

        for (let i = startPage; i <= endPage; i++) {
            html += `<button class="pagination-btn ${i === currentPage ? 'active' : ''}" data-page="${i}">${i}</button>`;
        }

        if (endPage < lastPage) {
            if (endPage < lastPage - 1) {
                html += '<span class="pagination-ellipsis">...</span>';
            }
            html += `<button class="pagination-btn" data-page="${lastPage}">${lastPage}</button>`;
        }

        // Next button
        html += `<button class="pagination-btn" data-page="${currentPage + 1}" ${currentPage === lastPage ? 'disabled' : ''}">Next &raquo;</button>`;

        container.innerHTML = html;
    }

    setupLazyLoading() {
        if ('IntersectionObserver' in window) {
            const lazyImages = document.querySelectorAll('img.lazy-load');

            const imageObserver = new IntersectionObserver((entries, observer) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const img = entry.target;
                        img.src = img.dataset.src;
                        img.classList.remove('lazy-load');
                        img.classList.add('loaded');
                        observer.unobserve(img);

                        img.onerror = () => {
                            img.src = 'https://suitmedia.com/_ipx/f_webp&s_960x540/https://suitmedia.static-assets.id/strapi/shutterstock-2355575943-686e2544df0b7-3b9e833b9f.webp';
                        };
                    }
                });
            }, {
                rootMargin: '50px'
            });

            lazyImages.forEach(img => imageObserver.observe(img));
        } else {
            // Fallback for browsers without IntersectionObserver
            document.querySelectorAll('img.lazy-load').forEach(img => {
                img.src = img.dataset.src;
                img.classList.remove('lazy-load');
                img.classList.add('loaded');

                img.onerror = () => {
                    img.src = 'https://suitmedia.com/_ipx/f_webp&s_960x540/https://suitmedia.static-assets.id/strapi/shutterstock-2355575943-686e2544df0b7-3b9e833b9f.webp';
                };
            });
        }
    }

    updateControls() {
        const perPageSelect = document.getElementById('per-page');
        const sortSelect = document.getElementById('sort');

        if (perPageSelect) perPageSelect.value = this.perPage;
        if (sortSelect) sortSelect.value = this.sort;
    }

    updateURL() {
        const url = new URL(window.location);
        url.searchParams.set('page', this.currentPage);
        url.searchParams.set('per_page', this.perPage);
        url.searchParams.set('sort', this.sort);

        const state = {
            page: this.currentPage,
            perPage: this.perPage,
            sort: this.sort
        };

        history.pushState(state, '', url);
    }

    showLoading() {
        const grid = document.getElementById('ideas-grid');
        if (grid) {
            grid.innerHTML = `
                <div class="loading-state">
                    <div class="spinner"></div>
                    <p>Loading ideas...</p>
                </div>
            `;
        }
    }

    showError() {
        const grid = document.getElementById('ideas-grid');
        if (grid) {
            grid.innerHTML = `
                <div class="error-state">
                    <div class="error-icon">!</div>
                    <p>Error loading ideas. Please try again.</p>
                    <button class="retry-btn" id="retry-loading">Retry</button>
                </div>
            `;

            // Add event listener for retry button
            const retryBtn = document.getElementById('retry-loading');
            if (retryBtn) {
                retryBtn.addEventListener('click', () => this.loadIdeas());
            }
        }
    }
}

document.addEventListener('DOMContentLoaded', () => {
    new IdeasPage();
});
