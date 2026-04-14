<?php
/**
 * views/public/reunions.php
 * Page publique des réunions — Club Joker
 * Fidèle au frontend reunions.html
 */

$titreePage   = 'Réunions';
$pageCourante = 'reunions';
require_once __DIR__ . '/../layout/header.php';

if (!function_exists('formatDateFr')) {
    function formatDateFr(string $date): string {
        if (!$date) return '';
        $mois = ['','Jan','Fév','Mar','Avr','Mai','Juin','Juil','Aoû','Sep','Oct','Nov','Déc'];
        [$y, $m, $d] = explode('-', $date);
        return intval($d) . ' ' . $mois[intval($m)] . ' ' . $y;
    }
}
?>

<!-- ═══════ PAGE HEADER ═══════ -->
<div class="page-header">
  <div class="container" style="position:relative;z-index:2;">
    <h1 class="animate-fadeInUp">🤝 Réunions</h1>
    <p class="animate-fadeInUp delay-1">Consultez le planning des réunions du club.</p>
  </div>
</div>

<!-- ═══════ LISTE DES RÉUNIONS ═══════ -->
<section class="section-pad">
  <div class="container">

    <div class="section-heading mb-4">
      <span class="overline">Planning</span>
      <h2>Réunions à venir</h2>
      <div class="section-divider"></div>
    </div>

    <!-- Bannière info accès membres -->
    <div class="alert d-flex align-items-center gap-3 mb-4"
         style="background:rgba(26,58,107,0.08);border:1px solid rgba(26,58,107,0.15);
                border-radius:var(--radius);color:var(--blue)">
      <i class="bi bi-info-circle-fill fs-5" style="color:var(--blue)"></i>
      <div>
        <strong>Accès membres :</strong> Certaines réunions sont réservées aux membres du club.
        <a href="index.php?page=login" class="fw-bold ms-1" style="color:var(--red)">Connectez-vous</a> ou
        <a href="index.php?page=accueil#rejoindre" class="fw-bold ms-1" style="color:var(--red)">rejoignez le club</a>.
      </div>
    </div>

    <!-- Grille des réunions -->
    <div class="meeting-grid">
      <?php if (empty($reunions)): ?>
        <div class="empty-state" style="grid-column:1/-1">
          <i class="bi bi-calendar2-x"></i>
          <p>Aucune réunion planifiée pour le moment.</p>
          <p class="small text-muted">Revenez bientôt ou connectez-vous pour voir toutes les réunions.</p>
        </div>
      <?php else: ?>
        <?php foreach ($reunions as $r): ?>
        <div class="meeting-card animate-fadeInUp">
          <!-- Date mise en avant -->
          <div class="meeting-date"><?= formatDateFr($r['date_reunion']) ?></div>

          <!-- Titre -->
          <div class="meeting-title"><?= htmlspecialchars($r['titre']) ?></div>

          <!-- Ordre du jour -->
          <?php if (!empty($r['ordre_du_jour'])): ?>
          <p class="small text-muted mb-2 mt-1">
            <?= nl2br(htmlspecialchars($r['ordre_du_jour'])) ?>
          </p>
          <?php endif; ?>

          <!-- Heure + Lieu -->
          <div class="d-flex gap-3 flex-wrap mt-2 mb-2">
            <?php if (!empty($r['heure'])): ?>
            <span class="small">
              <i class="bi bi-clock" style="color:var(--blue)"></i>
              <?= substr($r['heure'], 0, 5) ?>
            </span>
            <?php endif; ?>
            <?php if (!empty($r['lieu'])): ?>
            <span class="small">
              <i class="bi bi-geo-alt" style="color:var(--red)"></i>
              <?= htmlspecialchars($r['lieu']) ?>
            </span>
            <?php endif; ?>
          </div>

          <!-- Type -->
          <span class="badge-status active d-inline-block">
            <?= htmlspecialchars(ucfirst($r['type'])) ?>
          </span>

          <!-- Accès réservé pour réunions internes -->
          <?php if ($r['type'] === 'interne'): ?>
          <div class="mt-2">
            <?php if (isset($_SESSION['user'])): ?>
              <span class="small text-muted">
                <i class="bi bi-lock-fill" style="color:var(--blue)"></i> Réunion interne
              </span>
            <?php else: ?>
              <a href="index.php?page=login"
                 class="small fw-bold" style="color:var(--red)">
                <i class="bi bi-lock"></i> Réservé aux membres — Connexion
              </a>
            <?php endif; ?>
          </div>
          <?php endif; ?>
        </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>

  </div>
</section>

<!-- ═══════ CTA BAS DE PAGE ═══════ -->
<section style="background:var(--beige);padding:4rem 0">
  <div class="container text-center">
    <h3 class="text-blue mb-2">Vous voulez participer aux réunions ?</h3>
    <p class="text-muted mb-3">
      Devenez membre du club pour accéder aux réunions internes et participer aux décisions.
    </p>
    <a href="index.php?page=accueil#rejoindre" class="btn btn-joker-red me-2">Devenir membre</a>
    <a href="index.php?page=login" class="btn btn-joker-outline">Se connecter</a>
  </div>
</section>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>
