<?php
/**
 * views/public/accueil.php
 * Page d'accueil — MAJ : suppression form rejoindre, bouton redirect vers page rejoindre
 */
$titreePage   = 'Accueil';
$pageCourante = 'accueil';
require_once __DIR__ . '/../layout/header.php';

// Compter les membres pour les stats
require_once __DIR__ . '/../../models/UtilisateurModel.php';
$utilisateurModel = new UtilisateurModel();
$nbMembres        = $utilisateurModel->compterMembres();

// Variables flash
$flash_success = $flash_success ?? null;
$flash_error   = $flash_error   ?? null;
?>

<!-- Helper formatage date -->
<?php
function formatDateFr(string $date): string {
    if (!$date) return '';
    $mois = ['','Jan','Fév','Mar','Avr','Mai','Juin','Juil','Aoû','Sep','Oct','Nov','Déc'];
    [$y, $m, $d] = explode('-', $date);
    return intval($d) . ' ' . $mois[intval($m)] . ' ' . $y;
}
?>

<!-- ═══════ FLASH MESSAGES ═══════ -->
<?php if ($flash_success): ?>
<div class="alert alert-success alert-dismissible fade show position-fixed bottom-0 end-0 m-3"
     style="z-index:9999;border-radius:var(--radius);min-width:280px" role="alert">
  <?= htmlspecialchars($flash_success) ?>
  <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>
<?php if ($flash_error): ?>
<div class="alert alert-danger alert-dismissible fade show position-fixed bottom-0 end-0 m-3"
     style="z-index:9999;border-radius:var(--radius);min-width:280px" role="alert">
  <?= htmlspecialchars($flash_error) ?>
  <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<!-- ═══════ HERO ═══════ -->
<section class="hero-section">
  <div class="container">
    <div class="row align-items-center g-5">
      <div class="col-lg-7 hero-content">
        <div class="hero-badge">✨ Club Universitaire #1</div>
        <h1 class="hero-title animate-fadeInUp">
          Bienvenue au<br>Club <span>Joker</span>
        </h1>
        <p class="hero-subtitle animate-fadeInUp delay-1">
          Un espace d'innovation, de leadership et de développement personnel pour les étudiants ambitieux.
          Rejoignez notre communauté et transformez vos idées en réalité.
        </p>
        <div class="d-flex gap-3 flex-wrap animate-fadeInUp delay-2">
          <!-- ✅ Redirige vers la page rejoindre (plus d'ancre #rejoindre) -->
          <a href="index.php?page=rejoindre" class="btn btn-joker-red">
            Rejoindre le club <i class="bi bi-arrow-right ms-1"></i>
          </a>
          <a href="index.php?page=evenements"
             class="btn btn-joker-outline"
             style="color:var(--beige);border-color:rgba(245,236,215,0.5)">
            Voir les événements
          </a>
        </div>
        <div class="hero-stats animate-fadeInUp delay-3">
          <div class="hero-stat">
            <span class="stat-num"><?= $nbMembres ?: 120 ?></span>
            <span class="stat-label">Membres actifs</span>
          </div>
          <div class="hero-stat">
            <span class="stat-num">48</span>
            <span class="stat-label">Événements/an</span>
          </div>
          <div class="hero-stat">
            <span class="stat-num">5</span>
            <span class="stat-label">Années d'existence</span>
          </div>
        </div>
      </div>

      <!-- Carte du prochain événement -->
      <div class="col-lg-5 d-none d-lg-block animate-fadeInUp delay-2">
        <?php if (!empty($evenements[0])): $evt = $evenements[0]; ?>
        <div class="hero-card-float">
          <div class="card-icon">🃏</div>
          <h4 style="color:var(--beige);font-size:1.3rem;margin-bottom:.5rem">Prochain événement</h4>
          <p style="color:rgba(245,236,215,0.7);font-size:.9rem;margin-bottom:1.2rem">
            <?= htmlspecialchars($evt['titre']) ?> — <?= formatDateFr($evt['date_evenement']) ?>
          </p>
          <div class="d-flex gap-2 flex-wrap mb-3">
            <span class="badge-status active" style="background:rgba(245,236,215,.1);color:var(--beige)">
              <?= htmlspecialchars($evt['lieu'] ?? '') ?>
            </span>
          </div>
          <div class="d-flex align-items-center justify-content-between">
            <span style="color:rgba(245,236,215,0.65);font-size:.85rem">
              <i class="bi bi-people-fill me-1"></i>
              <?= (int)$evt['nb_inscrits'] ?> / <?= (int)$evt['max_participants'] ?> inscrits
            </span>
            <a href="index.php?page=evenements" class="btn btn-joker-red btn-sm">S'inscrire</a>
          </div>
        </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</section>

<!-- ═══════ ABOUT ═══════ -->
<section class="about-section" id="a-propos">
  <div class="container">
    <div class="row align-items-center g-5">
      <div class="col-lg-5">
        <div class="section-heading">
          <span class="overline">À propos du club</span>
          <h2>Un club fait par et pour les étudiants</h2>
          <div class="section-divider"></div>
          <p>Fondé en 2020, le Club Joker est un espace d'épanouissement académique et personnel.
             Nous organisons des événements, des formations et des projets innovants.</p>
        </div>
        <!-- ✅ Lien vers page rejoindre -->
        <a href="index.php?page=rejoindre" class="btn btn-joker-blue">Rejoindre l'aventure</a>
      </div>
      <div class="col-lg-7">
        <div class="row g-3">
          <div class="col-sm-6">
            <div class="joker-card p-4">
              <div class="value-icon red"><i class="bi bi-trophy-fill"></i></div>
              <h5 class="text-blue mb-2">Excellence</h5>
              <p class="text-muted small mb-0">Nous visons l'excellence dans tout ce que nous faisons.</p>
            </div>
          </div>
          <div class="col-sm-6">
            <div class="joker-card p-4">
              <div class="value-icon blue"><i class="bi bi-people-fill"></i></div>
              <h5 class="text-blue mb-2">Communauté</h5>
              <p class="text-muted small mb-0">Construire des liens durables entre étudiants.</p>
            </div>
          </div>
          <div class="col-sm-6">
            <div class="joker-card p-4">
              <div class="value-icon gold"><i class="bi bi-lightbulb-fill"></i></div>
              <h5 class="text-blue mb-2">Innovation</h5>
              <p class="text-muted small mb-0">Hackathons et projets collaboratifs innovants.</p>
            </div>
          </div>
          <div class="col-sm-6">
            <div class="joker-card p-4">
              <div class="value-icon red"><i class="bi bi-graph-up-arrow"></i></div>
              <h5 class="text-blue mb-2">Croissance</h5>
              <p class="text-muted small mb-0">Des formations pour développer vos compétences.</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ═══════ ÉVÉNEMENTS PUBLICS ═══════ -->
<section class="events-section">
  <div class="container">
    <div class="section-heading text-center">
      <span class="overline">Événements à venir</span>
      <h2>Ne manquez aucun événement</h2>
      <div class="section-divider mx-auto"></div>
      <p class="mx-auto">Découvrez nos événements publics ouverts à tous les étudiants.</p>
    </div>

    <div class="row g-4">
      <?php if (empty($evenements)): ?>
        <div class="col-12 empty-state">
          <i class="bi bi-calendar-x"></i>
          <p>Aucun événement public pour le moment.</p>
        </div>
      <?php else: ?>
        <?php foreach ($evenements as $evt):
          $nbInscrits = (int) $evt['nb_inscrits'];
          $spots      = (int) $evt['max_participants'] - $nbInscrits;
        ?>
        <div class="col-md-6 col-lg-4 animate-fadeInUp">
          <div class="joker-card h-100">
            <div class="card-body p-4">
              <div class="d-flex align-items-start justify-content-between mb-3">
                <span class="event-badge public">🌐 Public</span>
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
                <?php if ($spots > 0): ?>
                  <button class="btn btn-joker-red btn-sm"
                          data-bs-toggle="modal"
                          data-bs-target="#modalInscription"
                          data-id="<?= $evt['id'] ?>"
                          data-titre="<?= htmlspecialchars($evt['titre']) ?>">
                    S'inscrire
                  </button>
                <?php else: ?>
                  <span class="badge-status refused">Complet</span>
                <?php endif; ?>
              </div>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>

    <div class="text-center mt-4">
      <a href="index.php?page=evenements" class="btn btn-joker-blue">
        Voir tous les événements <i class="bi bi-arrow-right ms-1"></i>
      </a>
    </div>
  </div>
</section>

<!-- ═══════ MODAL INSCRIPTION ÉVÉNEMENT ═══════ -->
<div class="modal fade modal-joker" id="modalInscription" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">S'inscrire à : <span id="modal-evt-titre"></span></h5>
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
              <input type="tel" class="form-control" name="telephone" placeholder="XX XXX XXX">
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

<!-- ═══════ SECTION REJOINDRE — CTA simple (sans formulaire) ═══════ -->
<section class="join-section" id="rejoindre">
  <div class="container" style="position:relative;z-index:2;">
    <div class="row justify-content-center">
      <div class="col-lg-7 text-center">
        <span class="overline text-beige" style="opacity:.8">Devenir membre</span>
        <h2 class="text-beige mb-3" style="font-size:clamp(1.8rem,4vw,3rem)">Rejoignez le Club Joker</h2>
        <p style="color:rgba(245,236,215,0.75);font-size:1.05rem;margin-bottom:2rem">
          Créez votre compte, choisissez votre mot de passe et rejoignez notre communauté.
          L'admin examinera votre candidature et activera votre accès.
        </p>
        <!-- ✅ Bouton redirige vers la vraie page rejoindre avec le formulaire complet -->
        <a href="index.php?page=rejoindre" class="btn btn-joker-blue btn-lg px-5 py-3">
          <i class="bi bi-person-plus-fill me-2"></i>Créer mon compte
        </a>
        <p class="mt-3" style="color:rgba(245,236,215,0.5);font-size:.9rem">
          Déjà membre ? <a href="index.php?page=login" style="color:var(--beige);font-weight:bold">Connectez-vous</a>
        </p>
      </div>
    </div>
  </div>
</section>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.getElementById('modalInscription')?.addEventListener('show.bs.modal', function(e) {
  const btn = e.relatedTarget;
  document.getElementById('modal-evt-id').value             = btn.getAttribute('data-id');
  document.getElementById('modal-evt-titre').textContent    = btn.getAttribute('data-titre');
});
</script>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>