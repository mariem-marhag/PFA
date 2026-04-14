/* ============================================================
   JOKER UNIVERSITY CLUB — member.js
   Member dashboard
   ============================================================ */

const MY_TASKS = [
  { id: 1, title: 'Préparer présentation séance',  priority: 'high', due: '2024-10-26', done: false },
  { id: 2, title: 'Lire compte-rendu dernière réu', priority: 'med',  due: '2024-10-28', done: false },
  { id: 3, title: 'Contacter formateur externe',   priority: 'low',  due: '2024-11-05', done: true  },
];

let myInscriptions = 0;

document.addEventListener('DOMContentLoaded', () => {

  /* ---- Auth guard ---- */
  const user = JokerAuth.getUser();
  if (!user) {
    JokerToast.show('🔒 Connexion requise.', 'warning');
    setTimeout(() => JokerNav.navigateTo('login'), 1200);
    return;
  }

  document.getElementById('member-name').textContent = user.name;

  // Profile tab
  const initials = user.name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2);
  document.getElementById('profile-avatar').textContent = initials;
  document.getElementById('profile-name').textContent = user.name;
  document.getElementById('profile-email').textContent = user.email;
  document.getElementById('pf-name').value = user.name;
  document.getElementById('pf-email').value = user.email;

  /* ---- Tabs ---- */
  document.querySelectorAll('.tab-btn').forEach(btn => {
    btn.addEventListener('click', () => {
      document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
      document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
      btn.classList.add('active');
      document.getElementById(btn.dataset.tab).classList.add('active');
    });
  });

  /* ==== EVENTS ==== */
  function renderMemberEvents() {
    const grid = document.getElementById('member-events-grid');
    if (!grid || !window.EVENTS_DATA) return;
    // Members can see all events
    grid.innerHTML = window.EVENTS_DATA.slice(0, 6).map(ev => buildEventCard(ev, true)).join('');
    document.getElementById('m-stat-inscriptions').textContent = myInscriptions;
  }
  renderMemberEvents();

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
    JokerToast.show('✓ Participation confirmée en tant que membre !', 'success');
    const ev = window.EVENTS_DATA.find(e => e.id === eventId);
    if (ev) ev.participants++;
    myInscriptions++;
    renderMemberEvents();
  };

  document.getElementById('inscription-form')?.addEventListener('submit', (e) => {
    e.preventDefault();
    let valid = true;
    const name  = document.getElementById('insc-name');
    const email = document.getElementById('insc-email');
    if (!name.value.trim()) { document.getElementById('err-name').classList.add('show'); valid = false; }
    else document.getElementById('err-name').classList.remove('show');
    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.value)) { document.getElementById('err-email').classList.add('show'); valid = false; }
    else document.getElementById('err-email').classList.remove('show');
    if (!valid) return;

    const eventId = parseInt(e.target.dataset.eventId);
    const ev = window.EVENTS_DATA.find(ev => ev.id === eventId);
    if (ev) ev.participants++;
    myInscriptions++;

    document.getElementById('inscription-form-wrap').style.display = 'none';
    document.getElementById('inscription-success').classList.add('show');
    renderMemberEvents();
    JokerToast.show('✅ Inscription confirmée !', 'success');
    setTimeout(() => JokerModal.close('modal-inscription'), 2500);
  });

  /* ==== TASKS ==== */
  function renderMyTasks() {
    const list = document.getElementById('member-tasks-list');
    if (!list) return;

    document.getElementById('m-stat-tasks').textContent = MY_TASKS.filter(t => !t.done).length;

    if (MY_TASKS.length === 0) {
      list.innerHTML = `<div class="empty-state"><div class="empty-state-icon">✅</div><h3>Aucune tâche en cours</h3></div>`;
      return;
    }

    list.innerHTML = MY_TASKS.map(t => `
      <div class="task-item ${t.done ? 'done' : ''}">
        <div class="task-priority-bar priority-${t.priority}"></div>
        <div class="task-check ${t.done ? 'checked' : ''}" onclick="toggleMyTask(${t.id})"></div>
        <div class="task-title">${t.title}</div>
        <div style="display:flex;gap:0.75rem;align-items:center;margin-left:auto;">
          <span class="task-due">${t.due ? formatDateFR(t.due) : ''}</span>
          <button class="btn-icon" onclick="deleteMyTask(${t.id})">🗑</button>
        </div>
      </div>`).join('');
  }
  renderMyTasks();

  window.toggleMyTask = function(id) {
    const t = MY_TASKS.find(t => t.id === id);
    if (!t) return;
    t.done = !t.done;
    renderMyTasks();
    JokerToast.show(t.done ? '✅ Tâche terminée !' : '↩ Tâche rouverte.', t.done ? 'success' : 'info');
  };

  window.deleteMyTask = function(id) {
    const idx = MY_TASKS.findIndex(t => t.id === id);
    if (idx === -1) return;
    MY_TASKS.splice(idx, 1);
    renderMyTasks();
    JokerToast.show('🗑 Tâche supprimée.', 'warning');
  };

  document.getElementById('btn-add-my-task')?.addEventListener('click', () => JokerModal.open('modal-add-task'));

  document.getElementById('add-task-form')?.addEventListener('submit', (e) => {
    e.preventDefault();
    const title    = document.getElementById('mt-title').value.trim();
    const priority = document.getElementById('mt-priority').value;
    const due      = document.getElementById('mt-due').value;
    if (!title) return;
    MY_TASKS.unshift({ id: Date.now(), title, priority, due, done: false });
    renderMyTasks();
    JokerModal.close('modal-add-task');
    document.getElementById('add-task-form').reset();
    JokerToast.show(`📋 "${title}" ajoutée !`, 'success');
  });

  /* ==== PROFILE ==== */
  document.getElementById('profile-form')?.addEventListener('submit', (e) => {
    e.preventDefault();
    const name = document.getElementById('pf-name').value.trim();
    document.getElementById('profile-name').textContent = name || user.name;
    const initials2 = (name || user.name).split(' ').map(n => n[0]).join('').toUpperCase().slice(0,2);
    document.getElementById('profile-avatar').textContent = initials2;
    JokerToast.show('✅ Profil mis à jour !', 'success');
  });

});

function formatDateFR(dateStr) {
  if (!dateStr) return '';
  const d = new Date(dateStr + 'T00:00:00');
  return d.toLocaleDateString('fr-FR', { day: '2-digit', month: 'short', year: 'numeric' });
}
