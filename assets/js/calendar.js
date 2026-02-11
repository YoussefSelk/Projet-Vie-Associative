/**
 * =============================================================================
 * CALENDRIER AJAX - Module JavaScript
 * =============================================================================
 *
 * Calendrier interactif avec :
 * - Navigation mois par mois sans rechargement (AJAX)
 * - Transition fluide entre les mois
 * - Filtrage par campus en temps réel
 * - Modal détails événement
 * - Abonnement/désabonnement AJAX
 * - Rappels d'événements proches
 * - Toast notifications
 * - Support clavier (Échap, flèches)
 *
 * @author Équipe de développement EILCO
 * @version 3.0
 */

(function () {
  "use strict";

  // =========================================================================
  // STATE
  // =========================================================================
  const state = {
    currentMonth: new Date().getMonth() + 1,
    currentYear: new Date().getFullYear(),
    data: null,
    isLoading: false,
    activeCampusFilters: new Set([
      "calais",
      "longuenesse",
      "boulogne",
      "dunkerque",
    ]),
    remindersShown: false,
    cache: {}, // Cache for loaded months
  };

  // =========================================================================
  // DOM REFS
  // =========================================================================
  let els = {};

  function cacheDom() {
    els = {
      app: document.getElementById("calendarApp"),
      grid: document.getElementById("calGrid"),
      gridWrapper: document.getElementById("calGridWrapper"),
      loading: document.getElementById("calLoading"),
      monthLabel: document.getElementById("calMonthLabel"),
      prevBtn: document.getElementById("calPrev"),
      nextBtn: document.getElementById("calNext"),
      todayBtn: document.getElementById("calToday"),
      modalOverlay: document.getElementById("calModalOverlay"),
      modal: document.getElementById("calModal"),
      modalContent: document.getElementById("calModalContent"),
      modalClose: document.getElementById("calModalClose"),
      reminders: document.getElementById("calReminders"),
      toast: document.getElementById("calToast"),
      filterForm: document.getElementById("campus-filter-form"),
    };
  }

  // =========================================================================
  // HELPERS
  // =========================================================================
  function escapeHtml(text) {
    if (!text) return "";
    const div = document.createElement("div");
    div.textContent = text;
    return div.innerHTML;
  }

  function cacheKey(month, year) {
    return `${year}-${month}`;
  }

  // =========================================================================
  // AJAX FETCH
  // =========================================================================
  async function fetchCalendarData(month, year) {
    const key = cacheKey(month, year);
    if (state.cache[key]) {
      return state.cache[key];
    }

    const response = await fetch(
      `index.php?page=calendar-data&month=${month}&year=${year}`,
      {
        headers: { "X-Requested-With": "XMLHttpRequest" },
      },
    );

    if (!response.ok) throw new Error(`HTTP ${response.status}`);
    const data = await response.json();
    state.cache[key] = data;
    return data;
  }

  // =========================================================================
  // RENDER CALENDAR GRID
  // =========================================================================
  function renderGrid(data) {
    const { nb_days, first_day_offset, events_by_day, today } = data;
    const todayParts = today.split("-");
    const todayDay = parseInt(todayParts[2]);
    const todayMonth = parseInt(todayParts[1]);
    const todayYear = parseInt(todayParts[0]);
    const isCurrentMonth = data.month === todayMonth && data.year === todayYear;

    const dayNames = ["Lun", "Mar", "Mer", "Jeu", "Ven", "Sam", "Dim"];

    let html = '<table class="cal-table"><thead><tr>';
    dayNames.forEach((d) => {
      html += `<th>${d}</th>`;
    });
    html += "</tr></thead><tbody><tr>";

    // Empty cells before first day
    for (let i = 0; i < first_day_offset; i++) {
      html += '<td class="cal-empty"></td>';
    }

    let cellIndex = first_day_offset;

    for (let day = 1; day <= nb_days; day++) {
      if (cellIndex % 7 === 0 && cellIndex !== 0) {
        html += "</tr><tr>";
      }

      const isToday = isCurrentMonth && day === todayDay;
      const todayClass = isToday ? " cal-today" : "";
      const dayEvents = events_by_day[day] || [];
      const hasEvents = dayEvents.length > 0 ? " cal-has-events" : "";

      html += `<td class="cal-day${todayClass}${hasEvents}">`;
      html += `<div class="day-number">${day}</div>`;

      if (dayEvents.length > 0) {
        html += '<div class="cal-events">';
        dayEvents.forEach((ev) => {
          const campus = (ev.campus || "").toLowerCase();
          const clubName = ev.nom_club ? escapeHtml(ev.nom_club) : "";
          const titre = escapeHtml(ev.titre);
          const subscribed = ev.subscribed ? " cal-event-subscribed" : "";

          html += `<div class="event campus-${campus}${subscribed}" 
                                  data-event='${JSON.stringify(ev).replace(/'/g, "&#39;")}'
                                  title="${titre}${clubName ? " - " + clubName : ""}">
                                <span class="event-title">${titre}</span>
                                ${clubName ? `<span class="event-club">${clubName}</span>` : ""}
                                ${ev.subscribed ? '<i class="fas fa-bell event-sub-icon"></i>' : ""}
                             </div>`;
        });
        html += "</div>";
      }

      html += "</td>";
      cellIndex++;
    }

    // Fill remaining cells
    while (cellIndex % 7 !== 0) {
      html += '<td class="cal-empty"></td>';
      cellIndex++;
    }

    html += "</tr></tbody></table>";
    return html;
  }

  // =========================================================================
  // UPDATE MONTH LABEL
  // =========================================================================
  function updateMonthLabel(data) {
    const monthText = els.monthLabel.querySelector(".cal-month-text");
    const yearText = els.monthLabel.querySelector(".cal-year-text");
    if (monthText) monthText.textContent = data.month_name;
    if (yearText) yearText.textContent = data.year;
  }

  // =========================================================================
  // LOAD MONTH
  // =========================================================================
  async function loadMonth(month, year, direction) {
    if (state.isLoading) return;
    state.isLoading = true;

    // Show loading
    els.loading.classList.add("visible");
    els.prevBtn.disabled = true;
    els.nextBtn.disabled = true;

    try {
      const data = await fetchCalendarData(month, year);
      state.data = data;
      state.currentMonth = data.month;
      state.currentYear = data.year;

      // Render new content immediately
      els.grid.innerHTML = renderGrid(data);
      updateMonthLabel(data);

      // Apply campus filters
      applyFilters();

      // Bind event clicks
      bindEventClicks();

      // Show reminders only on first load
      if (
        !state.remindersShown &&
        data.reminders &&
        data.reminders.length > 0
      ) {
        showReminders(data.reminders);
        state.remindersShown = true;
      }

      // Prefetch adjacent months
      prefetchAdjacent(data);
    } catch (error) {
      console.error("Erreur calendrier:", error);
      els.grid.innerHTML = `
                <div class="cal-error">
                    <i class="fas fa-exclamation-triangle"></i>
                    <p>Erreur de chargement du calendrier</p>
                    <button onclick="window.CalendarApp.reload()" class="cal-retry-btn">Réessayer</button>
                </div>`;
    } finally {
      els.loading.classList.remove("visible");
      els.prevBtn.disabled = false;
      els.nextBtn.disabled = false;
      state.isLoading = false;
    }
  }

  // =========================================================================
  // PREFETCH ADJACENT MONTHS
  // =========================================================================
  function prefetchAdjacent(data) {
    // Silently prefetch previous and next months
    const { prev, next } = data;
    fetchCalendarData(prev.month, prev.year).catch(() => {});
    fetchCalendarData(next.month, next.year).catch(() => {});
  }

  // =========================================================================
  // CAMPUS FILTERING
  // =========================================================================
  function applyFilters() {
    const events = els.grid.querySelectorAll(".event");
    events.forEach((ev) => {
      const classes = ev.className.split(" ");
      const campusClass = classes.find((c) => c.startsWith("campus-"));
      const campus = campusClass ? campusClass.replace("campus-", "") : "";
      ev.style.display = state.activeCampusFilters.has(campus) ? "" : "none";
    });
  }

  function initFilters() {
    if (!els.filterForm) return;
    els.filterForm.querySelectorAll('input[name="campus"]').forEach((cb) => {
      cb.addEventListener("change", () => {
        if (cb.checked) {
          state.activeCampusFilters.add(cb.value);
        } else {
          state.activeCampusFilters.delete(cb.value);
        }
        applyFilters();
      });
    });
  }

  // =========================================================================
  // EVENT DETAIL MODAL
  // =========================================================================
  function bindEventClicks() {
    els.grid.querySelectorAll(".event").forEach((el) => {
      el.addEventListener("click", () => {
        const eventData = JSON.parse(el.getAttribute("data-event"));
        openModal(eventData);
      });
    });
  }

  function openModal(ev) {
    const isLoggedIn = els.app.dataset.loggedIn === "true";
    const subscribed = ev.subscribed;

    let subscriptionBtn = "";
    if (isLoggedIn) {
      const bellIcon = subscribed ? "fa-bell-slash" : "fa-bell";
      const bellTitle = subscribed ? "Se désabonner" : "S'abonner";
      const btnClass = subscribed ? "cal-sub-btn-active" : "cal-sub-btn";
      subscriptionBtn = `
                <button class="${btnClass}" id="modalSubBtn" 
                        data-event-id="${ev.event_id}" 
                        data-subscribed="${subscribed}" 
                        title="${bellTitle}">
                    <i class="fas ${bellIcon}"></i>
                </button>`;
    } else {
      subscriptionBtn = `
                <a href="index.php?page=login" class="cal-login-link" title="Connectez-vous pour vous abonner">
                    <i class="far fa-bell"></i> Connexion
                </a>`;
    }

    const clubHtml = ev.nom_club
      ? `
            <div class="cal-modal-row">
                <div class="cal-modal-icon"><i class="fas fa-users"></i></div>
                <div class="cal-modal-info">
                    <div class="cal-modal-label">Club organisateur</div>
                    <div class="cal-modal-value">${escapeHtml(ev.nom_club)}</div>
                </div>
            </div>`
      : "";

    els.modalContent.innerHTML = `
            <div class="cal-modal-header">
                ${subscriptionBtn}
                <h3 class="cal-modal-title">${escapeHtml(ev.titre)}</h3>
                <span class="cal-modal-campus campus-${(ev.campus || "").toLowerCase()}">${escapeHtml(ev.campus)}</span>
            </div>
            <div class="cal-modal-body">
                <div class="cal-modal-row">
                    <div class="cal-modal-icon"><i class="fas fa-clock"></i></div>
                    <div class="cal-modal-info">
                        <div class="cal-modal-label">Horaires</div>
                        <div class="cal-modal-value">${escapeHtml(ev.horaire_debut)} — ${escapeHtml(ev.horaire_fin)}</div>
                    </div>
                </div>
                <div class="cal-modal-row">
                    <div class="cal-modal-icon"><i class="fas fa-map-marker-alt"></i></div>
                    <div class="cal-modal-info">
                        <div class="cal-modal-label">Lieu</div>
                        <div class="cal-modal-value">${escapeHtml(ev.lieu)}</div>
                    </div>
                </div>
                ${clubHtml}
            </div>
            <a href="index.php?page=event-view&id=${ev.event_id}" class="cal-modal-link">
                <i class="fas fa-external-link-alt"></i> Voir les détails complets
            </a>
        `;

    els.modalOverlay.classList.add("visible");
    els.modalOverlay.setAttribute("aria-hidden", "false");
    document.body.style.overflow = "hidden";

    // Bind subscription button
    const subBtn = document.getElementById("modalSubBtn");
    if (subBtn) {
      subBtn.addEventListener("click", (e) => {
        e.stopPropagation();
        toggleSubscription(ev.event_id, subBtn);
      });
    }
  }

  function closeModal() {
    els.modalOverlay.classList.remove("visible");
    els.modalOverlay.setAttribute("aria-hidden", "true");
    document.body.style.overflow = "";
  }

  // =========================================================================
  // AJAX SUBSCRIPTION TOGGLE
  // =========================================================================
  async function toggleSubscription(eventId, button) {
    button.disabled = true;
    button.classList.add("cal-sub-loading");

    try {
      const resp = await fetch(
        `index.php?page=subscribe-ajax&event_id=${eventId}`,
        {
          headers: { "X-Requested-With": "XMLHttpRequest" },
        },
      );
      const result = await resp.json();

      if (result.success) {
        const sub = result.subscribed;
        button.dataset.subscribed = sub;
        button.innerHTML = sub
          ? '<i class="fas fa-bell-slash"></i>'
          : '<i class="fas fa-bell"></i>';
        button.title = sub ? "Se désabonner" : "S'abonner";
        button.className = sub ? "cal-sub-btn-active" : "cal-sub-btn";

        // Update cache
        invalidateCache();

        // Show toast
        showToast(
          sub
            ? `Abonné à « ${result.event_title} »`
            : `Désabonné de « ${result.event_title} »`,
          sub ? "success" : "info",
        );

        // Reload current month to update indicators
        setTimeout(() => {
          loadMonth(state.currentMonth, state.currentYear, null);
        }, 300);
      } else {
        showToast(result.error || "Erreur", "error");
      }
    } catch (err) {
      console.error("Subscription error:", err);
      showToast("Erreur de connexion", "error");
    } finally {
      button.disabled = false;
      button.classList.remove("cal-sub-loading");
    }
  }

  function invalidateCache() {
    state.cache = {};
  }

  // =========================================================================
  // REMINDERS
  // =========================================================================
  function showReminders(reminders) {
    if (!els.reminders || reminders.length === 0) return;

    const today = new Date();
    today.setHours(0, 0, 0, 0);

    reminders.forEach((reminder, idx) => {
      const eventDate = new Date(reminder.date_ev);
      const diffTime = eventDate - today;
      const daysLeft = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
      if (daysLeft < 0) return;

      setTimeout(
        () => {
          const div = document.createElement("div");
          div.className = "cal-reminder";
          div.innerHTML = `
                    <div class="cal-reminder-icon"><i class="fas fa-bell"></i></div>
                    <div class="cal-reminder-text">
                        <strong>Rappel</strong>
                        <span>« ${escapeHtml(reminder.titre)} » dans <strong>${daysLeft} jour${daysLeft > 1 ? "s" : ""}</strong></span>
                    </div>
                    <button class="cal-reminder-close" aria-label="Fermer">&times;</button>
                `;
          div
            .querySelector(".cal-reminder-close")
            .addEventListener("click", () => {
              div.classList.add("cal-reminder-exit");
              setTimeout(() => div.remove(), 400);
            });
          els.reminders.appendChild(div);

          // Auto-dismiss after 8s
          setTimeout(
            () => {
              if (div.parentNode) {
                div.classList.add("cal-reminder-exit");
                setTimeout(() => div.remove(), 400);
              }
            },
            8000 + idx * 2000,
          );
        },
        500 + idx * 600,
      );
    });
  }

  // =========================================================================
  // TOAST
  // =========================================================================
  function showToast(message, type) {
    if (!els.toast) return;
    els.toast.textContent = message;
    els.toast.className = `cal-toast cal-toast-${type} visible`;
    setTimeout(() => {
      els.toast.classList.remove("visible");
    }, 3500);
  }

  // =========================================================================
  // KEYBOARD NAVIGATION
  // =========================================================================
  function handleKeyboard(e) {
    // Escape to close modal
    if (e.key === "Escape") {
      if (els.modalOverlay.classList.contains("visible")) {
        closeModal();
      }
    }
    // Arrow keys for month navigation (only when modal is not open)
    if (!els.modalOverlay.classList.contains("visible")) {
      if (e.key === "ArrowLeft") {
        navigatePrev();
      } else if (e.key === "ArrowRight") {
        navigateNext();
      }
    }
  }

  // =========================================================================
  // NAVIGATION
  // =========================================================================
  function navigatePrev() {
    if (!state.data) return;
    loadMonth(state.data.prev.month, state.data.prev.year, "prev");
  }

  function navigateNext() {
    if (!state.data) return;
    loadMonth(state.data.next.month, state.data.next.year, "next");
  }

  function navigateToday() {
    const now = new Date();
    loadMonth(now.getMonth() + 1, now.getFullYear(), null);
  }

  // =========================================================================
  // TOUCH SWIPE SUPPORT
  // =========================================================================
  function initSwipe() {
    let startX = 0;
    let startY = 0;

    els.gridWrapper.addEventListener(
      "touchstart",
      (e) => {
        startX = e.touches[0].clientX;
        startY = e.touches[0].clientY;
      },
      { passive: true },
    );

    els.gridWrapper.addEventListener(
      "touchend",
      (e) => {
        const endX = e.changedTouches[0].clientX;
        const endY = e.changedTouches[0].clientY;
        const diffX = endX - startX;
        const diffY = endY - startY;

        // Only trigger if horizontal swipe > 60px and more horizontal than vertical
        if (Math.abs(diffX) > 60 && Math.abs(diffX) > Math.abs(diffY) * 1.5) {
          if (diffX < 0) {
            navigateNext();
          } else {
            navigatePrev();
          }
        }
      },
      { passive: true },
    );
  }

  // =========================================================================
  // INIT
  // =========================================================================
  function init() {
    cacheDom();
    if (!els.app) return;

    // Navigation buttons
    els.prevBtn.addEventListener("click", navigatePrev);
    els.nextBtn.addEventListener("click", navigateNext);
    els.todayBtn.addEventListener("click", navigateToday);

    // Modal close
    els.modalClose.addEventListener("click", closeModal);
    els.modalOverlay.addEventListener("click", (e) => {
      if (e.target === els.modalOverlay) closeModal();
    });

    // Keyboard
    document.addEventListener("keydown", handleKeyboard);

    // Filters
    initFilters();

    // Touch swipe
    initSwipe();

    // Load initial month
    loadMonth(state.currentMonth, state.currentYear, null);
  }

  // =========================================================================
  // PUBLIC API (for error retry)
  // =========================================================================
  window.CalendarApp = {
    reload: () => loadMonth(state.currentMonth, state.currentYear, null),
  };

  // Start
  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", init);
  } else {
    init();
  }
})();
