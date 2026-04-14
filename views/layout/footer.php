<?php
/**
 * views/layout/footer.php
 * Pied de page commun
 */
?>
<!-- ═══════ FOOTER ═══════ -->
<footer class="footer-joker">
  <div class="container">
    <div class="row g-4">
      <div class="col-lg-4">
        <div class="footer-brand mb-2">🃏 Club <span style="color:var(--red-light)">Joker</span></div>
        <p class="small" style="color:rgba(245,236,215,0.55)">
          Un club universitaire dédié à l'innovation, le leadership et la croissance personnelle.
        </p>
      </div>
      <div class="col-lg-2 col-sm-4">
        <div class="fw-bold mb-2 small" style="color:var(--beige);letter-spacing:.5px">Navigation</div>
        <ul class="list-unstyled small">
          <li class="mb-1"><a href="index.php?page=accueil">Accueil</a></li>
          <li class="mb-1"><a href="index.php?page=evenements">Événements</a></li>
          <li class="mb-1"><a href="index.php?page=reunions">Réunions</a></li>
          <li class="mb-1"><a href="index.php?page=accueil#rejoindre">Nous rejoindre</a></li>
        </ul>
      </div>
      <div class="col-lg-2 col-sm-4">
        <div class="fw-bold mb-2 small" style="color:var(--beige);letter-spacing:.5px">Compte</div>
        <ul class="list-unstyled small">
          <li class="mb-1"><a href="index.php?page=login">Connexion</a></li>
          <li class="mb-1"><a href="index.php?page=membre_dashboard">Espace Membre</a></li>
          <li class="mb-1"><a href="index.php?page=admin_dashboard">Espace Admin</a></li>
        </ul>
      </div>
      <div class="col-lg-4 col-sm-4">
        <div class="fw-bold mb-2 small" style="color:var(--beige);letter-spacing:.5px">Contact</div>
        <ul class="list-unstyled small">
          <li class="mb-1"><i class="bi bi-envelope-fill me-2 text-red"></i>joker@univ.tn</li>
          <li class="mb-1"><i class="bi bi-geo-alt-fill me-2 text-red"></i>Campus Universitaire, Tunis</li>
          <li class="mb-1"><i class="bi bi-instagram me-2 text-red"></i>@clubjoker.univ</li>
        </ul>
      </div>
    </div>
    <hr class="footer-divider">
    <div class="row align-items-center">
      <div class="col-sm-6 small" style="color:rgba(245,236,215,0.4)">
        © 2025 Club Joker. Tous droits réservés.
      </div>
      <div class="col-sm-6 text-sm-end mt-2 mt-sm-0">
        <span class="small" style="color:rgba(245,236,215,0.35)">Fait avec ❤️ par les membres du club</span>
      </div>
    </div>
  </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<!-- JS uniquement pour Bootstrap modals + sidebar toggle -->
<script>
  // Sidebar toggle pour les dashboards
  document.getElementById('sidebar-toggle')?.addEventListener('click', () => {
    document.getElementById('sidebar')?.classList.toggle('open');
    document.getElementById('sidebar-overlay')?.classList.toggle('active');
  });
  document.getElementById('sidebar-overlay')?.addEventListener('click', () => {
    document.getElementById('sidebar')?.classList.remove('open');
    document.getElementById('sidebar-overlay')?.classList.remove('active');
  });
</script>
</body>
</html>