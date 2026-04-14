<?php
/**
 * views/public/evenements.php
 */
$titreePage   = 'Événements';
$pageCourante = 'evenements';
require_once __DIR__ . '/../layout/header.php';

if (!function_exists('formatDateFr')) {
    function formatDateFr(string $date): string {
        if (!$date) return '';
        $mois = ['','Jan','Fév','Mar','Avr','Mai','Juin','Juil','Aoû','Sep','Oct','Nov','Déc'];
        [$y, $m, $d] = explode('-', $date);
        return intval($d) . ' ' . $mois[intval($m)] . ' ' . $y;
    }
}

$filtreCourant = $_GET['filtre'] ?? 'tous';
$recherche     = $_GET['q'] ?? '';
?>

<!-- Flash -->
<?php if (!empty($flash_success)): ?>
<div class="alert alert-success alert-dismissible fade show position-fixed bottom-0 end-0 m-3"
     style="z-index:9999;border-radius:var(--radius)" role="alert">
  <?= htmlspecialchars($flash_success) ?>
  <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>
<?php if (!empty($flash_error)): ?>
<div class="alert alert-danger alert-dismissible fade show position-fixed bottom-0 end-0 m-3"
     style="z-index:9999;border-radius:var(--radius)" role="alert">
  <?= htmlspecialchars($flash_error) ?>
  <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<!-- Page Header -->
<div class="page-header">
  <div class="container" style="position:relative;z-index:2;">
    <h1 class="animate-fadeInUp">📅 Événements</h1>
    <p class="animate-fadeInUp delay-1">Découvrez et inscrivez-vous à nos événements.</p>
  </div>
</div>

<!-- Événements -->
<section class="section-pad">
  <div class="container">

    <!-- Barre filtres + recherche -->
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4">
      <div class="section-heading mb-0">
        <span class="overline">Tous nos événements</span>
        <h2 style="font-size:1.8rem">Programme du semestre</h2>
      </div>
      <div class="d-flex gap-2 align-items-center flex-wrap">
        <!-- Recherche -->
        <form method="GET" action="index.php" class="d-flex gap-2">
          <input type="hidden" name="page" value="evenements">
          <input type="text" name="q" class="form-control form-control-sm rounded-joker"
                 placeholder="Rechercher..." value="<?= htmlspecialchars($recherche) ?>"
                 style="width:180px">
          <button type="submit" class="btn btn-joker-blue btn-sm">
            <i class="bi bi-search"></i>
          </button>
        </form>
        <!-- Filtre type -->
        <form method="GET" action="index.php">
          <input type="hidden" name="page" value="evenements">
          <?php if ($recherche): ?>
            <input type="hidden" name="q" value="<?= htmlspecialchars($recherche) ?>">
          <?php endif; ?>
          <select class="form-select form-select-sm rounded-joker" name="filtre"
                  onchange="this.form.submit()" style="width:150px">
            <option value="tous"   <?= $filtreCourant === 'tous'   ? 'selected' : '' ?>>Tous</option>
            <option value="public" <?= $filtreCourant === 'public' ? 'selected' : '' ?>>🌐 Publics</option>
            <option value="prive"  <?= $filtreCourant === 'prive'  ? 'selected' : '' ?>>🔒 Privés</option>
          </select>
        </form>
      </div>
    </div>

    <div class="row g-4">
      <?php if (empty($evenements)): ?>
        <div class="col-12 empty-state">
          <i class="bi bi-calendar-x"></i>
          <p>Aucun événement trouvé.</p>
        </div>
      <?php else: ?>
        <?php foreach ($evenements as $evt):
          $nbInscrits = (int) $evt['nb_inscrits'];
          $spots      = (int) $evt['max_participants'] - $nbInscrits;
          $typeClass  = $evt['type'] === 'public' ? 'public' : 'private';
        ?>
        <div class="col-md-6 col-lg-4 animate-fadeInUp">
          <div class="joker-card h-100">
            <div class="card-body p-4">
              <div class="d-flex align-items-start justify-content-between mb-3">
                <span class="event-badge <?= $typeClass ?>">
                  <?= $evt['type'] === 'public' ? '🌐 Public' : '🔒 Privé' ?>
                </span>
                <small class="text-muted fw-bold"><?= formatDateFr($evt['date_evenement']) ?></small>
              </div>
              <h5 class="text-blue fw-bold mb-2"><?= htmlspecialchars($evt['titre']) ?></h5>
              <p class="text-muted small mb-3"><?= htmlspecialchars($evt['description'] ?? '') ?></p>
              <div class="d-flex align-items-center gap-3 mb-3 flex-wrap">
                <span class="small"><i class="bi bi-geo-alt text-red"></i> <?= htmlspecialchars($evt['lieu'] ?? '') ?></span>
                <span class="small"><i class="bi bi-clock text-blue"></i> <?= $evt['heure'] ? substr($evt['heure'],0,5) : '' ?></span>
              </div>
              <div class="d-flex align-items-center justify-content-between">
                <span class="participant-count">
                  <i class="bi bi-people-fill"></i> <?= $nbInscrits ?>/<?= $evt['max_participants'] ?>
                </span>
                <?php if ($evt['type'] === 'public' && $spots > 0): ?>
                  <button class="btn btn-joker-red btn-sm"
                          data-bs-toggle="modal"
                          data-bs-target="#modalInscription"
                          data-id="<?= $evt['id'] ?>"
                          data-titre="<?= htmlspecialchars($evt['titre']) ?>">
                    S'inscrire
                  </button>
                <?php elseif ($spots <= 0): ?>
                  <span class="badge-status refused">Complet</span>
                <?php else: ?>
                  <span class="badge-status active">Membres</span>
                <?php endif; ?>
              </div>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>
</section>

<!-- Modal Inscription -->
<div class="modal fade modal-joker" id="modalInscription" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Inscription : <span id="modal-evt-titre"></span></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form method="POST" action="index.php?page=inscrire_evenement">
        <input type="hidden" name="id_evenement" id="modal-evt-id">
        <div class="modal-body p-4">
          <div class="form-joker row g-3">
            <div class="col-12">
              <label class="form-label">Nom complet *</label>
              <input type="text" class="form-control" name="nom" placeholder="Votre nom" required>
            </div>
            <div class="col-12">
              <label class="form-label">Email *</label>
              <input type="email" class="form-control" name="email" placeholder="votre@email.com" required>
            </div>
            <div class="col-12">
              <label class="form-label">Téléphone</label>
              <input type="tel" class="form-control" name="telephone">
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-joker-blue">
            <i class="bi bi-check-lg me-1"></i>Confirmer l'inscription
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- CTA -->
<section style="background:var(--beige);padding:4rem 0">
  <div class="container text-center">
    <h3 class="text-blue mb-2">Vous n'êtes pas encore membre ?</h3>
    <p class="text-muted mb-3">Rejoignez le club pour accéder aux événements privés.</p>
    <a href="index.php?page=accueil#rejoindre" class="btn btn-joker-red me-2">Nous rejoindre</a>
    <a href="index.php?page=login" class="btn btn-joker-outline">Se connecter</a>
  </div>
</section>

<script>
document.getElementById('modalInscription')?.addEventListener('show.bs.modal', function(e) {
  const btn = e.relatedTarget;
  document.getElementById('modal-evt-id').value = btn.getAttribute('data-id');
  document.getElementById('modal-evt-titre').textContent = btn.getAttribute('data-titre');
});
</script>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>