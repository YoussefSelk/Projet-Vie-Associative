/**
 * Optimized Search Component
 * Features: Debouncing, Client-side filtering, Highlighting, Performance optimized
 */
class SearchComponent {
  constructor(options) {
    this.inputSelector = options.input;
    this.itemsSelector = options.items;
    this.searchableFields = options.fields || ["data-search"];
    this.noResultsMessage = options.noResultsMessage || "Aucun résultat trouvé";
    this.debounceDelay = options.debounceDelay || 150;
    this.onSearch = options.onSearch || null;
    this.onFilter = options.onFilter || null;

    this.input = document.querySelector(this.inputSelector);
    this.itemsContainer = document.querySelector(
      this.itemsSelector
    )?.parentElement;
    this.items = [];
    this.debounceTimer = null;
    this.currentQuery = "";
    this.activeFilters = new Set();

    if (this.input) {
      this.init();
    }
  }

  init() {
    // Cache items for performance
    this.cacheItems();

    // Setup event listeners
    this.input.addEventListener("input", (e) => this.handleInput(e));
    this.input.addEventListener("keydown", (e) => this.handleKeydown(e));

    // Setup clear button
    const clearBtn = this.input.parentElement?.querySelector(".search-clear");
    if (clearBtn) {
      clearBtn.addEventListener("click", () => this.clear());
    }

    // Setup filter chips
    document.querySelectorAll("[data-search-filter]").forEach((chip) => {
      chip.addEventListener("click", () => this.toggleFilter(chip));
    });

    // Create no results element
    this.createNoResultsElement();
  }

  cacheItems() {
    const container = document.querySelector(this.itemsSelector);
    if (!container) return;

    this.items = Array.from(container.children).map((item) => {
      const searchText = this.searchableFields
        .map((field) => {
          if (field.startsWith("data-")) {
            return item.getAttribute(field) || "";
          }
          const el = item.querySelector(field);
          return el ? el.textContent : "";
        })
        .join(" ")
        .toLowerCase();

      return {
        element: item,
        searchText: searchText,
        originalDisplay: item.style.display || "",
      };
    });
  }

  handleInput(e) {
    const query = e.target.value.trim();

    // Update clear button visibility
    const clearBtn = this.input.parentElement?.querySelector(".search-clear");
    if (clearBtn) {
      clearBtn.classList.toggle("visible", query.length > 0);
    }

    // Debounce search
    clearTimeout(this.debounceTimer);
    this.debounceTimer = setTimeout(() => {
      this.search(query);
    }, this.debounceDelay);
  }

  handleKeydown(e) {
    if (e.key === "Escape") {
      this.clear();
    }
  }

  search(query) {
    this.currentQuery = query.toLowerCase();
    const terms = this.currentQuery.split(/\s+/).filter((t) => t.length > 0);

    let visibleCount = 0;

    this.items.forEach((item) => {
      let isVisible = true;

      // Check search terms
      if (terms.length > 0) {
        isVisible = terms.every((term) => item.searchText.includes(term));
      }

      // Check active filters
      if (isVisible && this.activeFilters.size > 0) {
        const itemFilters =
          item.element.getAttribute("data-filter")?.split(",") || [];
        isVisible = [...this.activeFilters].some((f) =>
          itemFilters.includes(f)
        );
      }

      // Apply visibility
      item.element.style.display = isVisible ? item.originalDisplay : "none";

      if (isVisible) {
        visibleCount++;
        this.highlightMatches(item.element, terms);
      } else {
        this.removeHighlights(item.element);
      }
    });

    // Show/hide no results message
    this.toggleNoResults(
      visibleCount === 0 && (terms.length > 0 || this.activeFilters.size > 0)
    );

    // Update results count
    this.updateResultsCount(visibleCount);

    // Callback
    if (this.onSearch) {
      this.onSearch(query, visibleCount);
    }
  }

  highlightMatches(element, terms) {
    if (terms.length === 0) {
      this.removeHighlights(element);
      return;
    }

    // Only highlight specific elements to avoid breaking HTML
    const highlightTargets = element.querySelectorAll(
      "h3, h4, .event-description, td"
    );
    highlightTargets.forEach((target) => {
      // Skip if contains child elements with data
      if (target.querySelector("a, button, span.badge")) return;

      let html = target.textContent;
      terms.forEach((term) => {
        if (term.length < 2) return;
        const regex = new RegExp(`(${this.escapeRegex(term)})`, "gi");
        html = html.replace(regex, '<mark class="search-highlight">$1</mark>');
      });
      target.innerHTML = html;
    });
  }

  removeHighlights(element) {
    element.querySelectorAll(".search-highlight").forEach((mark) => {
      const parent = mark.parentNode;
      parent.replaceChild(document.createTextNode(mark.textContent), mark);
      parent.normalize();
    });
  }

  escapeRegex(string) {
    return string.replace(/[.*+?^${}()|[\]\\]/g, "\\$&");
  }

  toggleFilter(chip) {
    const filter = chip.getAttribute("data-search-filter");

    if (this.activeFilters.has(filter)) {
      this.activeFilters.delete(filter);
      chip.classList.remove("active");
    } else {
      this.activeFilters.add(filter);
      chip.classList.add("active");
    }

    // Re-run search with current query
    this.search(this.currentQuery);

    if (this.onFilter) {
      this.onFilter([...this.activeFilters]);
    }
  }

  clear() {
    this.input.value = "";
    this.currentQuery = "";

    const clearBtn = this.input.parentElement?.querySelector(".search-clear");
    if (clearBtn) {
      clearBtn.classList.remove("visible");
    }

    // Clear filters
    this.activeFilters.clear();
    document.querySelectorAll("[data-search-filter]").forEach((chip) => {
      chip.classList.remove("active");
    });

    // Show all items
    this.items.forEach((item) => {
      item.element.style.display = item.originalDisplay;
      this.removeHighlights(item.element);
    });

    this.toggleNoResults(false);
    this.updateResultsCount(this.items.length);

    this.input.focus();
  }

  createNoResultsElement() {
    this.noResultsEl = document.createElement("div");
    this.noResultsEl.className = "no-results";
    this.noResultsEl.style.display = "none";
    this.noResultsEl.innerHTML = `
            <i class="fas fa-search"></i>
            <h3>${this.noResultsMessage}</h3>
            <p>Essayez de modifier vos termes de recherche</p>
            <button type="button" class="btn btn-outline btn-sm" onclick="this.closest('.no-results').previousElementSibling?.querySelector('.search-clear')?.click()">
                <i class="fas fa-times"></i> Effacer la recherche
            </button>
        `;

    const container = document.querySelector(this.itemsSelector)?.parentElement;
    if (container) {
      container.appendChild(this.noResultsEl);
    }
  }

  toggleNoResults(show) {
    if (this.noResultsEl) {
      this.noResultsEl.style.display = show ? "block" : "none";
    }
  }

  updateResultsCount(count) {
    const countEl = document.querySelector(".search-results-count");
    if (countEl) {
      countEl.innerHTML = `<strong>${count}</strong> résultat${
        count !== 1 ? "s" : ""
      }`;
    }
  }

  refresh() {
    this.cacheItems();
    this.search(this.currentQuery);
  }
}

// Auto-initialize search on page load
document.addEventListener("DOMContentLoaded", function () {
  // Initialize search for events page
  if (document.querySelector("#eventSearch")) {
    window.eventSearch = new SearchComponent({
      input: "#eventSearch",
      items: ".events-grid",
      fields: ["data-search", "h3", ".event-description"],
      noResultsMessage: "Aucun événement trouvé",
    });
  }

  // Initialize search for users page
  if (document.querySelector("#userSearch")) {
    window.userSearch = new SearchComponent({
      input: "#userSearch",
      items: ".data-table tbody",
      fields: ["data-search"],
      noResultsMessage: "Aucun utilisateur trouvé",
    });
  }

  // Initialize search for subscriptions page
  if (document.querySelector("#subscriptionSearch")) {
    window.subscriptionSearch = new SearchComponent({
      input: "#subscriptionSearch",
      items: ".events-grid",
      fields: ["data-search", "h3"],
      noResultsMessage: "Aucune inscription trouvée",
    });
  }

  // Initialize search for clubs page
  if (document.querySelector("#clubSearch")) {
    window.clubSearch = new SearchComponent({
      input: "#clubSearch",
      items: ".clubs-grid, .data-table tbody",
      fields: ["data-search", "h3", "h4"],
      noResultsMessage: "Aucun club trouvé",
    });
  }
});
