/* ============================================================
   JOKER UNIVERSITY CLUB — reunions.js
   Meetings page: render, create, attendance
   ============================================================ */

const MEETINGS_DATA = [
  {
    id: 1, title: 'Réunion Bureau Mensuelle', type: 'bureau',
    date: '2024-10-28', time: '18:00',
    location: 'Salle B12', agenda: 'Bilan financier, préparation Gala, points divers.',
    emoji: '🏛️', upcoming: true,
  },
  {
    id: 2, title: 'Assemblée Générale', type: 'generale',
    date: '2024-11-10', time: '14:00',
    location: 'Amphithéâtre A', agenda: 'Élection nouveaux membres bureau, vote budget 2025.',
    emoji: '🗳️', upcoming: true,
  },
  {
    id: 3, title: 'Commission Communication', type: 'commission',
    date: '2024-11-18', time: '17:30',
    location: 'Meet.google.com/abc-xyz', agenda: 'Stratégie réseaux sociaux, calendrier publications.',
    emoji: '📢', upcoming: true,
  },
  {
    id: 4, title: 'Point Stratégique Q3', type: 'bureau',
    date: '2024-09-15', time: '18:00',
    location: 'Salle B12', agenda: 'Bilan événements été, recrutement automne.',
    emoji: '📊', upcoming: false,
  },
  {
    id: 5, title: 'Formation interne leadership', type: 'online',
    date: '2024-09-28', time: '10:00',
    location: 'Zoom link', agenda: 'Gestion d\'équipe, communication non-violente.',
    emoji: '🎤', upcoming: false,
  },
];

const MEMBERS_PRESENCE = [
  { name: 'Alex Admin',    present: true  },
  { name: 'Marie Membre',  present: false },
  { name: 'Thomas Remy',   present: true  },
  { name: 'Léa Martin',    present: false },
  { name: 'Jules Dupont',  present: false },
  { name: 'Camille Vidal', present: true  },
];

document.addEventListener('DOMContentLoaded', () => {

  /* ---- Auth guard (members only for this page) ---- */
  const user = JokerAuth.getUser();
  if (!user) {
    JokerToast.show('🔒 Connectez-vous pour accéder aux réunions.', 'warning');
    setTimeout(() => JokerNav.navigateTo('login'), 1400);
    return;
  }

  /* ---- Tabs ---- */
  document.querySelectorAll('.tab-btn').forEach(btn => {
    btn.addEventListener('click', () => {
      document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
      document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
      btn.classList.add('active');
      document.getElementById(btn.dataset.tab).classList.add('active');
    });
  });

  /* ---- Render meetings ---- */
  function buildMeetingCard(m) {
    const typeLabel = { bureau: 'Bureau', generale: 'Générale', commission: 'Commission', online: 'En ligne' };
    const typeClass = { bureau: 'badge-primary', generale: 'badge-accent', commission: 'badge-success', online: 'badge-muted' };
    const isOnline = m.type === 'online' || m.location.includes('meet') || m.location.includes('Zoom');

    return `
      <div class="meeting-card anim-up">
        <div class="meeting-card-header">
          <div class="meeting-type-icon">${m.emoji}</div>
          <span class="badge ${typeClass[m.type] || 'badge-muted'}">${typeLabel[m.type] || m.type}</span>
        </div>
        <div class="meeting-title">${m.title}</div>
        <div class="meeting-meta" style="margin-bottom:0.875rem;">
          📅 ${formatDateFR(m.date)} · ${m.time}<br>
          📍 ${m.location}
        </div>
        <p style="font-size:0.8125rem;color:var(--text-muted);line-height:1.6;margin-bottom:1rem;">${m.agenda}</p>
        <div style="display:flex;gap:0.5rem;flex-wrap:wrap;">
          ${isOnline
            ? `<button class="btn btn-primary btn-sm" onclick="joinMeeting(${m.id})">🎥 Rejoindre</button>`
            : `<button class="btn btn-outline btn-sm" onclick="JokerToast.show('📍 Réunion en présentiel : ${m.location}','info')">📍 Voir lieu</button>`
          }
          <button class="btn btn-ghost btn-sm" onclick="deleteMeeting(${m.id})">🗑</button>
        </div>
      </div>`;
  }

  function renderMeetings() {
    const upcoming = document.getElementById('meetings-grid');
    const past     = document.getElementById('meetings-past-grid');
    if (upcoming) {
      const list = MEETINGS_DATA.filter(m => m.upcoming);
      upcoming.innerHTML = list.length
        ? list.map(buildMeetingCard).join('')
        : `<div class="empty-state"><div class="empty-state-icon">📅</div><h3>Aucune réunion planifiée</h3></div>`;
    }
    if (past) {
      const list = MEETINGS_DATA.filter(m => !m.upcoming);
      past.innerHTML = list.length
        ? list.map(buildMeetingCard).join('')
        : `<div class="empty-state"><div class="empty-state-icon">📁</div><h3>Aucune réunion passée</h3></div>`;
    }
  }
  renderMeetings();

  window.joinMeeting = function(id) {
    const m = MEETINGS_DATA.find(m => m.id === id);
    JokerToast.show(`🎥 Lancement de la réunion : ${m ? m.title : ''}…`, 'info');
  };

  window.deleteMeeting = function(id) {
    if (!JokerAuth.isAdmin()) {
      JokerToast.show('🔒 Seul un admin peut supprimer.', 'warning');
      return;
    }
    const idx = MEETINGS_DATA.findIndex(m => m.id === id);
    if (idx === -1) return;
    const title = MEETINGS_DATA[idx].title;
    MEETINGS_DATA.splice(idx, 1);
    renderMeetings();
    JokerToast.show(`🗑 "${title}" supprimée.`, 'warning');
  };

  /* ---- Attendance ---- */
  function updateAttendanceCount() {
    const total   = MEMBERS_PRESENCE.length;
    const present = MEMBERS_PRESENCE.filter(m => m.present).length;
    document.getElementById('attendance-count').textContent = `${present} / ${total} présents`;
  }

  function renderAttendance() {
    const list = document.getElementById('attendance-list');
    if (!list) return;
    list.innerHTML = MEMBERS_PRESENCE.map((m, i) => `
      <div class="attendance-row ${m.present ? 'present' : ''}" id="att-row-${i}">
        <input type="checkbox" class="attendance-check" id="att-${i}"
               ${m.present ? 'checked' : ''}
               onchange="toggleAttendance(${i})"/>
        <label for="att-${i}" style="cursor:pointer;flex:1;font-weight:500;font-size:0.9375rem;">${m.name}</label>
        <span style="font-size:0.75rem;color:${m.present ? '#34d399' : 'var(--text-faint)'};">
          ${m.present ? '✅ Présent' : '—'}
        </span>
      </div>`).join('');
    updateAttendanceCount();
  }
  renderAttendance();

  window.toggleAttendance = function(index) {
    MEMBERS_PRESENCE[index].present = !MEMBERS_PRESENCE[index].present;
    renderAttendance();
  };

  document.getElementById('save-attendance-btn')?.addEventListener('click', () => {
    const total   = MEMBERS_PRESENCE.length;
    const present = MEMBERS_PRESENCE.filter(m => m.present).length;
    JokerToast.show(`✅ Présence sauvegardée : ${present}/${total} membres présents.`, 'success');
  });

  /* ---- Create meeting ---- */
  document.getElementById('btn-create-meeting')?.addEventListener('click', () => {
    if (!JokerAuth.isAdmin() && !JokerAuth.isMember()) {
      JokerToast.show('🔒 Connexion requise.', 'warning');
      return;
    }
    JokerModal.open('modal-create-meeting');
  });

  document.getElementById('create-meeting-form')?.addEventListener('submit', (e) => {
    e.preventDefault();
    const title    = document.getElementById('cm-title').value.trim();
    const date     = document.getElementById('cm-date').value;
    const time     = document.getElementById('cm-time').value || '18:00';
    const type     = document.getElementById('cm-type').value;
    const location = document.getElementById('cm-location').value.trim() || 'Campus Joker';
    const agenda   = document.getElementById('cm-agenda').value.trim() || 'Ordre du jour à définir.';
    if (!title || !date) return;

    const emojis = { bureau: '🏛️', generale: '🗳️', commission: '📢', online: '💻' };
    MEETINGS_DATA.unshift({
      id: Date.now(), title, type, date, time, location, agenda,
      emoji: emojis[type] || '📅', upcoming: true,
    });

    renderMeetings();
    JokerModal.close('modal-create-meeting');
    document.getElementById('create-meeting-form').reset();
    JokerToast.show(`📅 "${title}" créée et ajoutée à la grille !`, 'success');
  });

  /* ---- Declare absence ---- */
  document.getElementById('btn-declare-absence')?.addEventListener('click', () => {
    const name = prompt('Votre nom pour déclarer une absence :');
    if (name && name.trim()) {
      JokerToast.show(`📩 Absence de « ${name.trim()} » déclarée. Le bureau a été notifié.`, 'info');
    }
  });

});

function formatDateFR(dateStr) {
  if (!dateStr) return '';
  const d = new Date(dateStr + 'T00:00:00');
  return d.toLocaleDateString('fr-FR', { day: '2-digit', month: 'short', year: 'numeric' });
}
