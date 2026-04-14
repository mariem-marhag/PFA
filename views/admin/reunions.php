<?php
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
<div class="page-header">
  <div class="container" style="position:relative;z-index:2;">
    <h1 class="animate-fadeInUp">🤝 Réunions</h1>
    <p class="animate-fadeInUp delay-1">Consultez le planning des réunions du club.</p>
  </div>
</div>

<section class="section-pad">
  <div class="container">
    <div class="section-heading mb-4">
      <span class="overline">Planning</span>
      <h2>Réunions à venir</h2>
      <div class="section-divider"></div>
    </div>
    <div class="alert d-flex align-items-center gap-3 mb-4"
         style="background:rgba(26,58,107,0.08);border:1px solid rgba(26,58,107,0.15);border-radius:var(--radius);color:var(--blue)">
      <i class="bi bi-info-circle-fill fs-5"></i>
      <div>
        <strong>Accès membres :</strong> Certaines réunions sont réservées aux membres.
        <a href="index.php?page=login" class="fw-bold ms-1" style="color:var(--red)">Connectez-vous</a> ou
        <a href="index.php?page=accueil#rejoindre" class="fw-bold ms-1" style="color:var(--red)">rejoignez le club</a>.
      </div>
    </div>

    <div class="meeting-grid">
      <?php if (empty($reunions)): ?>
        <div class="empty-state">
          <i class="bi bi-calendar2-x"></i>
          <p>Aucune réunion planifiée.</p>
        </div>
      <?php else: ?>
        <?php foreach ($reunions as $r): ?>
        <div class="meeting-card animate-fadeInUp">
          <div class="meeting-date"><?= formatDateFr($r['date_reunion']) ?></div>
          <div class="meeting-title"><?= htmlspecialchars($r['titre']) ?></div>
          <p class="small text-muted mb-2"><?= htmlspecialchars($r['ordre_du_jour'] ?? '') ?></p>
          <div class="d-flex gap-2 flex-wrap">
            <span class="small"><i class="bi bi-clock text-blue"></i> <?= $r['heure'] ? substr($r['heure'],0,5) : '' ?></span>
            <span class="small"><i class="bi bi-geo-alt text-red"></i> <?= htmlspecialchars($r['lieu'] ?? '') ?></span>
          </div>
          <span class="badge-status active mt-2 d-inline-block"><?= htmlspecialchars($r['type']) ?></span>
        </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>
</section>

<section style="background:var(--beige);padding:4rem 0">
  <div class="container text-center">
    <h3 class="text-blue mb-2">Vous voulez participer aux réunions ?</h3>
    <p class="text-muted mb-3">Devenez membre pour accéder aux réunions internes.</p>
    <a href="index.php?page=accueil#rejoindre" class="btn btn-joker-red me-2">Devenir membre</a>
    <a href="index.php?page=login" class="btn btn-joker-outline">Se connecter</a>
  </div>
</section>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>