<?php
/**
 * views/admin/evenements.php
 * Gestion des événements (vue standalone — inclus via AdminController)
 * Utilisé quand on veut une page dédiée à la gestion des événements
 * Note : Le dashboard intégré gère aussi les événements via tab=events
 */

if (!function_exists('formatDateFr')) {
    function formatDateFr(string $date): string {
        if (!$date) return '';
        $mois = ['','Jan','Fév','Mar','Avr','Mai','Juin','Juil','Aoû','Sep','Oct','Nov','Déc'];
        [$y, $m, $d] = explode('-', $date);
        return intval($d) . ' ' . $mois[intval($m)] . ' ' . $y;
    }
}

$user      = $_SESSION['user'];
$initiales = mb_strtoupper(mb_substr($user['nom'], 0, 1))
           . mb_strtoupper(mb_substr(explode(' ', $user['nom'])[1] ?? '', 0, 1));
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Club Joker — Événements Admin</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
  <link rel="stylesheet" href="public/style.css">
</head>
<body>
<div class="dashboard-wrapper">

<!-- ══════ SIDEBAR ══════ -->
<aside class="sidebar" id="sidebar">
  <div class="sidebar-brand">🃏 Club <span>Joker</span></div>
  <div class="sidebar-role">Administration</div>
  <ul class="sidebar-menu">
    <?php
    $items = [
      'overview'  => ['icon'=>'speedometer2',   'label'=>'Vue d\'ensemble'],
      'events'    => ['icon'=>'calendar-event', 'label'=>'Événements'],
      'meetings'  => ['icon'=>'people',         'label'=>'Réunions'],
      'requests'  => ['icon'=>'person-check',   'label'=>'Demandes d\'adhésion'],
      'tasks'     => ['icon'=>'check2-square',  'label'=>'Tâches'],
    ];
    foreach ($items as $key => $item):
    ?>
    <li>
      <a href="index.php?page=admin_dashboard&tab=<?= $key ?>"
         class="nav-item <?= $key === 'events' ? 'active' : '' ?>"
         style="text-decoration:none">
        <i class="bi bi-<?= $item['icon'] ?>"></i>
        <?= $item['label'] ?>
      </a>
    </li>
    <?php endforeach; ?>
  </ul>
  <div class="sidebar-footer">
    <div class="sidebar-user">
      <div class="sidebar-avatar"><?= htmlspecialchars($initiales) ?></div>
      <div class="sidebar-user-info">
        <div class="name"><?= htmlspecialchars($user['nom']) ?></div>
        <div class="role">Président du club</div>
      </div>
    </div>
    <a href="index.php?page=logout"
       class="btn btn-sm w-100 mt-3"
       style="background:rgba(192,57,43,0.2);color:var(--red-light);border:none;border-radius:8px;font-weight:600">
      <i class="bi bi-box-arrow-left me-1"></i>Déconnexion
    </a>
  </div>
</aside>
<div class="sidebar-overlay" id="sidebar-overlay"></div>

<!-- ══════ MAIN ══════ -->
<main class="dashboard-main">
  <div class="dashboard-topbar">
    <div class="d-flex align-items-center gap-3">
      <button class="btn btn-sm d-lg-none" id="sidebar-toggle"
              style="background:var(--beige);border:none;border-radius:8px;padding:.4rem .7rem">
        <i class="bi bi-list fs-5 text-blue"></i>
      </button>
      <h1>Gestion des événements</h1>
    </div>
    <a href="index.php?page=accueil" class="btn btn-sm btn-joker-outline">
      <i class="bi bi-house me-1"></i>Site public
    </a>
  </div>

  <div class="dashboard-body">

    <!-- Flash -->
    <?php if (!empty($flash_success)): ?>
    <div class="alert alert-success alert-dismissible fade show rounded-joker mb-3" role="alert">
      <?= htmlspecialchars($flash_success) ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>
    <?php if (!empty($flash_error)): ?>
    <div class="alert alert-danger alert-dismissible fade show rounded-joker mb-3" role="alert">
      <?= htmlspecialchars($flash_error) ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <!-- Header section -->
    <div class="d-flex align-items-center justify-content-between mb-4">
      <div>
        <h4 class="text-blue mb-0">Tous les événements</h4>
        <p class="text-muted small mb-0">Créez, modifiez et supprimez les événements du club</p>
      </div>
      <button class="btn btn-joker-red" data-bs-toggle="modal" data-bs-target="#modalAjouterEvt">
        <i class="bi bi-plus-lg me-1"></i>Ajouter un événement
      </button>
    </div>

    <!-- Tableau événements -->
    <div class="dash-panel">
      <div class="dash-panel-body p-0">
        <div class="table-responsive">
          <table class="joker-table">
            <thead>
              <tr>
                <th>#</th>
                <th>Titre</th>
                <th>Date & Heure</th>
                <th>Lieu</th>
                <th>Type</th>
                <th>Inscrits</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($evenements)): ?>
                <tr><td colspan="7" class="text-center text-muted py-4">
                  <i class="bi bi-calendar-x fs-2 d-block mb-2"></i>Aucun événement
                </td></tr>
              <?php else: ?>
                <?php foreach ($evenements as $i => $e): ?>
                <tr>
                  <td class="text-muted small"><?= $i + 1 ?></td>
                  <td>
                    <strong><?= htmlspecialchars($e['titre']) ?></strong>
                    <?php if (!empty($e['description'])): ?>
                    <div class="small text-muted"><?= htmlspecialchars(mb_substr($e['description'], 0, 50)) ?>...</div>
                    <?php endif; ?>
                  </td>
                  <td>
                    <div><?= formatDateFr($e['date_evenement']) ?></div>
                    <?php if ($e['heure']): ?>
                    <div class="small text-muted"><i class="bi bi-clock"></i> <?= substr($e['heure'],0,5) ?></div>
                    <?php endif; ?>
                  </td>
                  <td><?= htmlspecialchars($e['lieu'] ?? '—') ?></td>
                  <td>
                    <span class="event-badge <?= $e['type'] === 'public' ? 'public' : 'private' ?>">
                      <?= $e['type'] === 'public' ? '🌐 Public' : '🔒 Privé' ?>
                    </span>
                  </td>
                  <td>
                    <span class="<?= (int)$e['nb_inscrits'] >= (int)$e['max_participants'] ? 'text-danger fw-bold' : '' ?>">
                      <?= (int)$e['nb_inscrits'] ?>/<?= (int)$e['max_participants'] ?>
                    </span>
                  </td>
                  <td>
                    <button class="btn btn-sm btn-joker-blue me-1"
                            data-bs-toggle="modal" data-bs-target="#modalModifEvt"
                            data-id="<?= $e['id'] ?>"
                            data-titre="<?= htmlspecialchars($e['titre']) ?>"
                            data-date="<?= $e['date_evenement'] ?>"
                            data-heure="<?= $e['heure'] ?? '' ?>"
                            data-lieu="<?= htmlspecialchars($e['lieu'] ?? '') ?>"
                            data-type="<?= $e['type'] ?>"
                            data-max="<?= $e['max_participants'] ?>"
                            data-desc="<?= htmlspecialchars($e['description'] ?? '') ?>">
                      <i class="bi bi-pencil"></i>
                    </button>
                    <a href="index.php?page=admin_supprimer_evenement&id=<?= $e['id'] ?>"
                       class="btn btn-sm btn-joker-red"
                       onclick="return confirm('Supprimer « <?= htmlspecialchars($e['titre']) ?> » ?')">
                      <i class="bi bi-trash"></i>
                    </a>
                  </td>
                </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

  </div><!-- /dashboard-body -->
</main>
</div><!-- /dashboard-wrapper -->

<!-- Modal Ajouter Événement -->
<div class="modal fade modal-joker" id="modalAjouterEvt" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="bi bi-calendar-plus me-2"></i>Ajouter un événement</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form method="POST" action="index.php?page=admin_ajouter_evenement">
        <div class="modal-body p-4">
          <div class="form-joker row g-3">
            <div class="col-md-8">
              <label class="form-label">Titre *</label>
              <input type="text" class="form-control" name="titre" required>
            </div>
            <div class="col-md-4">
              <label class="form-label">Type</label>
              <select class="form-select" name="type">
                <option value="public">🌐 Public</option>
                <option value="prive">🔒 Privé</option>
              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label">Date *</label>
              <input type="date" class="form-control" name="date_evenement" required>
            </div>
            <div class="col-md-4">
              <label class="form-label">Heure</label>
              <input type="time" class="form-control" name="heure">
            </div>
            <div class="col-md-4">
              <label class="form-label">Max participants</label>
              <input type="number" class="form-control" name="max_participants" value="30" min="1">
            </div>
            <div class="col-12">
              <label class="form-label">Lieu</label>
              <input type="text" class="form-control" name="lieu">
            </div>
            <div class="col-12">
              <label class="form-label">Description</label>
              <textarea class="form-control" name="description" rows="3"></textarea>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-joker-outline" data-bs-dismiss="modal">Annuler</button>
          <button type="submit" class="btn btn-joker-blue">
            <i class="bi bi-check-lg me-1"></i>Créer l'événement
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal Modifier Événement -->
<div class="modal fade modal-joker" id="modalModifEvt" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="bi bi-pencil-square me-2"></i>Modifier l'événement</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form method="POST" action="index.php?page=admin_modifier_evenement">
        <input type="hidden" name="id" id="modif-evt-id">
        <div class="modal-body p-4">
          <div class="form-joker row g-3">
            <div class="col-md-8">
              <label class="form-label">Titre *</label>
              <input type="text" class="form-control" name="titre" id="modif-evt-titre" required>
            </div>
            <div class="col-md-4">
              <label class="form-label">Type</label>
              <select class="form-select" name="type" id="modif-evt-type">
                <option value="public">🌐 Public</option>
                <option value="prive">🔒 Privé</option>
              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label">Date *</label>
              <input type="date" class="form-control" name="date_evenement" id="modif-evt-date">
            </div>
            <div class="col-md-4">
              <label class="form-label">Heure</label>
              <input type="time" class="form-control" name="heure" id="modif-evt-heure">
            </div>
            <div class="col-md-4">
              <label class="form-label">Max participants</label>
              <input type="number" class="form-control" name="max_participants" id="modif-evt-max" min="1">
            </div>
            <div class="col-12">
              <label class="form-label">Lieu</label>
              <input type="text" class="form-control" name="lieu" id="modif-evt-lieu">
            </div>
            <div class="col-12">
              <label class="form-label">Description</label>
              <textarea class="form-control" name="description" rows="3" id="modif-evt-desc"></textarea>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-joker-outline" data-bs-dismiss="modal">Annuler</button>
          <button type="submit" class="btn btn-joker-blue">
            <i class="bi bi-check-lg me-1"></i>Sauvegarder
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.getElementById('sidebar-toggle')?.addEventListener('click', () => {
  document.getElementById('sidebar').classList.toggle('open');
  document.getElementById('sidebar-overlay').classList.toggle('active');
});
document.getElementById('sidebar-overlay')?.addEventListener('click', () => {
  document.getElementById('sidebar').classList.remove('open');
  document.getElementById('sidebar-overlay').classList.remove('active');
});

document.getElementById('modalModifEvt')?.addEventListener('show.bs.modal', e => {
  const b = e.relatedTarget;
  document.getElementById('modif-evt-id').value    = b.dataset.id;
  document.getElementById('modif-evt-titre').value = b.dataset.titre;
  document.getElementById('modif-evt-date').value  = b.dataset.date;
  document.getElementById('modif-evt-heure').value = b.dataset.heure;
  document.getElementById('modif-evt-lieu').value  = b.dataset.lieu;
  document.getElementById('modif-evt-max').value   = b.dataset.max;
  document.getElementById('modif-evt-desc').value  = b.dataset.desc;
  document.getElementById('modif-evt-type').value  = b.dataset.type;
});
</script>
</body>
</html>