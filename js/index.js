/* ============================================================
   JOKER UNIVERSITY CLUB — index.js
   Home page interactions
   ============================================================ */

/* ---- Shared event data (used across pages) ---- */
const EVENTS_DATA = [
  {
    id: 1, title: 'Mastering UX Strategies for AI',
    type: 'formation', access: 'public',
    date: '2024-10-24', time: '18:00',
    location: 'Innovation Lab, Salle 402',
    desc: 'Workshop intensif sur le design d\'expérience utilisateur appliqué à l\'IA.',
    emoji: '🎨', participants: 42, maxParticipants: 60,
  },
  {
    id: 2, title: 'Elite Networkers Gala',
    type: 'prive', access: 'members',
    date: '2024-11-02', time: '20:00',
    location: 'Sky Garden Lounge',
    desc: 'Soirée networking exclusive réservée aux membres actifs du club.',
    emoji: '🥂', participants: 89, maxParticipants: 100,
  },
  {
    id: 3, title: 'Joker Tech Carnival',
    type: 'public', access: 'public',
    date: '2024-11-15', time: '14:00',
    location: 'University Square',
    desc: 'Festival technologique ouvert à tous avec démos, concours et keynotes.',
    emoji: '🚀', participants: 156, maxParticipants: 300,
  },
  {
    id: 4, title: 'Entrepreneurship Bootcamp',
    type: 'formation', access: 'public',
    date: '2024-12-05', time: '10:00',
    location: 'Business Wing, Salle 12',
    desc: 'Deux jours intensifs pour transformer vos idées en projets concrets.',
    emoji: '💡', participants: 38, maxParticipants: 50,
  },
  {
    id: 5, title: 'Soirée Cinéma Club',
    type: 'public', access: 'public',
    date: '2024-12-10', time: '19:30',
    location: 'Amphithéâtre B',
    desc: 'Projection suivie d\'un débat autour du cinéma indépendant.',
    emoji: '🎬', participants: 67, maxParticipants: 120,
  },
  {
    id: 6, title: 'Leadership Masterclass',
    type: 'formation', access: 'members',
    date: '2024-12-18', time: '09:00',
    location: 'Salle Conférence C',
    desc: 'Session exclusive avec un DG alumni sur la vision et le leadership.',
    emoji: '🏆', participants: 24, maxParticipants: 30,
  },
];

// Make globally accessible
window.EVENTS_DATA = EVENTS_DATA;

/* ---- Build event card HTML ---- */
function buildEventCard(ev, showInscribeBtn = true) {
  const isFull = ev.participants >= ev.maxParticipants;
  const isPrivate = ev.access === 'members';
  const pct = Math.round((ev.participants / ev.maxParticipants) * 100);

  const badgeMap = {
    formation: 'badge-primary',
    public:    'badge-success',
    prive:     'badge-accent',
  };
  const typeLabel = { formation: 'Formation', public: 'Public', prive: 'Privé' };

  let btnHTML = '';
  if (showInscribeBtn) {
    if (isPrivate) {
      btnHTML = `<button class="btn btn-outline btn-sm" onclick="handlePrivateEvent(${ev.id})">🔒 Membres</button>`;
    } else if (isFull) {
      btnHTML = `<button class="btn btn-ghost btn-sm" disabled>Complet</button>`;
    } else {
      btnHTML = `<button class="btn btn-primary btn-sm" onclick="openInscription(${ev.id})">S'inscrire</button>`;
    }
  }

  return `
    <div class="event-card" data-type="${ev.type}" data-id="${ev.id}" data-date="${ev.date}">
      <div class="event-img">
        <span class="event-img-emoji">${ev.emoji}</span>
        <div class="event-img-overlay"></div>
        <div class="event-img-badge">
          <span class="badge ${badgeMap[ev.type] || 'badge-muted'}">${typeLabel[ev.type]}</span>
        </div>
      </div>
      <div class="event-body">
        <div class="event-meta">
          <span>📅 ${formatDateFR(ev.date)} · ${ev.time}</span>
          <span>📍 ${ev.location}</span>
        </div>
        <h3 class="event-title">${ev.title}</h3>
        <p class="event-desc">${ev.desc}</p>
        <div class="event-footer">
          <div class="event-participants">
            👥 <span class="ev-count-${ev.id}">${ev.participants}</span> / ${ev.maxParticipants}
            <div style="margin-left:0.5rem;width:50px;height:4px;background:var(--border);border-radius:4px;overflow:hidden;">
              <div style="width:${pct}%;height:100%;background:var(--primary-light);border-radius:4px;"></div>
            </div>
          </div>
          ${btnHTML}
        </div>
      </div>
    </div>`;
}

function formatDateFR(dateStr) {
  if (!dateStr) return '';
  const d = new Date(dateStr + 'T00:00:00');
  return d.toLocaleDateString('fr-FR', { day: '2-digit', month: 'short', year: 'numeric' });
}

/* ---- Render home events (3 upcoming) ---- */
function renderHomeEvents(filter = 'all', search = '') {
  const grid = document.getElementById('home-events-grid');
  if (!grid) return;

  const user = JokerAuth.getUser();
  let list = EVENTS_DATA.slice(0, 6);

  if (filter !== 'all') {
    list = list.filter(e => e.type === filter);
  }
  if (search) {
    const q = search.toLowerCase();
    list = list.filter(e => e.title.toLowerCase().includes(q) || e.desc.toLowerCase().includes(q));
  }

  if (list.length === 0) {
    grid.innerHTML = `<div class="empty-state" style="grid-column:1/-1;">
      <div class="empty-state-icon">🔍</div>
      <h3>Aucun événement trouvé</h3>
      <p>Essayez un autre filtre ou mot-clé.</p>
    </div>`;
    return;
  }

  grid.innerHTML = list.map(ev => buildEventCard(ev)).join('');
}

/* ---- Open inscription modal ---- */
function openInscription(eventId) {
  const ev = EVENTS_DATA.find(e => e.id === eventId);
  if (!ev) return;

  document.getElementById('inscription-event-name').textContent = ev.title;
  document.getElementById('inscription-form-wrap').style.display = 'block';
  document.getElementById('inscription-success').classList.remove('show');
  document.getElementById('inscription-form').dataset.eventId = eventId;

  // Reset form
  document.getElementById('inscription-form').reset();
  document.querySelectorAll('.form-error').forEach(e => e.classList.remove('show'));

  JokerModal.open('modal-inscription');
}
window.openInscription = openInscription;

function handlePrivateEvent(eventId) {
  const user = JokerAuth.getUser();
  if (!user) {
    JokerToast.show('🔒 Événement réservé aux membres. Connectez-vous d\'abord.', 'warning');
    setTimeout(() => JokerNav.navigateTo('login'), 1400);
  } else {
    JokerToast.show('✓ Vous êtes membre, participation confirmée !', 'success');
    const ev = EVENTS_DATA.find(e => e.id === eventId);
    if (ev) ev.participants++;
    renderHomeEvents();
  }
}
window.handlePrivateEvent = handlePrivateEvent;

/* ---- Counter animation ---- */
function animateCounter(el) {
  const target = parseFloat(el.dataset.counter);
  const suffix = el.dataset.suffix || '';
  const duration = 1600;
  const start = performance.now();
  const update = (now) => {
    const elapsed = now - start;
    const progress = Math.min(elapsed / duration, 1);
    const eased = 1 - Math.pow(1 - progress, 3);
    const value = target % 1 !== 0
      ? (eased * target).toFixed(1)
      : Math.floor(eased * target);
    el.textContent = value + suffix;
    if (progress < 1) requestAnimationFrame(update);
  };
  requestAnimationFrame(update);
}

/* ---- DOMContentLoaded ---- */
document.addEventListener('DOMContentLoaded', () => {
  renderHomeEvents();

  /* Counters via IntersectionObserver */
  const counters = document.querySelectorAll('[data-counter]');
  const obs = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        animateCounter(entry.target);
        obs.unobserve(entry.target);
      }
    });
  }, { threshold: 0.5 });
  counters.forEach(el => obs.observe(el));

  /* Filter pills */
  document.querySelectorAll('.filter-pill').forEach(btn => {
    btn.addEventListener('click', () => {
      document.querySelectorAll('.filter-pill').forEach(b => b.classList.remove('active'));
      btn.classList.add('active');
      const search = document.getElementById('home-search')?.value || '';
      renderHomeEvents(btn.dataset.filter, search);
    });
  });

  /* Search */
  document.getElementById('home-search')?.addEventListener('input', (e) => {
    const activeFilter = document.querySelector('.filter-pill.active')?.dataset.filter || 'all';
    renderHomeEvents(activeFilter, e.target.value);
  });

  /* CTA buttons */
  document.getElementById('cta-join')?.addEventListener('click', () => JokerNav.navigateTo('login'));
  document.getElementById('cta-events')?.addEventListener('click', () => JokerNav.navigateTo('events'));
  document.getElementById('see-all-btn')?.addEventListener('click', () => JokerNav.navigateTo('events'));

  /* Inscription form submit */
  document.getElementById('inscription-form')?.addEventListener('submit', (e) => {
    e.preventDefault();
    let valid = true;

    const name  = document.getElementById('insc-name');
    const email = document.getElementById('insc-email');

    if (!name.value.trim()) {
      document.getElementById('err-name').classList.add('show');
      valid = false;
    } else {
      document.getElementById('err-name').classList.remove('show');
    }

    const emailRx = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRx.test(email.value)) {
      document.getElementById('err-email').classList.add('show');
      valid = false;
    } else {
      document.getElementById('err-email').classList.remove('show');
    }

    if (!valid) return;

    const eventId = parseInt(e.target.dataset.eventId);
    const ev = EVENTS_DATA.find(ev => ev.id === eventId);
    if (ev) ev.participants++;

    // Show success
    document.getElementById('inscription-form-wrap').style.display = 'none';
    document.getElementById('inscription-success').classList.add('show');
    renderHomeEvents();

    JokerToast.show('✅ Demande envoyée avec succès !', 'success');

    setTimeout(() => JokerModal.close('modal-inscription'), 2800);
  });
});
