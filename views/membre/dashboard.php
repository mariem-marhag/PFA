<?php
/**
 * views/membre/dashboard.php
 * Dashboard membre — Vue d'ensemble, événements, tâches, to-do
 */

if (!function_exists('formatDateFr')) {
    function formatDateFr(string $date): string {
        if (!$date) return '';
        $mois = ['','Jan','Fév','Mar','Avr','Mai','Juin','Juil','Aoû','Sep','Oct','Nov','Déc'];
        [$y, $m, $d] = explode('-', $date);
        return intval($d) . ' ' . $mois[intval($m)] . ' ' . $y;
    }
}

function prioriteMembreBadge(string $p): string {
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
$tabActif  = $tab ?? 'overview';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Club Joker — Espace Membre</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
  <link rel="stylesheet" href="public/style.css">
</head>
<body>
<div class="dashboard-wrapper">

<!-- ══════ SIDEBAR ══════ -->
<aside class="sidebar" id="sidebar">
  <div class="sidebar-brand">🃏 Club <span>Joker</span></div>
  <div class="sidebar-role">Espace Membre</div>
  <ul class="sidebar-menu">
    <?php
    $items = [
      'overview'   => ['icon'=>'grid-1x2-fill',   'label'=>'Vue d\'ensemble'],
      'events'     => ['icon'=>'calendar-event',  'label'=>'Événements'],
      'meetings'   => ['icon'=>'people',           'label'=>'Réunions'],
      'my_tasks'   => ['icon'=>'check2-square',    'label'=>'Mes tâches'],
      'todo'       => ['icon'=>'list-check',       'label'=>'Ma to-do list'],
    ];
    foreach ($items as $key => $item):
    ?>
    <li>
      <a href="index.php?page=membre_dashboard&tab=<?= $key ?>"
         class="nav-item <?= $tabActif === $key ? 'active' : '' ?>"
         style="text-decoration:none">
        <i class="bi bi-<?= $item['icon'] ?>"></i>
        <?= $item['label'] ?>
        <?php if ($key === 'my_tasks' && !empty($mes_taches)): ?>
          <span class="badge bg-danger ms-auto"><?= count($mes_taches) ?></span>
        <?php endif; ?>
      </a>
    </li>
    <?php endforeach; ?>
  </ul>
  <div class="sidebar-footer">
    <div class="sidebar-user">
      <div class="sidebar-avatar"><?= htmlspecialchars($initiales) ?></div>
      <div class="sidebar-user-info">
        <div class="name"><?= htmlspecialchars($user['nom']) ?></div>
        <div class="role">Membre actif</div>
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

  <!-- Topbar -->
  <div class="dashboard-topbar">
    <div class="d-flex align-items-center gap-3">
      <button class="btn btn-sm d-lg-none" id="sidebar-toggle"
              style="background:var(--beige);border:none;border-radius:8px;padding:.4rem .7rem">
        <i class="bi bi-list fs-5 text-blue"></i>
      </button>
      <h1>
        <?= [
          'overview' => 'Vue d\'ensemble',
          'events'   => 'Événements',
          'meetings' => 'Réunions',
          'my_tasks' => 'Mes tâches',
          'todo'     => 'Ma to-do list',
        ][$tabActif] ?? 'Dashboard' ?>
      </h1>
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

    <!-- ══════ TAB OVERVIEW ══════ -->
    <?php if ($tabActif === 'overview'): ?>

    <!-- Bienvenue -->
    <div class="joker-card mb-4 p-4"
         style="background:linear-gradient(135deg,var(--blue),var(--blue-dark));color:var(--beige)">
      <div class="d-flex align-items-center gap-3">
        <div class="sidebar-avatar" style="width:56px;height:56px;font-size:1.2rem;background:rgba(245,236,215,.15)">
          <?= htmlspecialchars($initiales) ?>
        </div>
        <div>
          <h4 style="color:var(--beige)" class="mb-1">Bonjour, <?= htmlspecialchars(explode(' ', $user['nom'])[0]) ?> 👋</h4>
          <p style="color:rgba(245,236,215,.7)" class="mb-0 small">Membre actif du Club Joker</p>
        </div>
      </div>
    </div>

    <!-- Stats -->
    <div class="row g-3 mb-4">
      <div class="col-6 col-lg-4">
        <div class="stat-card">
          <div class="stat-icon blue"><i class="bi bi-calendar-event"></i></div>
          <div><div class="stat-value"><?= $stats['evenements'] ?></div><div class="stat-label">Événements</div></div>
        </div>
      </div>
      <div class="col-6 col-lg-4">
        <div class="stat-card">
          <div class="stat-icon gold"><i class="bi bi-people"></i></div>
          <div><div class="stat-value"><?= $stats['reunions'] ?></div><div class="stat-label">Réunions à venir</div></div>
        </div>
      </div>
      <div class="col-6 col-lg-4">
        <div class="stat-card">
          <div class="stat-icon red"><i class="bi bi-check2-square"></i></div>
          <div><div class="stat-value"><?= $stats['mes_taches'] ?></div><div class="stat-label">Mes tâches</div></div>
        </div>
      </div>
    </div>

    <!-- Prochains événements + tâches urgentes -->
    <div class="row g-4">
      <div class="col-lg-6">
        <div class="dash-panel">
          <div class="dash-panel-header">
            <h5><i class="bi bi-calendar-event me-2 text-blue"></i>Prochains événements</h5>
            <a href="index.php?page=membre_dashboard&tab=events" class="btn btn-joker-blue btn-sm">Voir tout</a>
          </div>
          <div class="dash-panel-body p-0">
            <table class="joker-table">
              <thead><tr><th>Titre</th><th>Date</th><th>Type</th></tr></thead>
              <tbody>
                <?php foreach (array_slice($evenements ?? [], 0, 4) as $e): ?>
                <tr>
                  <td><strong><?= htmlspecialchars($e['titre']) ?></strong></td>
                  <td class="small"><?= formatDateFr($e['date_evenement']) ?></td>
                  <td><span class="event-badge <?= $e['type'] === 'public' ? 'public' : 'private' ?>">
                    <?= $e['type'] === 'public' ? 'Public' : 'Privé' ?>
                  </span></td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($evenements)): ?>
                <tr><td colspan="3" class="text-center text-muted py-3">Aucun événement</td></tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <div class="col-lg-6">
        <div class="dash-panel">
          <div class="dash-panel-header">
            <h5><i class="bi bi-check2-square me-2 text-red"></i>Mes tâches urgentes</h5>
            <a href="index.php?page=membre_dashboard&tab=my_tasks" class="btn btn-joker-red btn-sm">Voir tout</a>
          </div>
          <div class="dash-panel-body">
            <?php
            $urgentes = array_filter($mes_taches ?? [], fn($t) => $t['priorite'] === 'haute' && $t['statut'] === 'en_cours');
            if (empty($urgentes)):
            ?>
              <div class="empty-state py-3">
                <i class="bi bi-check-circle fs-2"></i>
                <p class="mt-2 small">Aucune tâche urgente 🎉</p>
              </div>
            <?php else: ?>
              <?php foreach (array_slice($urgentes, 0, 4) as $t): ?>
              <div class="d-flex align-items-center gap-3 py-2" style="border-bottom:1px solid var(--beige)">
                <i class="bi bi-exclamation-circle-fill text-danger fs-5"></i>
                <div class="flex-grow-1">
                  <div class="fw-bold small"><?= htmlspecialchars($t['titre']) ?></div>
                  <?php if ($t['deadline']): ?>
                  <div class="small text-muted">Deadline: <?= formatDateFr($t['deadline']) ?></div>
                  <?php endif; ?>
                </div>
              </div>
              <?php endforeach; ?>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>

    <!-- ══════ TAB EVENTS ══════ -->
    <?php elseif ($tabActif === 'events'): ?>

    <div class="dash-panel">
      <div class="dash-panel-body p-0">
        <div class="table-responsive">
          <table class="joker-table">
            <thead>
              <tr><th>Titre</th><th>Date & Heure</th><th>Lieu</th><th>Type</th><th>Places</th><th>Action</th></tr>
            </thead>
            <tbody>
              <?php if (empty($evenements)): ?>
                <tr><td colspan="6" class="text-center text-muted py-4">Aucun événement</td></tr>
              <?php else: ?>
                <?php foreach ($evenements as $e):
                  $spots = (int)$e['max_participants'] - (int)$e['nb_inscrits'];
                ?>
                <tr>
                  <td><strong><?= htmlspecialchars($e['titre']) ?></strong></td>
                  <td><?= formatDateFr($e['date_evenement']) ?> <?= $e['heure'] ? substr($e['heure'],0,5) : '' ?></td>
                  <td><?= htmlspecialchars($e['lieu'] ?? '—') ?></td>
                  <td><span class="event-badge <?= $e['type'] === 'public' ? 'public' : 'private' ?>">
                    <?= $e['type'] === 'public' ? '🌐 Public' : '🔒 Privé' ?>
                  </span></td>
                  <td><?= (int)$e['nb_inscrits'] ?>/<?= (int)$e['max_participants'] ?></td>
                  <td>
                    <?php if ($e['type'] === 'public' && $spots > 0): ?>
                    <button class="btn btn-sm btn-joker-red"
                            data-bs-toggle="modal" data-bs-target="#modalInscription"
                            data-id="<?= $e['id'] ?>"
                            data-titre="<?= htmlspecialchars($e['titre']) ?>">
                      S'inscrire
                    </button>
                    <?php elseif ($spots <= 0): ?>
                      <span class="badge-status refused">Complet</span>
                    <?php else: ?>
                      <span class="badge-status active small">Inscrit</span>
                    <?php endif; ?>
                  </td>
                </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- ══════ TAB MEETINGS ══════ -->
    <?php elseif ($tabActif === 'meetings'): ?>

    <div class="meeting-grid">
      <?php if (empty($reunions)): ?>
        <div class="empty-state"><i class="bi bi-calendar2-x"></i><p>Aucune réunion à venir.</p></div>
      <?php else: ?>
        <?php foreach ($reunions as $r): ?>
        <div class="meeting-card">
          <div class="meeting-date"><?= formatDateFr($r['date_reunion']) ?></div>
          <div class="meeting-title"><?= htmlspecialchars($r['titre']) ?></div>
          <p class="small text-muted mb-2"><?= htmlspecialchars($r['ordre_du_jour'] ?? '') ?></p>
          <div class="d-flex gap-3 small flex-wrap">
            <span><i class="bi bi-clock text-blue"></i> <?= $r['heure'] ? substr($r['heure'],0,5) : '—' ?></span>
            <span><i class="bi bi-geo-alt text-red"></i> <?= htmlspecialchars($r['lieu'] ?? '—') ?></span>
          </div>
          <span class="badge-status active mt-2 d-inline-block"><?= htmlspecialchars($r['type']) ?></span>
        </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>

    <!-- ══════ TAB MY TASKS ══════ -->
    <?php elseif ($tabActif === 'my_tasks'): ?>

    <div class="dash-panel">
      <div class="dash-panel-body p-0">
        <div class="table-responsive">
          <table class="joker-table">
            <thead>
              <tr><th>Tâche</th><th>Deadline</th><th>Priorité</th><th>Statut</th></tr>
            </thead>
            <tbody>
              <?php if (empty($mes_taches)): ?>
                <tr><td colspan="4" class="text-center text-muted py-5">
                  <i class="bi bi-check2-circle fs-2 d-block mb-2"></i>Aucune tâche assignée pour le moment
                </td></tr>
              <?php else: ?>
                <?php foreach ($mes_taches as $t): ?>
                <tr class="<?= $t['statut'] === 'termine' ? 'opacity-75' : '' ?>">
                  <td>
                    <strong class="<?= $t['statut'] === 'termine' ? 'text-decoration-line-through text-muted' : '' ?>">
                      <?= htmlspecialchars($t['titre']) ?>
                    </strong>
                  </td>
                  <td>
                    <?php if ($t['deadline']): ?>
                      <?php $late = strtotime($t['deadline']) < time() && $t['statut'] === 'en_cours'; ?>
                      <span class="<?= $late ? 'text-danger fw-bold' : '' ?>">
                        <?= $late ? '⚠️ ' : '' ?><?= formatDateFr($t['deadline']) ?>
                      </span>
                    <?php else: ?><span class="text-muted">—</span><?php endif; ?>
                  </td>
                  <td><?= prioriteMembreBadge($t['priorite']) ?></td>
                  <td>
                    <span class="badge-status <?= $t['statut'] === 'termine' ? 'accepted' : 'pending' ?>">
                      <?= $t['statut'] === 'termine' ? '✅ Terminé' : '🔄 En cours' ?>
                    </span>
                  </td>
                </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- ══════ TAB TODO ══════ -->
    <?php elseif ($tabActif === 'todo'): ?>

    <div class="row justify-content-center">
      <div class="col-lg-7">

        <!-- Formulaire ajout todo -->
        <div class="dash-panel mb-4">
          <div class="dash-panel-header">
            <h5><i class="bi bi-plus-circle me-2 text-blue"></i>Nouvelle tâche</h5>
          </div>
          <div class="dash-panel-body">
            <form method="POST" action="index.php?page=membre_ajouter_todo" class="form-joker">
              <div class="d-flex gap-2">
                <input type="text" class="form-control" name="titre"
                       placeholder="Que voulez-vous faire ?" required>
                <button type="submit" class="btn btn-joker-blue" style="white-space:nowrap">
                  <i class="bi bi-plus-lg me-1"></i>Ajouter
                </button>
              </div>
            </form>
          </div>
        </div>

        <!-- Liste todo -->
        <div class="dash-panel">
          <div class="dash-panel-header">
            <h5><i class="bi bi-list-check me-2 text-red"></i>Ma liste
              <span class="badge bg-secondary ms-1"><?= count($todo_list ?? []) ?></span>
            </h5>
          </div>
          <div class="dash-panel-body p-0">
            <?php if (empty($todo_list)): ?>
              <div class="empty-state py-4">
                <i class="bi bi-journal-check fs-2"></i>
                <p class="mt-2">Votre liste est vide. Ajoutez votre première tâche !</p>
              </div>
            <?php else: ?>
              <?php foreach ($todo_list as $t): ?>
              <div class="d-flex align-items-center gap-3 px-3 py-2"
                   style="border-bottom:1px solid var(--beige)">
                <!-- Toggle statut -->
                <a href="index.php?page=membre_toggle_todo&id=<?= $t['id'] ?>"
                   class="btn btn-sm <?= $t['statut'] === 'termine' ? 'btn-joker-blue' : 'btn-joker-outline' ?>"
                   style="min-width:32px;padding:.2rem .5rem">
                  <i class="bi bi-<?= $t['statut'] === 'termine' ? 'check-circle-fill' : 'circle' ?>"></i>
                </a>
                <!-- Texte -->
                <span class="flex-grow-1 <?= $t['statut'] === 'termine' ? 'text-decoration-line-through text-muted' : '' ?>">
                  <?= htmlspecialchars($t['titre']) ?>
                </span>
                <!-- Supprimer -->
                <a href="index.php?page=membre_supprimer_todo&id=<?= $t['id'] ?>"
                   class="btn btn-sm btn-joker-red"
                   onclick="return confirm('Supprimer cette tâche ?')"
                   style="padding:.2rem .5rem">
                  <i class="bi bi-trash"></i>
                </a>
              </div>
              <?php endforeach; ?>
              <!-- Résumé -->
              <?php
              $done  = count(array_filter($todo_list, fn($t) => $t['statut'] === 'termine'));
              $total = count($todo_list);
              $pct   = $total > 0 ? round(($done / $total) * 100) : 0;
              ?>
              <div class="px-3 py-3">
                <div class="d-flex justify-content-between small text-muted mb-1">
                  <span><?= $done ?>/<?= $total ?> terminées</span>
                  <span><?= $pct ?>%</span>
                </div>
                <div class="progress" style="height:6px;border-radius:3px">
                  <div class="progress-bar" style="width:<?= $pct ?>%;background:var(--blue)"></div>
                </div>
              </div>
            <?php endif; ?>
          </div>
        </div>

      </div>
    </div>

    <?php endif; ?>

  </div><!-- /dashboard-body -->
</main>
</div><!-- /dashboard-wrapper -->

<!-- Modal Inscription Événement -->
<div class="modal fade modal-joker" id="modalInscription" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">S'inscrire : <span id="modal-evt-titre"></span></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form method="POST" action="index.php?page=inscrire_evenement">
        <input type="hidden" name="id_evenement" id="modal-evt-id">
        <div class="modal-body p-4">
          <div class="form-joker row g-3">
            <div class="col-12">
              <label class="form-label">Nom complet *</label>
              <input type="text" class="form-control" name="nom"
                     value="<?= htmlspecialchars($user['nom']) ?>" required>
            </div>
            <div class="col-12">
              <label class="form-label">Email *</label>
              <input type="email" class="form-control" name="email"
                     value="<?= htmlspecialchars($user['email']) ?>" required>
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
document.getElementById('modalInscription')?.addEventListener('show.bs.modal', e => {
  const b = e.relatedTarget;
  document.getElementById('modal-evt-id').value = b.dataset.id;
  document.getElementById('modal-evt-titre').textContent = b.dataset.titre;
});
</script>
</body>
</html>