/* ============================================================
   JOKER UNIVERSITY CLUB — events.js
   Events page logic: render, filter, sort, add (admin)
   ============================================================ */

document.addEventListener('DOMContentLoaded', () => {

  let sortAsc = false;
  let activeFilter = 'all';
  let searchQuery = '';
  let dateFilter = '';

  /* ---- Show admin button if admin ---- */
  if (JokerAuth.isAdmin()) {
    const btn = document.getElementById('admin-add-btn');
    if (btn) btn.style.display = 'inline-flex';
  }

  /* ---- Render events grid ---- */
  function renderEvents() {
    const grid = document.getElementById('events-grid');
    if (!grid) return;

    let list = [...window.EVENTS_DATA];

    // Filter
    if (activeFilter !== 'all') list = list.filter(e => e.type === activeFilter);

    // Search
    if (searchQuery) {
      const q = searchQuery.toLowerCase();
      list = list.filter(e =>
        e.title.toLowerCase().includes(q) ||
        e.desc.toLowerCase().includes(q) ||
        e.location.toLowerCase().includes(q)
      );
    }

    // Date filter
    if (dateFilter) list = list.filter(e => e.date >= dateFilter);

    // Sort
    list.sort((a, b) => sortAsc
      ? a.date.localeCompare(b.date)
      : b.date.localeCompare(a.date)
    );

    if (list.length === 0) {
      grid.innerHTML = `<div class="empty-state" style="grid-column:1/-1;">
        <div class="empty-state-icon">🔍</div>
        <h3>Aucun événement trouvé</h3>
        <p>Essayez un autre filtre ou une autre date.</p>
      </div>`;
      return;
    }

    grid.innerHTML = list.map(ev => buildEventCard(ev, true)).join('');
  }

  /* ---- Initial render ---- */
  renderEvents();

  /* ---- Filters ---- */
  document.querySelectorAll('.filter-pill').forEach(btn => {
    btn.addEventListener('click', () => {
      document.querySelectorAll('.filter-pill').forEach(b => b.classList.remove('active'));
      btn.classList.add('active');
      activeFilter = btn.dataset.filter;
      renderEvents();
    });
  });

  /* ---- Search ---- */
  document.getElementById('ev-search')?.addEventListener('input', (e) => {
    searchQuery = e.target.value;
    renderEvents();
  });

  /* ---- Date filter ---- */
  document.getElementById('ev-date-filter')?.addEventListener('change', (e) => {
    dateFilter = e.target.value;
    renderEvents();
  });

  /* ---- Sort ---- */
  document.getElementById('sort-btn')?.addEventListener('click', (btn) => {
    sortAsc = !sortAsc;
    document.getElementById('sort-btn').textContent = sortAsc ? '↑ Plus ancien' : '↓ Plus récent';
    renderEvents();
  });

  /* ---- Admin: Add event ---- */
  document.getElementById('admin-add-btn')?.addEventListener('click', () => {
    JokerModal.open('modal-add-event');
  });

  document.getElementById('add-event-form')?.addEventListener('submit', (e) => {
    e.preventDefault();

    const title    = document.getElementById('ae-title').value.trim();
    const date     = document.getElementById('ae-date').value;
    const time     = document.getElementById('ae-time').value || '18:00';
    const type     = document.getElementById('ae-type').value;
    const access   = document.getElementById('ae-access').value;
    const location = document.getElementById('ae-location').value.trim() || 'Campus Joker';
    const desc     = document.getElementById('ae-desc').value.trim() || 'Événement organisé par le Joker Club.';
    const emoji    = document.getElementById('ae-emoji').value || '🎯';
    const maxPart  = parseInt(document.getElementById('ae-max').value) || 50;

    if (!title || !date) {
      JokerToast.show('⚠️ Veuillez remplir les champs obligatoires.', 'warning');
      return;
    }

    const newEvent = {
      id: Date.now(),
      title, type, access, date, time,
      location, desc, emoji,
      participants: 0,
      maxParticipants: maxPart,
    };

    window.EVENTS_DATA.unshift(newEvent);

    JokerModal.close('modal-add-event');
    document.getElementById('add-event-form').reset();
    document.getElementById('ae-emoji').value = '🎯';

    renderEvents();
    JokerToast.show(`✦ "${title}" publié avec succès !`, 'success');
  });

  /* ---- Inscription modal ---- */
  window.openInscription = function(eventId) {
    const ev = window.EVENTS_DATA.find(e => e.id === eventId);
    if (!ev) return;

    document.getElementById('inscription-event-name').textContent = ev.title;
    document.getElementById('inscription-form-wrap').style.display = 'block';
    document.getElementById('inscription-success').classList.remove('show');
    document.getElementById('inscription-form').dataset.eventId = eventId;
    document.getElementById('inscription-form').reset();
    document.querySelectorAll('.form-error').forEach(el => el.classList.remove('show'));

    JokerModal.open('modal-inscription');
  };

  window.handlePrivateEvent = function(eventId) {
    const user = JokerAuth.getUser();
    if (!user) {
      JokerToast.show('🔒 Événement membres uniquement. Connectez-vous.', 'warning');
      setTimeout(() => JokerNav.navigateTo('login'), 1400);
    } else {
      JokerToast.show('✓ Participation confirmée en tant que membre !', 'success');
      const ev = window.EVENTS_DATA.find(e => e.id === eventId);
      if (ev) ev.participants++;
      renderEvents();
    }
  };

  document.getElementById('inscription-form')?.addEventListener('submit', (e) => {
    e.preventDefault();
    let valid = true;

    const name  = document.getElementById('insc-name');
    const email = document.getElementById('insc-email');

    if (!name.value.trim()) {
      document.getElementById('err-name').classList.add('show'); valid = false;
    } else document.getElementById('err-name').classList.remove('show');

    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.value)) {
      document.getElementById('err-email').classList.add('show'); valid = false;
    } else document.getElementById('err-email').classList.remove('show');

    if (!valid) return;

    const eventId = parseInt(e.target.dataset.eventId);
    const ev = window.EVENTS_DATA.find(ev => ev.id === eventId);
    if (ev) ev.participants++;

    document.getElementById('inscription-form-wrap').style.display = 'none';
    document.getElementById('inscription-success').classList.add('show');
    renderEvents();

    JokerToast.show('✅ Demande envoyée !', 'success');
    setTimeout(() => JokerModal.close('modal-inscription'), 2800);
  });

});
