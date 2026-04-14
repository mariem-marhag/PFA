/* ============================================================
   JOKER UNIVERSITY CLUB — navigation.js
   Shared navigation, routing, auth helpers, toast
   ============================================================ */

/* ---------- AUTH MODULE ---------- */
const JokerAuth = (() => {
  const DEMO_USERS = [
    { email: 'admin@joker.edu',   password: 'admin123',  role: 'admin',   name: 'Alex Admin' },
    { email: 'membre@joker.edu',  password: 'membre123', role: 'member',  name: 'Marie Membre' },
  ];

  function login(email, password) {
    const user = DEMO_USERS.find(u => u.email === email && u.password === password);
    if (user) {
      sessionStorage.setItem('joker_user', JSON.stringify(user));
      return { success: true, user };
    }
    return { success: false };
  }

  function logout() {
    sessionStorage.removeItem('joker_user');
    window.location.href = 'index.html';
  }

  function getUser() {
    const raw = sessionStorage.getItem('joker_user');
    return raw ? JSON.parse(raw) : null;
  }

  function isLoggedIn() { return !!getUser(); }
  function isAdmin()    { const u = getUser(); return u && u.role === 'admin'; }
  function isMember()   { const u = getUser(); return u && (u.role === 'member' || u.role === 'admin'); }

  return { login, logout, getUser, isLoggedIn, isAdmin, isMember };
})();

/* ---------- NAVIGATION MODULE ---------- */
const JokerNav = (() => {
  const PAGES = {
    home:      'index.html',
    events:    'events.html',
    reunions:  'reunions.html',
    login:     'login.html',
    admin:     'dashboard-admin.html',
    member:    'dashboard-member.html',
  };

  const NAV_LINKS = [
    { label: 'Accueil',   page: 'home',     public: true  },
    { label: 'Événements',page: 'events',   public: true  },
    { label: 'Réunions',  page: 'reunions', public: false },
  ];

  function currentPage() {
    const file = window.location.pathname.split('/').pop() || 'index.html';
    for (const [key, val] of Object.entries(PAGES)) {
      if (val === file) return key;
    }
    return 'home';
  }

  function navigateTo(page) {
    const target = PAGES[page];
    if (!target) return;
    if (PAGES[currentPage()] === target) return;
    document.body.style.transition = 'opacity 0.18s ease';
    document.body.style.opacity = '0';
    setTimeout(() => { window.location.href = target; }, 180);
  }

  function handleNavClick(e, page) {
    e.preventDefault();
    navigateTo(page);
  }

  function renderNav() {
    const container = document.getElementById('nav-root');
    if (!container) return;
    const active  = currentPage();
    const user    = JokerAuth.getUser();

    const linksHTML = NAV_LINKS
      .filter(l => l.public || user)
      .map(l => `<a href="${PAGES[l.page]}"
                    class="${active === l.page ? 'active' : ''}"
                    onclick="JokerNav.handleNavClick(event,'${l.page}')"
                 >${l.label}</a>`)
      .join('');

    const actionsHTML = user
      ? `<div class="nav-user-badge visible">
           <span class="nav-user-dot"></span>
           ${user.name}
           <span class="badge badge-${user.role === 'admin' ? 'accent' : 'primary'}"
                 style="font-size:0.6rem;padding:0.15rem 0.5rem;">${user.role}</span>
         </div>
         <button class="btn btn-ghost btn-sm" onclick="JokerAuth.logout()">Déconnexion</button>
         <button class="btn btn-primary btn-sm"
                 onclick="JokerNav.navigateTo('${user.role === 'admin' ? 'admin' : 'member'}')">
           Dashboard
         </button>`
      : `<button class="btn btn-ghost btn-sm" onclick="JokerNav.navigateTo('login')">Connexion</button>
         <button class="btn btn-primary btn-sm" onclick="JokerNav.navigateTo('login')">Rejoindre</button>`;

    container.innerHTML = `
      <header class="nav-header">
        <div class="nav-inner">
          <div class="nav-logo" onclick="JokerNav.navigateTo('home')" role="button" tabindex="0">
            Joker<span class="logo-dot"></span>
          </div>
          <nav class="nav-links">${linksHTML}</nav>
          <div class="nav-actions">${actionsHTML}</div>
        </div>
      </header>`;
  }

  function renderFooter() {
    const container = document.getElementById('footer-root');
    if (!container) return;
    container.innerHTML = `
      <footer class="site-footer">
        <div class="container">
          <div class="footer-grid">
            <div>
              <div class="footer-brand-name">Joker.</div>
              <p class="footer-brand-desc">Façonner les leaders de demain à travers une communauté universitaire vibrante et engagée.</p>
            </div>
            <div class="footer-col">
              <div class="footer-col-title">Navigation</div>
              <ul>
                <li><a href="index.html">Accueil</a></li>
                <li><a href="events.html">Événements</a></li>
                <li><a href="reunions.html">Réunions</a></li>
                <li><a href="login.html">Connexion</a></li>
              </ul>
            </div>
            <div class="footer-col">
              <div class="footer-col-title">Club</div>
              <ul>
                <li><a href="#">À propos</a></li>
                <li><a href="#">Bureau Exécutif</a></li>
                <li><a href="#">Formations</a></li>
              </ul>
            </div>
            <div class="footer-col">
              <div class="footer-col-title">Support</div>
              <ul>
                <li><a href="#">Contact</a></li>
                <li><a href="#">FAQ</a></li>
                <li><a href="#">Politique de confidentialité</a></li>
              </ul>
            </div>
          </div>
          <div class="footer-bottom">
            <span>© 2024 Joker University Club. Tous droits réservés.</span>
            <span>Made with ♥ by the Joker Team</span>
          </div>
        </div>
      </footer>`;
  }

  function init() {
    renderNav();
    renderFooter();
  }

  return { init, navigateTo, handleNavClick, currentPage };
})();

/* ---------- TOAST MODULE ---------- */
const JokerToast = (() => {
  function getContainer() {
    let c = document.getElementById('toast-container');
    if (!c) {
      c = document.createElement('div');
      c.id = 'toast-container';
      document.body.appendChild(c);
    }
    return c;
  }

  function show(message, type = 'info', duration = 3500) {
    const icons = { success: '✅', error: '❌', warning: '⚠️', info: '💜' };
    const c = getContainer();
    const t = document.createElement('div');
    t.className = `toast ${type}`;
    t.innerHTML = `<span>${icons[type] || '•'}</span><span>${message}</span>`;
    c.appendChild(t);
    setTimeout(() => {
      t.classList.add('removing');
      setTimeout(() => t.remove(), 280);
    }, duration);
    return t;
  }

  return { show };
})();

/* ---------- MODAL MODULE ---------- */
const JokerModal = (() => {
  function open(id) {
    const el = document.getElementById(id);
    if (el) el.classList.add('open');
  }
  function close(id) {
    const el = document.getElementById(id);
    if (el) el.classList.remove('open');
  }
  function closeAll() {
    document.querySelectorAll('.modal-overlay').forEach(m => m.classList.remove('open'));
  }
  // Close on overlay click
  document.addEventListener('click', (e) => {
    if (e.target.classList.contains('modal-overlay')) closeAll();
  });
  return { open, close, closeAll };
})();

/* ---------- AUTO-INIT ---------- */
document.addEventListener('DOMContentLoaded', () => JokerNav.init());
