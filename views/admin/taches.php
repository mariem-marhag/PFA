<?php
/**
 * views/admin/taches.php
 * Gestion des tâches — vue standalone admin
 */

if (!function_exists('formatDateFr')) {
    function formatDateFr(string $date): string {
        if (!$date) return '';
        $mois = ['','Jan','Fév','Mar','Avr','Mai','Juin','Juil','Aoû','Sep','Oct','Nov','Déc'];
        [$y, $m, $d] = explode('-', $date);
        return intval($d) . ' ' . $mois[intval($m)] . ' ' . $y;
    }
}

function prioriteBadge(string $p): string {
    $map = [
        'haute'   => ['danger',    '🔴 Haute'],
        'moyenne' => ['warning',   '🟡 Moyenne'],
        'faible'  => ['secondary', '🟢 Faible'],
    ];
    [$cls, $label] = $map[$p] ?? ['secondary','—'];
    return "<span class='badge bg-{$cls}'>{$label}</span>";
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
  <title>Club Joker — Tâches</title>
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
         class="nav-item <?= $key === 'tasks' ? 'active' : '' ?>"
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
      <h1>Gestion des tâches</h1>
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

    <!-- Header -->
    <div class="d-flex align-items-center justify-content-between mb-4">
      <div>
        <h4 class="text-blue mb-0">Tâches assignées aux membres</h4>
        <p class="text-muted small mb-0">Créez et suivez les tâches de l'équipe</p>
      </div>
      <button class="btn btn-joker-red" data-bs-toggle="modal" data-bs-target="#modalAjouterTache">
        <i class="bi bi-plus-lg me-1"></i>Assigner une tâche
      </button>
    </div>

    <!-- Stats rapides -->
    <?php
    $nbCours   = 0; $nbTermine = 0;
    foreach (($taches ?? []) as $t) {
        if ($t['statut'] === 'en_cours') $nbCours++;
        else $nbTermine++;
    }
    ?>
    <div class="row g-3 mb-4">
      <div class="col-sm-4">
        <div class="stat-card">
          <div class="stat-icon blue"><i class="bi bi-list-task"></i></div>
          <div><div class="stat-value"><?= count($taches ?? []) ?></div><div class="stat-label">Total</div></div>
        </div>
      </div>
      <div class="col-sm-4">
        <div class="stat-card">
          <div class="stat-icon gold"><i class="bi bi-hourglass-split"></i></div>
          <div><div class="stat-value"><?= $nbCours ?></div><div class="stat-label">En cours</div></div>
        </div>
      </div>
      <div class="col-sm-4">
        <div class="stat-card">
          <div class="stat-icon green" style="background:rgba(39,174,96,.1)"><i class="bi bi-check-circle-fill" style="color:#27ae60"></i></div>
          <div><div class="stat-value"><?= $nbTermine ?></div><div class="stat-label">Terminées</div></div>
        </div>
      </div>
    </div>

    <!-- Tableau tâches -->
    <div class="dash-panel">
      <div class="dash-panel-body p-0">
        <div class="table-responsive">
          <table class="joker-table">
            <thead>
              <tr>
                <th>Tâche</th>
                <th>Assigné à</th>
                <th>Deadline</th>
                <th>Priorité</th>
                <th>Statut</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($taches)): ?>
                <tr><td colspan="6" class="text-center text-muted py-5">
                  <i class="bi bi-check2-all fs-2 d-block mb-2"></i>Aucune tâche assignée
                </td></tr>
              <?php else: ?>
                <?php foreach ($taches as $t): ?>
                <tr class="<?= $t['statut'] === 'termine' ? 'opacity-75' : '' ?>">
                  <td>
                    <strong class="<?= $t['statut'] === 'termine' ? 'text-decoration-line-through text-muted' : '' ?>">
                      <?= htmlspecialchars($t['titre']) ?>
                    </strong>
                  </td>
                  <td>
                    <?php if (!empty($t['assigné_nom'])): ?>
                    <div class="d-flex align-items-center gap-2">
                      <div class="sidebar-avatar" style="width:30px;height:30px;font-size:.7rem;background:var(--blue);flex-shrink:0">
                        <?= mb_strtoupper(mb_substr($t['assigné_nom'], 0, 2)) ?>
                      </div>
                      <?= htmlspecialchars($t['assigné_nom']) ?>
                    </div>
                    <?php else: ?>
                      <span class="text-muted">—</span>
                    <?php endif; ?>
                  </td>
                  <td>
                    <?php if ($t['deadline']): ?>
                      <?php
                      $isLate = strtotime($t['deadline']) < time() && $t['statut'] === 'en_cours';
                      ?>
                      <span class="<?= $isLate ? 'text-danger fw-bold' : '' ?>">
                        <?= $isLate ? '⚠️ ' : '' ?><?= formatDateFr($t['deadline']) ?>
                      </span>
                    <?php else: ?>
                      <span class="text-muted">—</span>
                    <?php endif; ?>
                  </td>
                  <td><?= prioriteBadge($t['priorite']) ?></td>
                  <td>
                    <span class="badge-status <?= $t['statut'] === 'termine' ? 'accepted' : 'pending' ?>">
                      <?= $t['statut'] === 'termine' ? '✅ Terminé' : '🔄 En cours' ?>
                    </span>
                  </td>
                  <td>
                    <a href="index.php?page=admin_toggle_tache&id=<?= $t['id'] ?>"
                       class="btn btn-sm btn-joker-blue me-1"
                       title="<?= $t['statut'] === 'termine' ? 'Remettre en cours' : 'Marquer terminé' ?>">
                      <i class="bi bi-<?= $t['statut'] === 'termine' ? 'arrow-counterclockwise' : 'check-lg' ?>"></i>
                    </a>
                    <a href="index.php?page=admin_supprimer_tache&id=<?= $t['id'] ?>"
                       class="btn btn-sm btn-joker-red"
                       onclick="return confirm('Supprimer cette tâche ?')">
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

<!-- Modal Ajouter Tâche -->
<div class="modal fade modal-joker" id="modalAjouterTache" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="bi bi-check2-square me-2"></i>Assigner une tâche</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form method="POST" action="index.php?page=admin_ajouter_tache">
        <div class="modal-body p-4">
          <div class="form-joker row g-3">
            <div class="col-12">
              <label class="form-label">Description de la tâche *</label>
              <input type="text" class="form-control" name="titre"
                     placeholder="Ex: Préparer l'affiche de l'événement" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">Assigné à</label>
              <select class="form-select" name="id_assigne">
                <option value="">— Aucun —</option>
                <?php foreach (($membres ?? []) as $m): ?>
                <option value="<?= $m['id'] ?>"><?= htmlspecialchars($m['nom']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label">Priorité</label>
              <select class="form-select" name="priorite">
                <option value="haute">🔴 Haute</option>
                <option value="moyenne" selected>🟡 Moyenne</option>
                <option value="faible">🟢 Faible</option>
              </select>
            </div>
            <div class="col-12">
              <label class="form-label">Deadline</label>
              <input type="date" class="form-control" name="deadline">
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-joker-outline" data-bs-dismiss="modal">Annuler</button>
          <button type="submit" class="btn btn-joker-blue">
            <i class="bi bi-check-lg me-1"></i>Créer la tâche
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
</script>
</body>
</html>