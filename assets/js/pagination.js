/**
 * Reusable Pagination Component
 *
 * Works with both table rows and card grids.
 * Integrates with SearchComponent — only paginates visible (filtered) items.
 *
 * Usage:
 *   new PaginationComponent({
 *     itemsSelector: '.events-grid',    // container whose children are paginated
 *     paginationSelector: '#pagination', // where to render pagination controls
 *     perPage: 9,                        // items per page (default 10)
 *     perPageOptions: [6, 9, 18],        // selector options
 *     searchComponent: window.eventSearch // optional SearchComponent instance
 *   });
 */
class PaginationComponent {
  constructor(options) {
    this.itemsSelector = options.itemsSelector;
    this.paginationSelector = options.paginationSelector;
    this.perPage = options.perPage || 10;
    this.perPageOptions = options.perPageOptions || [5, 10, 25, 50];
    this.searchComponent = options.searchComponent || null;
    this.currentPage = 1;
    this.allItems = [];
    this.container = null;
    this.paginationEl = null;
    this._updating = false; // Guard against recursive updates

    this.init();
  }

  init() {
    this.container = document.querySelector(this.itemsSelector);
    this.paginationEl = document.querySelector(this.paginationSelector);

    if (!this.container || !this.paginationEl) return;

    // Cache all items
    this.cacheItems();

    // Hook into SearchComponent if provided
    this.hookSearch();

    // Initial render
    this.update();
  }

  cacheItems() {
    this.allItems = Array.from(this.container.children).filter(
      (el) =>
        !el.classList.contains("no-results") &&
        !el.classList.contains("pagination-wrapper"),
    );
  }

  /**
   * Returns only items currently visible (not hidden by search/filter).
   * Items hidden by pagination (pagination-hidden class) are still considered visible
   * for pagination purposes — only search-hidden items are excluded.
   */
  getVisibleItems() {
    return this.allItems.filter((item) => {
      if (item.classList.contains("pagination-hidden")) return true;
      return item.style.display !== "none";
    });
  }

  /**
   * Hook into SearchComponent's search callback to re-paginate after filtering
   */
  hookSearch() {
    if (
      this.searchComponent &&
      typeof this.searchComponent.onSearch !== "undefined"
    ) {
      const originalOnSearch = this.searchComponent.onSearch;
      const self = this;
      this.searchComponent.onSearch = function (query, visibleCount) {
        if (typeof originalOnSearch === "function")
          originalOnSearch(query, visibleCount);
        // Remove pagination-hidden from all items first, so search state is clean
        self.allItems.forEach((item) =>
          item.classList.remove("pagination-hidden"),
        );
        self.currentPage = 1;
        self.update();
      };
    }
  }

  update() {
    // Guard against recursive calls (e.g., from search callback triggering update again)
    if (this._updating) return;
    this._updating = true;

    try {
      const visibleItems = this.getVisibleItems();
      const totalVisible = visibleItems.length;
      const totalPages = Math.max(1, Math.ceil(totalVisible / this.perPage));

      // Clamp current page
      if (this.currentPage > totalPages) this.currentPage = totalPages;
      if (this.currentPage < 1) this.currentPage = 1;

      const start = (this.currentPage - 1) * this.perPage;
      const end = start + this.perPage;

      // Hide/show items for current page
      visibleItems.forEach((item, index) => {
        if (index >= start && index < end) {
          item.style.display = "";
          item.classList.remove("pagination-hidden");
        } else {
          item.style.display = "none";
          item.classList.add("pagination-hidden");
        }
      });

      // Also keep search-hidden items hidden
      this.allItems.forEach((item) => {
        if (!visibleItems.includes(item)) {
          item.style.display = "none";
        }
      });

      this.renderControls(totalVisible, totalPages);
    } finally {
      this._updating = false;
    }
  }

  renderControls(totalItems, totalPages) {
    if (totalItems === 0) {
      this.paginationEl.innerHTML = "";
      return;
    }

    const start = (this.currentPage - 1) * this.perPage + 1;
    const end = Math.min(this.currentPage * this.perPage, totalItems);

    let html = '<div class="pagination-controls">';

    // Info text
    html += `<div class="pagination-info">
            Affichage <strong>${start}</strong>-<strong>${end}</strong> sur <strong>${totalItems}</strong>
        </div>`;

    // Page navigation (only if multiple pages)
    if (totalPages > 1) {
      html += '<div class="pagination-nav">';

      // Previous button
      html += `<button class="pagination-btn pagination-prev" ${this.currentPage === 1 ? "disabled" : ""} data-page="${this.currentPage - 1}">
                <i class="fas fa-chevron-left"></i>
            </button>`;

      // Page numbers
      const pages = this.getPageNumbers(totalPages);
      pages.forEach((p) => {
        if (p === "...") {
          html += '<span class="pagination-ellipsis">...</span>';
        } else {
          html += `<button class="pagination-btn pagination-page ${p === this.currentPage ? "active" : ""}" data-page="${p}">${p}</button>`;
        }
      });

      // Next button
      html += `<button class="pagination-btn pagination-next" ${this.currentPage === totalPages ? "disabled" : ""} data-page="${this.currentPage + 1}">
                <i class="fas fa-chevron-right"></i>
            </button>`;

      html += "</div>";
    }

    // Per-page selector
    html += '<div class="pagination-per-page">';
    html += "<label>Par page :</label>";
    html += '<select class="pagination-select">';
    this.perPageOptions.forEach((opt) => {
      html += `<option value="${opt}" ${opt === this.perPage ? "selected" : ""}>${opt}</option>`;
    });
    html += "</select>";
    html += "</div>";

    html += "</div>";

    this.paginationEl.innerHTML = html;

    // Bind events
    this.paginationEl
      .querySelectorAll(".pagination-btn[data-page]")
      .forEach((btn) => {
        btn.addEventListener("click", () => {
          const page = parseInt(btn.dataset.page);
          if (page >= 1 && page <= totalPages) {
            this.currentPage = page;
            this.repaginate();
          }
        });
      });

    const select = this.paginationEl.querySelector(".pagination-select");
    if (select) {
      select.addEventListener("change", () => {
        this.perPage = parseInt(select.value);
        this.currentPage = 1;
        this.repaginate();
      });
    }
  }

  /**
   * Re-paginate: first restore all items' display, then let search re-filter, then paginate
   */
  repaginate() {
    // First restore all items to visible
    this.allItems.forEach((item) => {
      item.style.display = "";
      item.classList.remove("pagination-hidden");
    });

    // If search component exists and has a search method, re-run search to re-hide filtered items
    if (
      this.searchComponent &&
      typeof this.searchComponent.search === "function"
    ) {
      // Temporarily disable our hookSearch callback to avoid double update()
      const savedCallback = this.searchComponent.onSearch;
      this.searchComponent.onSearch = null;

      this.searchComponent.search(this.searchComponent.currentQuery || "");

      // Restore the callback
      this.searchComponent.onSearch = savedCallback;
    }

    this.update();

    // Scroll to top of container
    this.container.scrollIntoView({ behavior: "smooth", block: "nearest" });
  }

  /**
   * Generate page numbers with ellipsis for large page counts
   */
  getPageNumbers(totalPages) {
    const current = this.currentPage;
    const pages = [];

    if (totalPages <= 7) {
      for (let i = 1; i <= totalPages; i++) pages.push(i);
    } else {
      pages.push(1);
      if (current > 3) pages.push("...");

      const rangeStart = Math.max(2, current - 1);
      const rangeEnd = Math.min(totalPages - 1, current + 1);

      for (let i = rangeStart; i <= rangeEnd; i++) pages.push(i);

      if (current < totalPages - 2) pages.push("...");
      pages.push(totalPages);
    }

    return pages;
  }

  /**
   * Refresh items cache (call after DOM changes)
   */
  refresh() {
    this.cacheItems();
    this.currentPage = 1;
    this.update();
  }
}

// Auto-initialize pagination on page load — views will define their own instances
// via inline <script> blocks after DOMContentLoaded
