/* ============================================================
   JOKER UNIVERSITY CLUB — login.js
   ============================================================ */

document.addEventListener('DOMContentLoaded', () => {

  // Redirect if already logged in
  const existing = JokerAuth.getUser();
  if (existing) {
    JokerNav.navigateTo(existing.role === 'admin' ? 'admin' : 'member');
    return;
  }

  /* ---- Toggle password visibility ---- */
  document.getElementById('toggle-pwd')?.addEventListener('click', () => {
    const input = document.getElementById('login-password');
    const btn   = document.getElementById('toggle-pwd');
    if (input.type === 'password') {
      input.type = 'text';
      btn.textContent = '🙈';
    } else {
      input.type = 'password';
      btn.textContent = '👁';
    }
  });

  /* ---- Form submit ---- */
  document.getElementById('login-form')?.addEventListener('submit', (e) => {
    e.preventDefault();

    const email    = document.getElementById('login-email').value.trim();
    const password = document.getElementById('login-password').value;
    const btn      = document.getElementById('login-btn');

    // Clear errors
    document.querySelectorAll('.form-error').forEach(el => el.classList.remove('show'));

    // Basic validation
    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
      document.getElementById('err-email').classList.add('show');
      return;
    }

    // Loading state
    btn.disabled = true;
    btn.textContent = '⏳ Connexion…';

    setTimeout(() => {
      const result = JokerAuth.login(email, password);

      if (!result.success) {
        btn.disabled = false;
        btn.textContent = 'Se connecter';
        document.getElementById('err-password').textContent = 'Email ou mot de passe incorrect.';
        document.getElementById('err-password').classList.add('show');
        JokerToast.show('❌ Identifiants invalides.', 'error');
        return;
      }

      JokerToast.show(`✅ Bienvenue, ${result.user.name} !`, 'success');

      setTimeout(() => {
        JokerNav.navigateTo(result.user.role === 'admin' ? 'admin' : 'member');
      }, 600);
    }, 800);
  });

});
