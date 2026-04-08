/* ============================================================
   JOKER CLUB — script.js
   Shared state, utilities, and page-specific logic
   ============================================================ */

"use strict";

/* ══════════════════════════════════════════════
   SHARED STATE (simulated database)
══════════════════════════════════════════════ */
const Store = {
  currentUser: null, // { name, role: 'admin'|'membre' }

  users: [
    { id: 1, name: "Admin Joker", email: "admin@joker.tn", password: "admin123", role: "admin" },
    { id: 2, name: "Sarra Ben Ali",  email: "sarra@joker.tn",  password: "membre123", role: "membre" },
    { id: 3, name: "Yassine Khalil", email: "yassine@joker.tn", password: "membre123", role: "membre" },
  ],

  memberRequests: [
    { id: 1, name: "Amine Trabelsi",  email: "amine@gmail.com",  date: "2025-04-01", status: "pending",  message: "Passionné par l'entrepreneuriat et l'innovation." },
    { id: 2, name: "Lina Bouazizi",   email: "lina@gmail.com",   date: "2025-04-03", status: "pending",  message: "Je veux développer mes compétences en leadership." },
    { id: 3, name: "Omar Chaabane",   email: "omar@gmail.com",   date: "2025-03-28", status: "accepted", message: "Étudiant en gestion, motivé par le travail en équipe." },
  ],

  events: [
    { id: 1, title: "Hackathon Joker 2025",     date: "2025-05-15", time: "09:00", location: "Salle B12",   type: "public",  description: "48h d'innovation et de code.",   participants: 24, maxParticipants: 50 },
    { id: 2, title: "Atelier Leadership",        date: "2025-05-22", time: "14:00", location: "Amphi A",     type: "public",  description: "Développez vos skills de leader.", participants: 18, maxParticipants: 30 },
    { id: 3, title: "Réunion Stratégie Club",    date: "2025-05-10", time: "18:00", location: "Salle Club",  type: "private", description: "Planification du semestre.",       participants: 8,  maxParticipants: 15 },
    { id: 4, title: "Soirée Networking",         date: "2025-06-01", time: "19:00", location: "Hall Central",type: "public",  description: "Rencontrez professionnels et alumni.", participants: 35, maxParticipants: 80 },
    { id: 5, title: "Formation Communication",   date: "2025-06-08", time: "10:00", location: "Salle B10",   type: "private", description: "Techniques de prise de parole.",  participants: 12, maxParticipants: 20 },
  ],

  meetings: [
    { id: 1, title: "Réunion Bureau",      date: "2025-05-08", time: "17:00", location: "Salle Club",  agenda: "Bilan mensuel et planification",   type: "bureau" },
    { id: 2, title: "Brainstorming Projet",date: "2025-05-14", time: "15:30", location: "Bibliothèque", agenda: "Idées pour le hackathon",          type: "projet" },
    { id: 3, title: "AG Membres",          date: "2025-05-20", time: "18:00", location: "Amphi A",     agenda: "Assemblée générale mensuelle",     type: "generale" },
  ],

  formations: [
    { id: 1, title: "Excel Avancé",         trainer: "Prof. Mansouri", date: "2025-05-12", duration: "3h", level: "Intermédiaire", registered: 14 },
    { id: 2, title: "Design Thinking",      trainer: "Mentor Karim",   date: "2025-05-19", duration: "4h", level: "Débutant",      registered: 22 },
    { id: 3, title: "Prise de parole",      trainer: "Coach Samia",    date: "2025-05-26", duration: "2h", level: "Tous niveaux",  registered: 18 },
  ],

  tasks: [
    { id: 1, title: "Préparer l'affiche Hackathon",  assignedTo: "Sarra Ben Ali",  deadline: "2025-05-10", priority: "high",   done: false },
    { id: 2, title: "Contacter les sponsors",         assignedTo: "Yassine Khalil", deadline: "2025-05-08", priority: "high",   done: false },
    { id: 3, title: "Réserver la salle Amphi A",      assignedTo: "Sarra Ben Ali",  deadline: "2025-05-15", priority: "medium", done: true  },
    { id: 4, title: "Rédiger newsletter mensuelle",   assignedTo: "Yassine Khalil", deadline: "2025-05-20", priority: "low",    done: false },
  ],

  todoList: [
    { id: 1, text: "Lire les documents du hackathon", done: false },
    { id: 2, text: "Préparer ma présentation",         done: true  },
    { id: 3, text: "Contacter mon équipe",             done: false },
  ],

  /* helpers */
  nextId: (arr) => arr.length ? Math.max(...arr.map(x => x.id)) + 1 : 1,

  login(email, password) {
    const user = this.users.find(u => u.email === email && u.password === password);
    if (user) { this.currentUser = user; return user; }
    return null;
  },
  logout() { this.currentUser = null; },
  isLoggedIn() { return !!this.currentUser; },
  isAdmin()    { return this.currentUser?.role === "admin"; },
  isMembre()   { return this.currentUser?.role === "membre"; },
};

/* persist currentUser across pages */
function saveSession() {
  if (Store.currentUser) sessionStorage.setItem("joker_user", JSON.stringify(Store.currentUser));
  else sessionStorage.removeItem("joker_user");
}
function loadSession() {
  const raw = sessionStorage.getItem("joker_user");
  if (raw) Store.currentUser = JSON.parse(raw);
}

/* ══════════════════════════════════════════════
   TOAST UTILITY
══════════════════════════════════════════════ */
function showToast(message, type = "info") {
  // type: info | success | error | warning
  const icons = { info: "ℹ️", success: "✅", error: "❌", warning: "⚠️" };
  let container = document.getElementById("toast-container");
  if (!container) {
    container = document.createElement("div");
    container.id = "toast-container";
    container.className = "toast-container position-fixed bottom-0 end-0 p-3";
    document.body.appendChild(container);
  }
  const id = "toast-" + Date.now();
  container.insertAdjacentHTML("beforeend", `
    <div id="${id}" class="toast toast-joker ${type} align-items-center show" role="alert" aria-live="assertive">
      <div class="d-flex align-items-center gap-2 p-3">
        <span>${icons[type]}</span>
        <div class="flex-grow-1" style="font-weight:600;font-size:.9rem;">${message}</div>
        <button type="button" class="btn-close ms-2" onclick="document.getElementById('${id}').remove()"></button>
      </div>
    </div>`);
  setTimeout(() => { const el = document.getElementById(id); if (el) el.remove(); }, 4000);
}

/* ══════════════════════════════════════════════
   FORMATTING HELPERS
══════════════════════════════════════════════ */
function formatDate(dateStr) {
  if (!dateStr) return "";
  const [y, m, d] = dateStr.split("-");
  const months = ["Jan","Fév","Mar","Avr","Mai","Juin","Juil","Aoû","Sep","Oct","Nov","Déc"];
  return `${parseInt(d)} ${months[parseInt(m)-1]} ${y}`;
}

function priorityBadge(p) {
  const map = { high: ["danger","Haute"], medium: ["warning","Moyenne"], low: ["secondary","Faible"] };
  const [cls, label] = map[p] || ["secondary","—"];
  return `<span class="badge bg-${cls}">${label}</span>`;
}

/* ══════════════════════════════════════════════
   PAGE: INDEX.HTML
══════════════════════════════════════════════ */
function initIndexPage() {
  loadSession();
  updateNavAuth();
  renderPublicEvents();
  bindJoinForm();
  animateCounters();
}

function updateNavAuth() {
  const authLinks = document.getElementById("nav-auth-links");
  if (!authLinks) return;
  if (Store.isAdmin()) {
    authLinks.innerHTML = `<a href="dashboard_admin.html" class="btn btn-joker-red btn-sm me-2">Dashboard</a>
      <button onclick="doLogout()" class="btn btn-joker-outline btn-sm">Déconnexion</button>`;
  } else if (Store.isMembre()) {
    authLinks.innerHTML = `<a href="dashboard_membre.html" class="btn btn-joker-blue btn-sm me-2">Dashboard</a>
      <button onclick="doLogout()" class="btn btn-joker-outline btn-sm">Déconnexion</button>`;
  } else {
    authLinks.innerHTML = `<a href="login.html" class="btn btn-joker-blue btn-sm me-2">Connexion</a>
      <a href="#rejoindre" class="btn btn-joker-red btn-sm">Nous rejoindre</a>`;
  }
}

function doLogout() {
  Store.logout();
  saveSession();
  window.location.href = "index.html";
}

function renderPublicEvents() {
  const container = document.getElementById("public-events-container");
  if (!container) return;
  const publicEvts = Store.events.filter(e => e.type === "public").slice(0, 3);
  container.innerHTML = publicEvts.map(e => eventCardHtml(e)).join("");
}

function eventCardHtml(e) {
  const spots = e.maxParticipants - e.participants;
  return `
    <div class="col-md-6 col-lg-4 animate-fadeInUp">
      <div class="joker-card h-100">
        <div class="card-body p-4">
          <div class="d-flex align-items-start justify-content-between mb-3">
            <span class="event-badge ${e.type}">${e.type === "public" ? "🌐 Public" : "🔒 Privé"}</span>
            <small class="text-muted fw-bold">${formatDate(e.date)}</small>
          </div>
          <h5 class="text-blue fw-bold mb-2">${e.title}</h5>
          <p class="text-muted small mb-3">${e.description}</p>
          <div class="d-flex align-items-center gap-3 mb-3 flex-wrap">
            <span class="small"><i class="bi bi-geo-alt text-red"></i> ${e.location}</span>
            <span class="small"><i class="bi bi-clock text-blue"></i> ${e.time}</span>
          </div>
          <div class="d-flex align-items-center justify-content-between">
            <span class="participant-count"><i class="bi bi-people-fill"></i> <span id="participants-${e.id}">${e.participants}</span>/${e.maxParticipants}</span>
            ${e.type === "public"
              ? `<button class="btn btn-joker-red btn-sm" onclick="openRegisterModal(${e.id})" ${spots <= 0 ? "disabled" : ""}>${spots <= 0 ? "Complet" : "S'inscrire"}</button>`
              : `<span class="badge-status active">Membres</span>`}
          </div>
        </div>
      </div>
    </div>`;
}

function openRegisterModal(eventId) {
  const evt = Store.events.find(e => e.id === eventId);
  if (!evt) return;
  const modalHtml = `
    <div class="modal fade modal-joker" id="registerModal" tabindex="-1">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Inscription : ${evt.title}</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body p-4">
            <div class="form-joker">
              <div class="mb-3"><label class="form-label">Nom complet *</label>
                <input type="text" class="form-control" id="reg-name" placeholder="Votre nom" required></div>
              <div class="mb-3"><label class="form-label">Email *</label>
                <input type="email" class="form-control" id="reg-email" placeholder="votre@email.com" required></div>
              <div class="mb-3"><label class="form-label">Téléphone</label>
                <input type="tel" class="form-control" id="reg-phone" placeholder="XX XXX XXX"></div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-joker-blue" onclick="confirmRegister(${eventId})">Confirmer l'inscription</button>
          </div>
        </div>
      </div>
    </div>`;
  document.body.insertAdjacentHTML("beforeend", modalHtml);
  const modal = new bootstrap.Modal(document.getElementById("registerModal"));
  modal.show();
  document.getElementById("registerModal").addEventListener("hidden.bs.modal", () => {
    document.getElementById("registerModal").remove();
  });
}

function confirmRegister(eventId) {
  const name  = document.getElementById("reg-name")?.value.trim();
  const email = document.getElementById("reg-email")?.value.trim();
  if (!name || !email) { showToast("Veuillez remplir les champs obligatoires.", "error"); return; }
  const evt = Store.events.find(e => e.id === eventId);
  if (evt) {
    evt.participants++;
    const span = document.getElementById("participants-" + eventId);
    if (span) span.textContent = evt.participants;
    if (evt.participants >= evt.maxParticipants) {
      const btn = document.querySelector(`button[onclick="openRegisterModal(${eventId})"]`);
      if (btn) { btn.textContent = "Complet"; btn.disabled = true; }
    }
  }
  bootstrap.Modal.getInstance(document.getElementById("registerModal")).hide();
  showToast(`✅ Inscription confirmée pour ${name} ! Vous recevrez un email de confirmation.`, "success");
}

function bindJoinForm() {
  const form = document.getElementById("join-form");
  if (!form) return;
  form.addEventListener("submit", function (e) {
    e.preventDefault();
    const name = document.getElementById("join-name").value.trim();
    const email= document.getElementById("join-email").value.trim();
    const msg  = document.getElementById("join-message").value.trim();
    if (!name || !email) { showToast("Nom et email sont obligatoires.", "error"); return; }
    const req = { id: Store.nextId(Store.memberRequests), name, email, date: new Date().toISOString().split("T")[0], status: "pending", message: msg };
    Store.memberRequests.push(req);
    form.reset();
    showToast("🎉 Demande envoyée ! L'admin examinera votre candidature.", "success");
  });
}

function animateCounters() {
  document.querySelectorAll("[data-count]").forEach(el => {
    const target = parseInt(el.getAttribute("data-count"));
    let current = 0;
    const step = Math.ceil(target / 60);
    const timer = setInterval(() => {
      current = Math.min(current + step, target);
      el.textContent = current;
      if (current >= target) clearInterval(timer);
    }, 25);
  });
}

/* ══════════════════════════════════════════════
   PAGE: EVENEMENTS.HTML
══════════════════════════════════════════════ */
function initEventsPage() {
  loadSession();
  updateNavAuth();
  renderAllEvents();
  document.getElementById("filter-type")?.addEventListener("change", renderAllEvents);
}

function renderAllEvents() {
  const container = document.getElementById("events-container");
  if (!container) return;
  const filter = document.getElementById("filter-type")?.value || "all";
  let events = [...Store.events];
  if (filter === "public")  events = events.filter(e => e.type === "public");
  if (filter === "private") events = events.filter(e => e.type === "private");
  if (!events.length) {
    container.innerHTML = `<div class="empty-state col-12"><i class="bi bi-calendar-x"></i><p>Aucun événement trouvé.</p></div>`;
    return;
  }
  container.innerHTML = events.map(e => eventCardHtml(e)).join("");
}

/* ══════════════════════════════════════════════
   PAGE: REUNIONS.HTML
══════════════════════════════════════════════ */
function initReunionsPage() {
  loadSession();
  updateNavAuth();
  renderMeetings();
}

function renderMeetings() {
  const grid = document.getElementById("meetings-grid");
  if (!grid) return;
  if (!Store.meetings.length) {
    grid.innerHTML = `<div class="empty-state"><i class="bi bi-calendar2-x"></i><p>Aucune réunion planifiée.</p></div>`;
    return;
  }
  grid.innerHTML = Store.meetings.map(m => `
    <div class="meeting-card animate-fadeInUp">
      <div class="meeting-date">${formatDate(m.date)}</div>
      <div class="meeting-title">${m.title}</div>
      <p class="small text-muted mb-2">${m.agenda}</p>
      <div class="d-flex gap-2 flex-wrap">
        <span class="small"><i class="bi bi-clock text-blue"></i> ${m.time}</span>
        <span class="small"><i class="bi bi-geo-alt text-red"></i> ${m.location}</span>
      </div>
      <span class="badge-status active mt-2 d-inline-block">${m.type}</span>
    </div>`).join("");
}

/* ══════════════════════════════════════════════
   PAGE: LOGIN.HTML
══════════════════════════════════════════════ */
function initLoginPage() {
  loadSession();
  if (Store.isAdmin())  { window.location.href = "dashboard_admin.html";  return; }
  if (Store.isMembre()) { window.location.href = "dashboard_membre.html"; return; }
  document.getElementById("login-form")?.addEventListener("submit", function (e) {
    e.preventDefault();
    const email    = document.getElementById("login-email").value.trim();
    const password = document.getElementById("login-password").value;
    const user = Store.login(email, password);
    if (!user) {
      showToast("Email ou mot de passe incorrect.", "error");
      document.getElementById("login-password").value = "";
      return;
    }
    saveSession();
    showToast(`Bienvenue, ${user.name} !`, "success");
    setTimeout(() => {
      window.location.href = user.role === "admin" ? "dashboard_admin.html" : "dashboard_membre.html";
    }, 800);
  });
}

/* ══════════════════════════════════════════════
   PAGE: DASHBOARD_ADMIN.HTML
══════════════════════════════════════════════ */
function initAdminDashboard() {
  loadSession();
  if (!Store.isAdmin()) { window.location.href = "login.html"; return; }

  document.getElementById("admin-name").textContent = Store.currentUser.name;
  document.getElementById("admin-initials").textContent = Store.currentUser.name.split(" ").map(w=>w[0]).join("").slice(0,2);

  updateAdminStats();
  renderAdminEvents();
  renderAdminMeetings();
  renderAdminRequests();
  renderAdminFormations();
  renderAdminTasks();

  /* tab navigation */
  document.querySelectorAll(".sidebar-menu .nav-item").forEach(item => {
    item.addEventListener("click", () => {
      document.querySelectorAll(".sidebar-menu .nav-item").forEach(i => i.classList.remove("active"));
      document.querySelectorAll(".tab-panel").forEach(p => p.classList.remove("active"));
      item.classList.add("active");
      const target = item.getAttribute("data-tab");
      if (target) {
        document.getElementById(target)?.classList.add("active");
        document.getElementById("page-title").textContent = item.getAttribute("data-label") || "Dashboard";
      }
    });
  });

  /* sidebar toggle */
  document.getElementById("sidebar-toggle")?.addEventListener("click", toggleSidebar);
  document.getElementById("sidebar-overlay")?.addEventListener("click", toggleSidebar);
}

function toggleSidebar() {
  document.getElementById("sidebar")?.classList.toggle("open");
  document.getElementById("sidebar-overlay")?.classList.toggle("active");
}

function updateAdminStats() {
  document.getElementById("stat-events").textContent    = Store.events.length;
  document.getElementById("stat-meetings").textContent  = Store.meetings.length;
  document.getElementById("stat-members").textContent   = Store.users.filter(u=>u.role==="membre").length;
  document.getElementById("stat-requests").textContent  = Store.memberRequests.filter(r=>r.status==="pending").length;
}

/* ─── ADMIN EVENTS ─── */
function renderAdminEvents() {
  const tbody = document.getElementById("admin-events-table");
  if (!tbody) return;
  tbody.innerHTML = Store.events.map(e => `
    <tr>
      <td><strong>${e.title}</strong></td>
      <td>${formatDate(e.date)} ${e.time}</td>
      <td>${e.location}</td>
      <td><span class="event-badge ${e.type}">${e.type === "public" ? "🌐 Public" : "🔒 Privé"}</span></td>
      <td>${e.participants}/${e.maxParticipants}</td>
      <td>
        <button class="btn btn-sm btn-joker-blue me-1" onclick="editEventModal(${e.id})"><i class="bi bi-pencil"></i></button>
        <button class="btn btn-sm btn-joker-red"       onclick="deleteEvent(${e.id})"><i class="bi bi-trash"></i></button>
      </td>
    </tr>`).join("") || `<tr><td colspan="6" class="text-center text-muted">Aucun événement</td></tr>`;
}

function openAddEventModal() {
  showEventModal(null);
}

function editEventModal(id) {
  const evt = Store.events.find(e => e.id === id);
  if (evt) showEventModal(evt);
}

function showEventModal(evt) {
  const isEdit = !!evt;
  const modalHtml = `
    <div class="modal fade modal-joker" id="eventModal" tabindex="-1">
      <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">${isEdit ? "Modifier" : "Ajouter"} un événement</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body p-4">
            <div class="form-joker row g-3">
              <div class="col-md-8"><label class="form-label">Titre *</label>
                <input type="text" class="form-control" id="evt-title" value="${evt?.title||""}" required></div>
              <div class="col-md-4"><label class="form-label">Type</label>
                <select class="form-select" id="evt-type">
                  <option value="public"  ${evt?.type==="public" ?"selected":""}>🌐 Public</option>
                  <option value="private" ${evt?.type==="private"?"selected":""}>🔒 Privé</option>
                </select></div>
              <div class="col-md-4"><label class="form-label">Date *</label>
                <input type="date" class="form-control" id="evt-date" value="${evt?.date||""}"></div>
              <div class="col-md-4"><label class="form-label">Heure</label>
                <input type="time" class="form-control" id="evt-time" value="${evt?.time||""}"></div>
              <div class="col-md-4"><label class="form-label">Max participants</label>
                <input type="number" class="form-control" id="evt-max" value="${evt?.maxParticipants||30}"></div>
              <div class="col-12"><label class="form-label">Lieu</label>
                <input type="text" class="form-control" id="evt-location" value="${evt?.location||""}"></div>
              <div class="col-12"><label class="form-label">Description</label>
                <textarea class="form-control" id="evt-desc" rows="3">${evt?.description||""}</textarea></div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-joker-blue" onclick="saveEvent(${isEdit ? evt.id : "null"})">
              <i class="bi bi-check-lg me-1"></i>${isEdit ? "Sauvegarder" : "Créer l'événement"}
            </button>
          </div>
        </div>
      </div>
    </div>`;
  document.body.insertAdjacentHTML("beforeend", modalHtml);
  const modal = new bootstrap.Modal(document.getElementById("eventModal"));
  modal.show();
  document.getElementById("eventModal").addEventListener("hidden.bs.modal", () => {
    document.getElementById("eventModal").remove();
  });
}

function saveEvent(id) {
  const title    = document.getElementById("evt-title").value.trim();
  const type     = document.getElementById("evt-type").value;
  const date     = document.getElementById("evt-date").value;
  const time     = document.getElementById("evt-time").value;
  const location = document.getElementById("evt-location").value.trim();
  const desc     = document.getElementById("evt-desc").value.trim();
  const maxP     = parseInt(document.getElementById("evt-max").value) || 30;
  if (!title || !date) { showToast("Titre et date obligatoires.", "error"); return; }

  if (id) {
    const evt = Store.events.find(e => e.id === id);
    Object.assign(evt, { title, type, date, time, location, description: desc, maxParticipants: maxP });
    showToast("Événement modifié.", "success");
  } else {
    Store.events.push({ id: Store.nextId(Store.events), title, type, date, time, location, description: desc, participants: 0, maxParticipants: maxP });
    showToast("Événement ajouté !", "success");
  }
  bootstrap.Modal.getInstance(document.getElementById("eventModal")).hide();
  renderAdminEvents();
  updateAdminStats();
}

function deleteEvent(id) {
  if (!confirm("Supprimer cet événement ?")) return;
  Store.events = Store.events.filter(e => e.id !== id);
  renderAdminEvents();
  updateAdminStats();
  showToast("Événement supprimé.", "info");
}

/* ─── ADMIN MEETINGS ─── */
function renderAdminMeetings() {
  const grid = document.getElementById("admin-meetings-grid");
  if (!grid) return;
  grid.innerHTML = Store.meetings.map(m => `
    <div class="meeting-card">
      <div class="d-flex justify-content-between align-items-start mb-2">
        <div class="meeting-date">${formatDate(m.date)}</div>
        <div class="d-flex gap-1">
          <button class="btn btn-sm btn-joker-blue" onclick="editMeetingModal(${m.id})"><i class="bi bi-pencil"></i></button>
          <button class="btn btn-sm btn-joker-red"  onclick="deleteMeeting(${m.id})"><i class="bi bi-trash"></i></button>
        </div>
      </div>
      <div class="meeting-title">${m.title}</div>
      <p class="small text-muted mb-1">${m.agenda}</p>
      <div class="d-flex gap-2 small flex-wrap">
        <span><i class="bi bi-clock text-blue"></i> ${m.time}</span>
        <span><i class="bi bi-geo-alt text-red"></i> ${m.location}</span>
      </div>
    </div>`).join("") || `<div class="empty-state"><i class="bi bi-calendar2-x"></i><p>Aucune réunion.</p></div>`;
}

function openAddMeetingModal() { showMeetingModal(null); }
function editMeetingModal(id) { showMeetingModal(Store.meetings.find(m => m.id === id)); }

function showMeetingModal(m) {
  const isEdit = !!m;
  const types = ["bureau","projet","generale","formation"];
  const typeOpts = types.map(t => `<option value="${t}" ${m?.type===t?"selected":""}>${t}</option>`).join("");
  const html = `
    <div class="modal fade modal-joker" id="meetingModal" tabindex="-1">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">${isEdit?"Modifier":"Ajouter"} une réunion</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body p-4">
            <div class="form-joker row g-3">
              <div class="col-12"><label class="form-label">Titre *</label>
                <input type="text" class="form-control" id="mtg-title" value="${m?.title||""}"></div>
              <div class="col-md-6"><label class="form-label">Date *</label>
                <input type="date" class="form-control" id="mtg-date" value="${m?.date||""}"></div>
              <div class="col-md-6"><label class="form-label">Heure</label>
                <input type="time" class="form-control" id="mtg-time" value="${m?.time||""}"></div>
              <div class="col-md-6"><label class="form-label">Lieu</label>
                <input type="text" class="form-control" id="mtg-location" value="${m?.location||""}"></div>
              <div class="col-md-6"><label class="form-label">Type</label>
                <select class="form-select" id="mtg-type">${typeOpts}</select></div>
              <div class="col-12"><label class="form-label">Ordre du jour</label>
                <textarea class="form-control" id="mtg-agenda" rows="2">${m?.agenda||""}</textarea></div>
            </div>
          </div>
          <div class="modal-footer">
            <button class="btn btn-joker-blue" onclick="saveMeeting(${isEdit?m.id:"null"})">
              <i class="bi bi-check-lg me-1"></i>${isEdit?"Sauvegarder":"Créer"}
            </button>
          </div>
        </div>
      </div>
    </div>`;
  document.body.insertAdjacentHTML("beforeend", html);
  const modal = new bootstrap.Modal(document.getElementById("meetingModal"));
  modal.show();
  document.getElementById("meetingModal").addEventListener("hidden.bs.modal", () => {
    document.getElementById("meetingModal").remove();
  });
}

function saveMeeting(id) {
  const title    = document.getElementById("mtg-title").value.trim();
  const date     = document.getElementById("mtg-date").value;
  const time     = document.getElementById("mtg-time").value;
  const location = document.getElementById("mtg-location").value.trim();
  const type     = document.getElementById("mtg-type").value;
  const agenda   = document.getElementById("mtg-agenda").value.trim();
  if (!title || !date) { showToast("Titre et date obligatoires.", "error"); return; }
  if (id) {
    const m = Store.meetings.find(m => m.id === id);
    Object.assign(m, { title, date, time, location, type, agenda });
    showToast("Réunion modifiée.", "success");
  } else {
    Store.meetings.push({ id: Store.nextId(Store.meetings), title, date, time, location, type, agenda });
    showToast("Réunion ajoutée !", "success");
  }
  bootstrap.Modal.getInstance(document.getElementById("meetingModal")).hide();
  renderAdminMeetings();
  updateAdminStats();
}

function deleteMeeting(id) {
  if (!confirm("Supprimer cette réunion ?")) return;
  Store.meetings = Store.meetings.filter(m => m.id !== id);
  renderAdminMeetings();
  updateAdminStats();
  showToast("Réunion supprimée.", "info");
}

/* ─── ADMIN REQUESTS ─── */
function renderAdminRequests() {
  const tbody = document.getElementById("admin-requests-table");
  if (!tbody) return;
  tbody.innerHTML = Store.memberRequests.map(r => `
    <tr>
      <td><strong>${r.name}</strong></td>
      <td>${r.email}</td>
      <td>${formatDate(r.date)}</td>
      <td><span class="badge-status ${r.status}">${r.status === "pending" ? "En attente" : r.status === "accepted" ? "Accepté" : "Refusé"}</span></td>
      <td>
        ${r.status === "pending"
          ? `<button class="btn btn-sm btn-joker-blue me-1" onclick="handleRequest(${r.id},'accepted')"><i class="bi bi-check-lg"></i> Accepter</button>
             <button class="btn btn-sm btn-joker-red" onclick="handleRequest(${r.id},'refused')"><i class="bi bi-x-lg"></i> Refuser</button>`
          : `<span class="text-muted small">—</span>`}
      </td>
    </tr>`).join("") || `<tr><td colspan="5" class="text-center text-muted">Aucune demande</td></tr>`;
}

function handleRequest(id, action) {
  const req = Store.memberRequests.find(r => r.id === id);
  if (!req) return;
  req.status = action;
  if (action === "accepted") {
    Store.users.push({ id: Store.nextId(Store.users), name: req.name, email: req.email, password: "joker123", role: "membre" });
    showToast(`✅ ${req.name} a été accepté comme membre !`, "success");
  } else {
    showToast(`❌ Demande de ${req.name} refusée.`, "info");
  }
  renderAdminRequests();
  updateAdminStats();
}

/* ─── ADMIN FORMATIONS ─── */
function renderAdminFormations() {
  const container = document.getElementById("admin-formations-container");
  if (!container) return;
  container.innerHTML = Store.formations.map(f => `
    <div class="col-md-6 col-lg-4">
      <div class="formation-card h-100">
        <div class="d-flex justify-content-between align-items-start mb-2">
          <h6 class="text-blue fw-bold mb-0">${f.title}</h6>
          <div class="d-flex gap-1">
            <button class="btn btn-sm btn-joker-blue" onclick="editFormation(${f.id})"><i class="bi bi-pencil"></i></button>
            <button class="btn btn-sm btn-joker-red"  onclick="deleteFormation(${f.id})"><i class="bi bi-trash"></i></button>
          </div>
        </div>
        <p class="small text-muted mb-1">👤 ${f.trainer}</p>
        <p class="small mb-1"><i class="bi bi-calendar3 text-blue"></i> ${formatDate(f.date)} — ${f.duration}</p>
        <div class="d-flex gap-2 flex-wrap">
          <span class="badge-status active">${f.level}</span>
          <span class="small text-muted">${f.registered} inscrits</span>
        </div>
      </div>
    </div>`).join("") || `<div class="empty-state col-12"><i class="bi bi-mortarboard"></i><p>Aucune formation.</p></div>`;
}

function openAddFormationModal() { showFormationModal(null); }
function editFormation(id) { showFormationModal(Store.formations.find(f => f.id === id)); }

function showFormationModal(f) {
  const isEdit = !!f;
  const levels = ["Débutant","Intermédiaire","Avancé","Tous niveaux"];
  const html = `
    <div class="modal fade modal-joker" id="formationModal" tabindex="-1">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">${isEdit?"Modifier":"Ajouter"} une formation</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body p-4">
            <div class="form-joker row g-3">
              <div class="col-12"><label class="form-label">Titre *</label>
                <input type="text" class="form-control" id="frm-title" value="${f?.title||""}"></div>
              <div class="col-md-6"><label class="form-label">Formateur</label>
                <input type="text" class="form-control" id="frm-trainer" value="${f?.trainer||""}"></div>
              <div class="col-md-6"><label class="form-label">Niveau</label>
                <select class="form-select" id="frm-level">${levels.map(l=>`<option ${f?.level===l?"selected":""}>${l}</option>`).join("")}</select></div>
              <div class="col-md-6"><label class="form-label">Date</label>
                <input type="date" class="form-control" id="frm-date" value="${f?.date||""}"></div>
              <div class="col-md-6"><label class="form-label">Durée</label>
                <input type="text" class="form-control" id="frm-duration" placeholder="ex: 3h" value="${f?.duration||""}"></div>
            </div>
          </div>
          <div class="modal-footer">
            <button class="btn btn-joker-blue" onclick="saveFormation(${isEdit?f.id:"null"})">
              <i class="bi bi-check-lg me-1"></i>${isEdit?"Sauvegarder":"Créer"}
            </button>
          </div>
        </div>
      </div>
    </div>`;
  document.body.insertAdjacentHTML("beforeend", html);
  const modal = new bootstrap.Modal(document.getElementById("formationModal"));
  modal.show();
  document.getElementById("formationModal").addEventListener("hidden.bs.modal", () => {
    document.getElementById("formationModal").remove();
  });
}

function saveFormation(id) {
  const title    = document.getElementById("frm-title").value.trim();
  const trainer  = document.getElementById("frm-trainer").value.trim();
  const level    = document.getElementById("frm-level").value;
  const date     = document.getElementById("frm-date").value;
  const duration = document.getElementById("frm-duration").value.trim();
  if (!title) { showToast("Titre obligatoire.", "error"); return; }
  if (id) {
    const f = Store.formations.find(f => f.id === id);
    Object.assign(f, { title, trainer, level, date, duration });
    showToast("Formation modifiée.", "success");
  } else {
    Store.formations.push({ id: Store.nextId(Store.formations), title, trainer, level, date, duration, registered: 0 });
    showToast("Formation ajoutée !", "success");
  }
  bootstrap.Modal.getInstance(document.getElementById("formationModal")).hide();
  renderAdminFormations();
}

function deleteFormation(id) {
  if (!confirm("Supprimer cette formation ?")) return;
  Store.formations = Store.formations.filter(f => f.id !== id);
  renderAdminFormations();
  showToast("Formation supprimée.", "info");
}

/* ─── ADMIN TASKS ─── */
function renderAdminTasks() {
  const tbody = document.getElementById("admin-tasks-table");
  if (!tbody) return;
  tbody.innerHTML = Store.tasks.map(t => `
    <tr>
      <td><strong>${t.title}</strong></td>
      <td>${t.assignedTo}</td>
      <td>${formatDate(t.deadline)}</td>
      <td>${priorityBadge(t.priority)}</td>
      <td><span class="badge-status ${t.done?"accepted":"pending"}">${t.done?"Fait":"En cours"}</span></td>
      <td>
        <button class="btn btn-sm btn-joker-blue me-1" onclick="toggleTask(${t.id})">
          <i class="bi bi-${t.done?"arrow-counterclockwise":"check-lg"}"></i>
        </button>
        <button class="btn btn-sm btn-joker-red" onclick="deleteTask(${t.id})"><i class="bi bi-trash"></i></button>
      </td>
    </tr>`).join("") || `<tr><td colspan="6" class="text-center text-muted">Aucune tâche</td></tr>`;
}

function openAddTaskModal() {
  const members = Store.users.filter(u => u.role === "membre");
  const html = `
    <div class="modal fade modal-joker" id="taskModal" tabindex="-1">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Ajouter une tâche</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body p-4">
            <div class="form-joker row g-3">
              <div class="col-12"><label class="form-label">Tâche *</label>
                <input type="text" class="form-control" id="task-title" placeholder="Description de la tâche"></div>
              <div class="col-md-6"><label class="form-label">Assigné à</label>
                <select class="form-select" id="task-assigned">
                  ${members.map(m=>`<option>${m.name}</option>`).join("")}
                </select></div>
              <div class="col-md-6"><label class="form-label">Priorité</label>
                <select class="form-select" id="task-priority">
                  <option value="high">🔴 Haute</option>
                  <option value="medium" selected>🟡 Moyenne</option>
                  <option value="low">🟢 Faible</option>
                </select></div>
              <div class="col-12"><label class="form-label">Deadline</label>
                <input type="date" class="form-control" id="task-deadline"></div>
            </div>
          </div>
          <div class="modal-footer">
            <button class="btn btn-joker-blue" onclick="saveTask()"><i class="bi bi-check-lg me-1"></i>Créer</button>
          </div>
        </div>
      </div>
    </div>`;
  document.body.insertAdjacentHTML("beforeend", html);
  const modal = new bootstrap.Modal(document.getElementById("taskModal"));
  modal.show();
  document.getElementById("taskModal").addEventListener("hidden.bs.modal", () => {
    document.getElementById("taskModal").remove();
  });
}

function saveTask() {
  const title      = document.getElementById("task-title").value.trim();
  const assignedTo = document.getElementById("task-assigned").value;
  const priority   = document.getElementById("task-priority").value;
  const deadline   = document.getElementById("task-deadline").value;
  if (!title) { showToast("Titre obligatoire.", "error"); return; }
  Store.tasks.push({ id: Store.nextId(Store.tasks), title, assignedTo, deadline, priority, done: false });
  bootstrap.Modal.getInstance(document.getElementById("taskModal")).hide();
  renderAdminTasks();
  showToast("Tâche ajoutée !", "success");
}

function toggleTask(id) {
  const t = Store.tasks.find(t => t.id === id);
  if (t) { t.done = !t.done; renderAdminTasks(); }
}

function deleteTask(id) {
  Store.tasks = Store.tasks.filter(t => t.id !== id);
  renderAdminTasks();
  showToast("Tâche supprimée.", "info");
}

/* ══════════════════════════════════════════════
   PAGE: DASHBOARD_MEMBRE.HTML
══════════════════════════════════════════════ */
function initMembreDashboard() {
  loadSession();
  if (!Store.isMembre() && !Store.isAdmin()) { window.location.href = "login.html"; return; }

  document.getElementById("membre-name").textContent = Store.currentUser.name;
  document.getElementById("membre-initials").textContent = Store.currentUser.name.split(" ").map(w=>w[0]).join("").slice(0,2);

  renderMembreEvents();
  renderMembreMeetings();
  renderMembreFormations();
  renderMembreTasks();
  renderTodoList();

  /* tab nav */
  document.querySelectorAll(".sidebar-menu .nav-item").forEach(item => {
    item.addEventListener("click", () => {
      document.querySelectorAll(".sidebar-menu .nav-item").forEach(i => i.classList.remove("active"));
      document.querySelectorAll(".tab-panel").forEach(p => p.classList.remove("active"));
      item.classList.add("active");
      const target = item.getAttribute("data-tab");
      if (target) {
        document.getElementById(target)?.classList.add("active");
        document.getElementById("page-title").textContent = item.getAttribute("data-label") || "Dashboard";
      }
    });
  });

  document.getElementById("sidebar-toggle")?.addEventListener("click", toggleSidebar);
  document.getElementById("sidebar-overlay")?.addEventListener("click", toggleSidebar);
}

function renderMembreEvents() {
  const container = document.getElementById("membre-events-container");
  if (!container) return;
  container.innerHTML = Store.events.map(e => eventCardHtml(e)).join("");
}

function renderMembreMeetings() {
  const grid = document.getElementById("membre-meetings-grid");
  if (!grid) return;
  grid.innerHTML = Store.meetings.map(m => `
    <div class="meeting-card animate-fadeInUp">
      <div class="meeting-date">${formatDate(m.date)}</div>
      <div class="meeting-title">${m.title}</div>
      <p class="small text-muted mb-2">${m.agenda}</p>
      <div class="d-flex gap-2 small flex-wrap">
        <span><i class="bi bi-clock text-blue"></i> ${m.time}</span>
        <span><i class="bi bi-geo-alt text-red"></i> ${m.location}</span>
      </div>
    </div>`).join("") || `<div class="empty-state"><i class="bi bi-calendar2-x"></i><p>Aucune réunion prévue.</p></div>`;
}

function renderMembreFormations() {
  const container = document.getElementById("membre-formations-container");
  if (!container) return;
  container.innerHTML = Store.formations.map(f => `
    <div class="col-md-6 col-lg-4">
      <div class="formation-card h-100">
        <h6 class="text-blue fw-bold mb-2">${f.title}</h6>
        <p class="small text-muted mb-1">👤 ${f.trainer}</p>
        <p class="small mb-2"><i class="bi bi-calendar3 text-blue"></i> ${formatDate(f.date)} — ${f.duration}</p>
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
          <span class="badge-status active">${f.level}</span>
          <button class="btn btn-joker-blue btn-sm" onclick="registerFormation(${f.id})">S'inscrire</button>
        </div>
      </div>
    </div>`).join("") || `<div class="empty-state col-12"><i class="bi bi-mortarboard"></i><p>Aucune formation disponible.</p></div>`;
}

function registerFormation(id) {
  const f = Store.formations.find(f => f.id === id);
  if (f) {
    f.registered++;
    showToast(`✅ Inscription confirmée pour "${f.title}" !`, "success");
    renderMembreFormations();
  }
}

function renderMembreTasks() {
  const container = document.getElementById("membre-tasks-container");
  if (!container) return;
  const myTasks = Store.tasks.filter(t => t.assignedTo === Store.currentUser.name);
  if (!myTasks.length) {
    container.innerHTML = `<div class="empty-state"><i class="bi bi-check2-all"></i><p>Aucune tâche assignée.</p></div>`;
    return;
  }
  container.innerHTML = myTasks.map(t => `
    <div class="d-flex align-items-start gap-3 p-3 rounded-joker border mb-2" style="background:var(--beige-light)">
      <div class="flex-grow-1">
        <div class="fw-bold ${t.done?"text-muted text-decoration-line-through":""}">${t.title}</div>
        <div class="small text-muted">Deadline: ${formatDate(t.deadline)} ${priorityBadge(t.priority)}</div>
      </div>
      <span class="badge-status ${t.done?"accepted":"pending"}">${t.done?"Fait":"En cours"}</span>
    </div>`).join("");
}

function renderTodoList() {
  const container = document.getElementById("todo-container");
  if (!container) return;
  container.innerHTML = Store.todoList.map(t => `
    <div class="todo-item ${t.done?"done":""}">
      <div class="todo-check ${t.done?"checked":""}" onclick="toggleTodo(${t.id})">${t.done?"✓":""}</div>
      <div class="todo-text flex-grow-1">${t.text}</div>
      <button class="btn btn-sm text-muted" onclick="deleteTodo(${t.id})" style="opacity:.6">🗑</button>
    </div>`).join("") || `<div class="empty-state"><i class="bi bi-check2-square"></i><p>Todo list vide.</p></div>`;
}

function addTodoItem() {
  const input = document.getElementById("new-todo");
  const text = input?.value.trim();
  if (!text) { showToast("Saisissez une tâche.", "error"); return; }
  Store.todoList.push({ id: Store.nextId(Store.todoList), text, done: false });
  input.value = "";
  renderTodoList();
  showToast("Tâche ajoutée !", "success");
}

function toggleTodo(id) {
  const t = Store.todoList.find(t => t.id === id);
  if (t) { t.done = !t.done; renderTodoList(); }
}

function deleteTodo(id) {
  Store.todoList = Store.todoList.filter(t => t.id !== id);
  renderTodoList();
}

/* ══════════════════════════════════════════════
   AUTO-INIT: detect page and call appropriate init
══════════════════════════════════════════════ */
document.addEventListener("DOMContentLoaded", () => {
  const page = document.body.getAttribute("data-page");
  const inits = {
    "index":             initIndexPage,
    "evenements":        initEventsPage,
    "reunions":          initReunionsPage,
    "login":             initLoginPage,
    "dashboard_admin":   initAdminDashboard,
    "dashboard_membre":  initMembreDashboard,
  };
  if (inits[page]) inits[page]();
});
