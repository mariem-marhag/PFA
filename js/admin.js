/* ============================================================
   JOKER UNIVERSITY CLUB — admin.js
   Admin dashboard: members, tasks, events, formations
   ============================================================ */

/* ---- Sample data ---- */
const MEMBERS_DATA = [
  { id: 1, name: 'Alex Admin', email: 'admin@joker.edu',   role: 'admin',  status: 'actif' },
  { id: 2, name: 'Marie Membre', email: 'membre@joker.edu', role: 'member', status: 'actif' },
  { id: 3, name: 'Thomas Remy',  email: 'thomas@univ.fr',   role: 'bureau', status: 'actif' },
  { id: 4, name: 'Léa Martin',   email: 'lea@univ.fr',      role: 'member', status: 'actif' },
  { id: 5, name: 'Jules Dupont', email: 'jules@univ.fr',    role: 'member', status: 'inactif' },
];

const TASKS_DATA = [
  { id: 1, title: 'Finaliser budget Gala Annuel', priority: 'high', due: '2024-10-24', assignee: 'Bureau Exécutif', done: false },
  { id: 2, title: 'Mise à jour du site web',       priority: 'med',  due: '2024-10-30', assignee: 'Thomas R.',       done: false },
  { id: 3, title: 'Campagne recrutement réseaux',  priority: 'med',  due: '2024-11-05', assignee: 'Léa M.',          done: false },
  { id: 4, title: 'Commander les sweats club',     priority: 'low',  due: '2024-10-20', assignee: 'Thomas R.',       done: true  },
];

const FORMATIONS_DATA = [
  { id: 1, title: 'Leadership Masterclass', trainer: 'Dr. Salim Bouzit', date: '2024-12-18', duration: '1 jour',   emoji: '🏆' },
  { id: 2, title: 'Communication Efficace',  trainer: 'Coach Amira H.',   date: '2024-11-20', duration: '2 jours',  emoji: '🎤' },
  { id: 3, title: 'Initiation VC & Startups',trainer: 'Alumni Panel',     date: '2025-01-10', duration: '3 heures', emoji: '💰' },
];

document.addEventListener('DOMContentLoaded', () => {

  /* ---- Auth guard ---- */
  const user = JokerAuth.getUser();
  if (!user || user.role !== 'admin') {
    JokerToast.show('🔒 Accès réservé aux administrateurs.', 'error');
    setTimeout(() => JokerNav.navigateTo('login'), 1200);
    return;
  }
  document.getElementById('admin-name').textContent = user.name;

  /* ---- Update stats ---- */
  function updateStats() {
    document.getElementById('stat-members').textContent  = MEMBERS_DATA.filter(m => m.status === 'actif').length;
    document.getElementById('stat-events').textContent   = (window.EVENTS_DATA || []).length;
    document.getElementById('stat-tasks').textContent    = TASKS_DATA.filter(t => !t.done).length;
  }
  updateStats();

  /* ---- Tabs ---- */
  document.querySelectorAll('.tab-btn').forEach(btn => {
    btn.addEventListener('click', () => {
      document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
      document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
      btn.classList.add('active');
      document.getElementById(btn.dataset.tab).classList.add('active');
    });
  });

  /* ==== MEMBERS ==== */
  function renderMembers() {
    const tbody = document.getElementById('members-tbody');
    if (!tbody) return;
    tbody.innerHTML = MEMBERS_DATA.map(m => {
      const initials = m.name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0,2);
      const roleLabel = { admin: 'Admin', bureau: 'Bureau', member: 'Membre' }[m.role] || m.role;
      const badgeClass = { admin: 'badge-accent', bureau: 'badge-primary', member: 'badge-muted' }[m.role];
      const statusBadge = m.status === 'actif'
        ? '<span class="badge badge-success">Actif</span>'
        : '<span class="badge badge-muted">Inactif</span>';
      return `<tr>
        <td><span class="member-avatar">${initials}</span><span class="member-name">${m.name}</span></td>
        <td>${m.email}</td>
        <td><span class="badge ${badgeClass}">${roleLabel}</span></td>
        <td>${statusBadge}</td>
        <td style="display:flex;gap:0.5rem;">
          <button class="btn-icon" onclick="toggleMemberStatus(${m.id})" title="Changer statut">⚡</button>
          <button class="btn-icon" onclick="deleteMember(${m.id})" title="Supprimer">🗑</button>
        </td>
      </tr>`;
    }).join('');
  }
  renderMembers();

  window.toggleMemberStatus = function(id) {
    const m = MEMBERS_DATA.find(m => m.id === id);
    if (!m) return;
    m.status = m.status === 'actif' ? 'inactif' : 'actif';
    renderMembers();
    updateStats();
    JokerToast.show(`⚡ ${m.name} → ${m.status}`, 'info');
  };

  window.deleteMember = function(id) {
    const idx = MEMBERS_DATA.findIndex(m => m.id === id);
    if (idx === -1) return;
    const name = MEMBERS_DATA[idx].name;
    if (!confirm(`Supprimer ${name} ?`)) return;
    MEMBERS_DATA.splice(idx, 1);
    renderMembers();
    updateStats();
    JokerToast.show(`🗑 ${name} supprimé.`, 'warning');
  };

  document.getElementById('btn-add-member')?.addEventListener('click', () => {
    JokerModal.open('modal-add-member');
  });

  document.getElementById('add-member-form')?.addEventListener('submit', (e) => {
    e.preventDefault();
    const name   = document.getElementById('am-name').value.trim();
    const email  = document.getElementById('am-email').value.trim();
    const role   = document.getElementById('am-role').value;
    const status = document.getElementById('am-status').value;

    if (!name || !email) return;

    MEMBERS_DATA.push({
      id: Date.now(), name, email, role, status,
    });
    renderMembers();
    updateStats();
    JokerModal.close('modal-add-member');
    document.getElementById('add-member-form').reset();
    JokerToast.show(`✅ ${name} ajouté(e) !`, 'success');
  });

  /* ==== TASKS ==== */
  function renderTasks() {
    const list = document.getElementById('admin-tasks-list');
    if (!list) return;

    if (TASKS_DATA.length === 0) {
      list.innerHTML = `<div class="empty-state"><div class="empty-state-icon">📋</div><h3>Aucune tâche</h3></div>`;
      return;
    }

    list.innerHTML = TASKS_DATA.map(t => {
      const pLabel = { high: 'Haute 🔴', med: 'Moyenne 🟡', low: 'Basse 🟢' }[t.priority];
      return `
        <div class="task-item ${t.done ? 'done' : ''}" id="task-${t.id}">
          <div class="task-priority-bar priority-${t.priority}"></div>
          <div class="task-check ${t.done ? 'checked' : ''}" onclick="toggleTask(${t.id})"></div>
          <div class="task-title">${t.title}</div>
          <div style="display:flex;gap:0.75rem;align-items:center;margin-left:auto;">
            <span class="badge badge-muted" style="font-size:0.65rem;">${pLabel}</span>
            <span class="task-due">${t.due ? formatDateFR(t.due) : ''}</span>
            <span style="font-size:0.8125rem;color:var(--text-muted);">→ ${t.assignee}</span>
            <button class="btn-icon" onclick="deleteTask(${t.id})">🗑</button>
          </div>
        </div>`;
    }).join('');
  }
  renderTasks();

  window.toggleTask = function(id) {
    const t = TASKS_DATA.find(t => t.id === id);
    if (!t) return;
    t.done = !t.done;
    renderTasks();
    updateStats();
    JokerToast.show(t.done ? '✅ Tâche terminée !' : '↩ Tâche rouverte.', t.done ? 'success' : 'info');
  };

  window.deleteTask = function(id) {
    const idx = TASKS_DATA.findIndex(t => t.id === id);
    if (idx === -1) return;
    TASKS_DATA.splice(idx, 1);
    renderTasks();
    updateStats();
    JokerToast.show('🗑 Tâche supprimée.', 'warning');
  };

  document.getElementById('btn-add-task')?.addEventListener('click', () => JokerModal.open('modal-add-task'));

  document.getElementById('add-task-form')?.addEventListener('submit', (e) => {
    e.preventDefault();
    const title    = document.getElementById('at-title').value.trim();
    const priority = document.getElementById('at-priority').value;
    const due      = document.getElementById('at-due').value;
    const assignee = document.getElementById('at-assignee').value;

    if (!title) return;

    TASKS_DATA.unshift({ id: Date.now(), title, priority, due, assignee, done: false });
    renderTasks();
    updateStats();
    JokerModal.close('modal-add-task');
    document.getElementById('add-task-form').reset();
    JokerToast.show(`🚀 "${title}" lancée !`, 'success');
  });

  /* ==== EVENTS (admin tab) ==== */
  function renderAdminEvents() {
    const grid = document.getElementById('admin-events-grid');
    if (!grid || !window.EVENTS_DATA) return;
    grid.innerHTML = window.EVENTS_DATA.map(ev => {
      const card = buildEventCard(ev, false);
      // inject delete button via replace
      return card.replace(
        '</div>\n    </div>',
        `<button class="btn btn-danger btn-sm" style="margin-top:0.5rem;width:100%;" onclick="deleteEvent(${ev.id})">🗑 Supprimer</button>
        </div>\n    </div>`
      );
    }).join('');
  }

  window.deleteEvent = function(id) {
    const idx = (window.EVENTS_DATA || []).findIndex(e => e.id === id);
    if (idx === -1) return;
    const title = window.EVENTS_DATA[idx].title;
    if (!confirm(`Supprimer "${title}" ?`)) return;
    window.EVENTS_DATA.splice(idx, 1);
    renderAdminEvents();
    updateStats();
    JokerToast.show(`🗑 "${title}" supprimé.`, 'warning');
  };

  document.getElementById('btn-add-event-tab')?.addEventListener('click', () => JokerModal.open('modal-add-event'));

  document.getElementById('add-event-form')?.addEventListener('submit', (e) => {
    e.preventDefault();
    const title    = document.getElementById('ae-title').value.trim();
    const date     = document.getElementById('ae-date').value;
    const time     = document.getElementById('ae-time').value || '18:00';
    const type     = document.getElementById('ae-type').value;
    const access   = document.getElementById('ae-access').value;
    const location = document.getElementById('ae-location').value.trim() || 'Campus Joker';
    const desc     = document.getElementById('ae-desc').value.trim() || 'Événement Joker Club.';
    const emoji    = document.getElementById('ae-emoji').value || '🎯';
    const maxPart  = parseInt(document.getElementById('ae-max').value) || 50;
    if (!title || !date) return;

    window.EVENTS_DATA.unshift({ id: Date.now(), title, type, access, date, time, location, desc, emoji, participants: 0, maxParticipants: maxPart });
    renderAdminEvents();
    updateStats();
    JokerModal.close('modal-add-event');
    document.getElementById('add-event-form').reset();
    document.getElementById('ae-emoji').value = '🎯';
    JokerToast.show(`✦ "${title}" publié !`, 'success');
  });

  renderAdminEvents();

  /* ==== FORMATIONS ==== */
  function renderFormations() {
    const grid = document.getElementById('formations-grid');
    if (!grid) return;
    grid.innerHTML = FORMATIONS_DATA.map(f => `
      <div class="meeting-card">
        <div class="meeting-card-header">
          <div class="meeting-type-icon">${f.emoji}</div>
          <button class="btn-icon" onclick="deleteFormation(${f.id})">🗑</button>
        </div>
        <div class="meeting-title">${f.title}</div>
        <div class="meeting-meta">
          👤 ${f.trainer}<br>
          📅 ${formatDateFR(f.date)} · ⏱ ${f.duration}
        </div>
      </div>`).join('');
  }
  renderFormations();

  window.deleteFormation = function(id) {
    const idx = FORMATIONS_DATA.findIndex(f => f.id === id);
    if (idx === -1) return;
    const title = FORMATIONS_DATA[idx].title;
    FORMATIONS_DATA.splice(idx, 1);
    renderFormations();
    JokerToast.show(`🗑 "${title}" supprimée.`, 'warning');
  };

  document.getElementById('btn-add-formation')?.addEventListener('click', () => JokerModal.open('modal-add-formation'));

  document.getElementById('add-formation-form')?.addEventListener('submit', (e) => {
    e.preventDefault();
    const title    = document.getElementById('af-title').value.trim();
    const trainer  = document.getElementById('af-trainer').value.trim() || 'Intervenant TBD';
    const date     = document.getElementById('af-date').value;
    const duration = document.getElementById('af-duration').value.trim() || '1 jour';
    if (!title || !date) return;

    FORMATIONS_DATA.unshift({ id: Date.now(), title, trainer, date, duration, emoji: '📚' });
    renderFormations();
    JokerModal.close('modal-add-formation');
    document.getElementById('add-formation-form').reset();
    JokerToast.show(`📚 "${title}" créée !`, 'success');
  });

});

function formatDateFR(dateStr) {
  if (!dateStr) return '';
  const d = new Date(dateStr + 'T00:00:00');
  return d.toLocaleDateString('fr-FR', { day: '2-digit', month: 'short', year: 'numeric' });
}
