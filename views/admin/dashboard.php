<?php
/**
 * views/admin/dashboard.php
 * Dashboard administrateur — MAJ complète
 * Ajouts : statistiques, présences, lien_meet, logo image
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
    $map = ['haute'=>['danger','Haute'],'moyenne'=>['warning','Moyenne'],'faible'=>['secondary','Faible']];
    [$cls, $label] = $map[$p] ?? ['secondary','—'];
    return "<span class='badge bg-{$cls}'>{$label}</span>";
}

$user       = $_SESSION['user'];
$initiales  = mb_strtoupper(mb_substr($user['nom'], 0, 1)) . mb_strtoupper(mb_substr(explode(' ', $user['nom'])[1] ?? '', 0, 1));
$tabActif   = $tab ?? 'overview';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Club Joker — Dashboard Admin</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
  <link rel="stylesheet" href="public/style.css">
</head>
<body>
<div class="dashboard-wrapper">

<!-- ══════ SIDEBAR ══════ -->
<aside class="sidebar" id="sidebar">
  <!-- LOGO IMAGE -->
  <div class="sidebar-brand text-center">
    <img src="joker.png" alt="Club Joker" style="height:48px;object-fit:contain;margin-bottom:6px"
         onerror="this.style.display='none';this.nextElementSibling.style.display='block'">
    <span style="display:none">🃏 Club <span>Joker</span></span>
  </div>
  <div class="sidebar-role">Administration</div>
  <ul class="sidebar-menu">
    <?php
    $items = [
      'overview'  => ['icon'=>'speedometer2',   'label'=>'Vue d\'ensemble'],
      'events'    => ['icon'=>'calendar-event', 'label'=>'Événements'],
      'meetings'  => ['icon'=>'people',         'label'=>'Réunions'],
      'presences' => ['icon'=>'person-check',   'label'=>'Présences'],
      'requests'  => ['icon'=>'person-plus',    'label'=>'Demandes d\'adhésion'],
      'tasks'     => ['icon'=>'check2-square',  'label'=>'Tâches'],
      'stats'     => ['icon'=>'bar-chart-line', 'label'=>'Statistiques'],
      'members'   => ['icon'=>'people-fill',    'label'=>'Membres'],
    ];
    foreach ($items as $key => $item): ?>
    <li>
      <a href="index.php?page=admin_dashboard&tab=<?= $key ?>"
         class="nav-item <?= $tabActif === $key ? 'active' : '' ?>"
         style="text-decoration:none">
        <i class="bi bi-<?= $item['icon'] ?>"></i>
        <?= $item['label'] ?>
        <?php if ($key === 'requests' && ($stats['demandes'] ?? 0) > 0): ?>
          <span class="badge bg-danger ms-auto"><?= $stats['demandes'] ?></span>
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

  <!-- Topbar -->
  <div class="dashboard-topbar">
    <div class="d-flex align-items-center gap-3">
      <button class="btn btn-sm d-lg-none" id="sidebar-toggle"
              style="background:var(--beige);border:none;border-radius:8px;padding:.4rem .7rem">
        <i class="bi bi-list fs-5 text-blue"></i>
      </button>
      <h1>
        <?= [
          'overview'  => 'Vue d\'ensemble',
          'events'    => 'Événements',
          'meetings'  => 'Réunions',
          'presences' => 'Listes de présence',
          'requests'  => 'Demandes d\'adhésion',
          'tasks'     => 'Tâches',
          'stats'     => 'Statistiques & Rapports',
          'members'   => 'Membres',
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
      <?= $flash_success ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>
    <?php if (!empty($flash_error)): ?>
    <div class="alert alert-danger alert-dismissible fade show rounded-joker mb-3" role="alert">
      <?= htmlspecialchars($flash_error) ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <!-- ══════════════════════════════════
         TAB: OVERVIEW
    ═══════════════════════════════════ -->
    <?php if ($tabActif === 'overview'): ?>
    <div class="row g-3 mb-4">
      <div class="col-6 col-lg-3">
        <div class="stat-card">
          <div class="stat-icon blue"><i class="bi bi-calendar-event"></i></div>
          <div><div class="stat-value"><?= $stats['evenements'] ?></div><div class="stat-label">Événements</div></div>
        </div>
      </div>
      <div class="col-6 col-lg-3">
        <div class="stat-card">
          <div class="stat-icon red"><i class="bi bi-people-fill"></i></div>
          <div><div class="stat-value"><?= $stats['membres'] ?></div><div class="stat-label">Membres</div></div>
        </div>
      </div>
      <div class="col-6 col-lg-3">
        <div class="stat-card">
          <div class="stat-icon gold"><i class="bi bi-calendar2-check"></i></div>
          <div><div class="stat-value"><?= $stats['reunions'] ?></div><div class="stat-label">Réunions</div></div>
        </div>
      </div>
      <div class="col-6 col-lg-3">
        <div class="stat-card">
          <div class="stat-icon green"><i class="bi bi-person-plus"></i></div>
          <div><div class="stat-value"><?= $stats['demandes'] ?></div><div class="stat-label">Demandes</div></div>
        </div>
      </div>
    </div>

    <div class="row g-4">
      <div class="col-lg-7">
        <div class="dash-panel">
          <div class="dash-panel-header">
            <h5><i class="bi bi-calendar-event me-2 text-blue"></i>Événements récents</h5>
            <a href="index.php?page=admin_dashboard&tab=events" class="btn btn-joker-blue btn-sm">Gérer <i class="bi bi-arrow-right ms-1"></i></a>
          </div>
          <div class="dash-panel-body p-0">
            <table class="joker-table">
              <thead><tr><th>Titre</th><th>Date</th><th>Type</th><th>Inscrits</th></tr></thead>
              <tbody>
                <?php foreach (array_slice($evenements_recents, 0, 4) as $e): ?>
                <tr>
                  <td><strong><?= htmlspecialchars($e['titre']) ?></strong></td>
                  <td class="small"><?= formatDateFr($e['date_evenement']) ?></td>
                  <td><span class="event-badge <?= $e['type'] === 'public' ? 'public' : 'private' ?>"><?= $e['type'] === 'public' ? 'Public' : 'Privé' ?></span></td>
                  <td><?= $e['nb_inscrits'] ?>/<?= $e['max_participants'] ?></td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
      <div class="col-lg-5">
        <div class="dash-panel">
          <div class="dash-panel-header">
            <h5><i class="bi bi-person-check me-2 text-red"></i>Demandes en attente</h5>
            <a href="index.php?page=admin_dashboard&tab=requests" class="btn btn-joker-red btn-sm">Voir tout</a>
          </div>
          <div class="dash-panel-body">
            <?php if (empty($demandes_en_attente)): ?>
              <div class="empty-state py-3"><i class="bi bi-inbox fs-2"></i><p class="mt-2">Aucune demande</p></div>
            <?php else: ?>
              <?php foreach ($demandes_en_attente as $r): ?>
              <div class="d-flex align-items-center gap-3 py-2" style="border-bottom:1px solid var(--beige)">
                <div class="sidebar-avatar" style="width:36px;height:36px;font-size:.8rem;background:var(--blue)">
                  <?= mb_strtoupper(mb_substr($r['nom'],0,2)) ?>
                </div>
                <div class="flex-grow-1">
                  <div class="fw-bold small"><?= htmlspecialchars($r['nom']) ?></div>
                  <div class="small text-muted"><?= htmlspecialchars($r['email']) ?></div>
                </div>
                <div class="d-flex gap-1">
                  <a href="index.php?page=admin_traiter_demande&id=<?= $r['id'] ?>&action=accepte" class="btn btn-sm btn-joker-blue">✓</a>
                  <a href="index.php?page=admin_traiter_demande&id=<?= $r['id'] ?>&action=refuse" class="btn btn-sm btn-joker-red">✕</a>
                </div>
              </div>
              <?php endforeach; ?>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>

    <!-- ══════════════════════════════════
         TAB: EVENTS
    ═══════════════════════════════════ -->
    <?php elseif ($tabActif === 'events'): ?>
    <div class="d-flex align-items-center justify-content-between mb-4">
      <div>
        <h4 class="text-blue mb-0">Gestion des événements</h4>
        <p class="text-muted small mb-0">Créez, modifiez et supprimez les événements</p>
      </div>
      <button class="btn btn-joker-red" data-bs-toggle="modal" data-bs-target="#modalAjouterEvt">
        <i class="bi bi-plus-lg me-1"></i>Ajouter un événement
      </button>
    </div>
    <div class="dash-panel">
      <div class="dash-panel-body p-0">
        <div class="table-responsive">
          <table class="joker-table">
            <thead>
              <tr><th>Titre</th><th>Date & Heure</th><th>Lieu</th><th>Type</th><th>Inscrits</th><th>Actions</th></tr>
            </thead>
            <tbody>
              <?php if (empty($evenements)): ?>
                <tr><td colspan="6" class="text-center text-muted py-4">Aucun événement</td></tr>
              <?php else: ?>
                <?php foreach ($evenements as $e): ?>
                <tr>
                  <td><strong><?= htmlspecialchars($e['titre']) ?></strong></td>
                  <td><?= formatDateFr($e['date_evenement']) ?> <?= $e['heure'] ? substr($e['heure'],0,5) : '' ?></td>
                  <td><?= htmlspecialchars($e['lieu'] ?? '') ?></td>
                  <td><span class="event-badge <?= $e['type'] === 'public' ? 'public' : 'private' ?>"><?= $e['type'] === 'public' ? '🌐 Public' : '🔒 Privé' ?></span></td>
                  <td><?= $e['nb_inscrits'] ?>/<?= $e['max_participants'] ?></td>
                  <td>
                    <button class="btn btn-sm btn-joker-blue me-1"
                            data-bs-toggle="modal" data-bs-target="#modalModifEvt"
                            data-id="<?= $e['id'] ?>" data-titre="<?= htmlspecialchars($e['titre']) ?>"
                            data-date="<?= $e['date_evenement'] ?>" data-heure="<?= $e['heure'] ?? '' ?>"
                            data-lieu="<?= htmlspecialchars($e['lieu'] ?? '') ?>" data-type="<?= $e['type'] ?>"
                            data-max="<?= $e['max_participants'] ?>" data-desc="<?= htmlspecialchars($e['description'] ?? '') ?>">
                      <i class="bi bi-pencil"></i>
                    </button>
                    <a href="index.php?page=admin_supprimer_evenement&id=<?= $e['id'] ?>"
                       class="btn btn-sm btn-joker-red" onclick="return confirm('Supprimer ?')">
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

    <!-- Modal Ajouter Événement -->
    <div class="modal fade modal-joker" id="modalAjouterEvt" tabindex="-1">
      <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Ajouter un événement</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <form method="POST" action="index.php?page=admin_ajouter_evenement">
            <div class="modal-body p-4">
              <div class="form-joker row g-3">
                <div class="col-md-8"><label class="form-label">Titre *</label><input type="text" class="form-control" name="titre" required></div>
                <div class="col-md-4"><label class="form-label">Type</label>
                  <select class="form-select" name="type"><option value="public">Public</option><option value="prive">Privé</option></select>
                </div>
                <div class="col-md-6"><label class="form-label">Date *</label><input type="date" class="form-control" name="date_evenement" required></div>
                <div class="col-md-6"><label class="form-label">Heure</label><input type="time" class="form-control" name="heure"></div>
                <div class="col-md-8"><label class="form-label">Lieu</label><input type="text" class="form-control" name="lieu"></div>
                <div class="col-md-4"><label class="form-label">Max participants</label><input type="number" class="form-control" name="max_participants" value="30" min="1"></div>
                <div class="col-12"><label class="form-label">Description</label><textarea class="form-control" name="description" rows="2"></textarea></div>
              </div>
            </div>
            <div class="modal-footer">
              <button type="submit" class="btn btn-joker-blue"><i class="bi bi-check-lg me-1"></i>Créer</button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Modal Modifier Événement -->
    <div class="modal fade modal-joker" id="modalModifEvt" tabindex="-1">
      <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
          <div class="modal-header"><h5 class="modal-title">Modifier l'événement</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
          <form method="POST" action="index.php?page=admin_modifier_evenement">
            <input type="hidden" name="id" id="modif-evt-id">
            <div class="modal-body p-4">
              <div class="form-joker row g-3">
                <div class="col-md-8"><label class="form-label">Titre *</label><input type="text" class="form-control" name="titre" id="modif-evt-titre"></div>
                <div class="col-md-4"><label class="form-label">Type</label>
                  <select class="form-select" name="type" id="modif-evt-type"><option value="public">Public</option><option value="prive">Privé</option></select>
                </div>
                <div class="col-md-6"><label class="form-label">Date</label><input type="date" class="form-control" name="date_evenement" id="modif-evt-date"></div>
                <div class="col-md-6"><label class="form-label">Heure</label><input type="time" class="form-control" name="heure" id="modif-evt-heure"></div>
                <div class="col-md-8"><label class="form-label">Lieu</label><input type="text" class="form-control" name="lieu" id="modif-evt-lieu"></div>
                <div class="col-md-4"><label class="form-label">Max</label><input type="number" class="form-control" name="max_participants" id="modif-evt-max"></div>
                <div class="col-12"><label class="form-label">Description</label><textarea class="form-control" name="description" rows="2" id="modif-evt-desc"></textarea></div>
              </div>
            </div>
            <div class="modal-footer"><button type="submit" class="btn btn-joker-blue"><i class="bi bi-check-lg me-1"></i>Sauvegarder</button></div>
          </form>
        </div>
      </div>
    </div>

    <!-- ══════════════════════════════════
         TAB: MEETINGS
    ═══════════════════════════════════ -->
    <?php elseif ($tabActif === 'meetings'): ?>
    <div class="d-flex align-items-center justify-content-between mb-4">
      <div>
        <h4 class="text-blue mb-0">Gestion des réunions</h4>
        <p class="text-muted small mb-0">Planifiez les réunions du bureau et de l'équipe</p>
      </div>
      <button class="btn btn-joker-red" data-bs-toggle="modal" data-bs-target="#modalAjouterReunion">
        <i class="bi bi-plus-lg me-1"></i>Planifier une réunion
      </button>
    </div>
    <div class="meeting-grid">
      <?php if (empty($reunions)): ?>
        <div class="empty-state"><i class="bi bi-calendar2-x"></i><p>Aucune réunion.</p></div>
      <?php else: ?>
        <?php foreach ($reunions as $r): ?>
        <div class="meeting-card">
          <div class="d-flex justify-content-between align-items-start mb-2">
            <div class="meeting-date"><?= formatDateFr($r['date_reunion']) ?></div>
            <div class="d-flex gap-1">
              <a href="index.php?page=admin_dashboard&tab=presences&reunion_id=<?= $r['id'] ?>"
                 class="btn btn-sm btn-joker-blue" title="Liste de présence">
                <i class="bi bi-person-check"></i>
              </a>
              <button class="btn btn-sm btn-joker-blue"
                      data-bs-toggle="modal" data-bs-target="#modalModifReunion"
                      data-id="<?= $r['id'] ?>"
                      data-titre="<?= htmlspecialchars($r['titre']) ?>"
                      data-date="<?= $r['date_reunion'] ?>"
                      data-heure="<?= $r['heure'] ?? '' ?>"
                      data-lieu="<?= htmlspecialchars($r['lieu'] ?? '') ?>"
                      data-type="<?= $r['type'] ?>"
                      data-lien="<?= htmlspecialchars($r['lien_meet'] ?? '') ?>"
                      data-odj="<?= htmlspecialchars($r['ordre_du_jour'] ?? '') ?>">
                <i class="bi bi-pencil"></i>
              </button>
              <a href="index.php?page=admin_supprimer_reunion&id=<?= $r['id'] ?>"
                 class="btn btn-sm btn-joker-red" onclick="return confirm('Supprimer ?')">
                <i class="bi bi-trash"></i>
              </a>
            </div>
          </div>
          <div class="meeting-title"><?= htmlspecialchars($r['titre']) ?></div>
          <p class="small text-muted mb-1"><?= htmlspecialchars($r['ordre_du_jour'] ?? '') ?></p>
          <div class="d-flex gap-2 small flex-wrap mb-2">
            <span><i class="bi bi-clock text-blue"></i> <?= $r['heure'] ? substr($r['heure'],0,5) : '' ?></span>
            <span><i class="bi bi-geo-alt text-red"></i> <?= htmlspecialchars($r['lieu'] ?? '') ?></span>
          </div>
          <?php if (!empty($r['lien_meet'])): ?>
          <a href="<?= htmlspecialchars($r['lien_meet']) ?>" target="_blank"
             class="btn btn-sm w-100 mt-1"
             style="background:#1a73e8;color:#fff;border-radius:6px;font-size:.8rem">
            <i class="bi bi-camera-video-fill me-1"></i>Rejoindre le Meet
          </a>
          <?php endif; ?>
          <span class="badge-status active mt-2 d-inline-block"><?= htmlspecialchars($r['type']) ?></span>
        </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>

    <!-- Modal Ajouter Réunion -->
    <div class="modal fade modal-joker" id="modalAjouterReunion" tabindex="-1">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Planifier une réunion</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <form method="POST" action="index.php?page=admin_ajouter_reunion">
            <div class="modal-body p-4">
              <div class="form-joker row g-3">
                <div class="col-12"><label class="form-label">Titre *</label><input type="text" class="form-control" name="titre" required></div>
                <div class="col-md-6"><label class="form-label">Date *</label><input type="date" class="form-control" name="date_reunion" required></div>
                <div class="col-md-6"><label class="form-label">Heure</label><input type="time" class="form-control" name="heure"></div>
                <div class="col-md-6"><label class="form-label">Lieu</label><input type="text" class="form-control" name="lieu"></div>
                <div class="col-md-6"><label class="form-label">Type</label>
                  <select class="form-select" name="type">
                    <option value="bureau">Bureau</option><option value="projet">Projet</option>
                    <option value="generale">Générale</option><option value="formation">Formation</option>
                  </select>
                </div>
                <div class="col-12">
                  <label class="form-label"><i class="bi bi-camera-video text-blue me-1"></i>Lien Google Meet / Zoom</label>
                  <input type="url" class="form-control" name="lien_meet" placeholder="https://meet.google.com/...">
                </div>
                <div class="col-12"><label class="form-label">Ordre du jour</label><textarea class="form-control" name="ordre_du_jour" rows="2"></textarea></div>
              </div>
            </div>
            <div class="modal-footer">
              <button type="submit" class="btn btn-joker-blue"><i class="bi bi-check-lg me-1"></i>Créer</button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Modal Modifier Réunion -->
    <div class="modal fade modal-joker" id="modalModifReunion" tabindex="-1">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header"><h5 class="modal-title">Modifier la réunion</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
          <form method="POST" action="index.php?page=admin_modifier_reunion">
            <input type="hidden" name="id" id="modif-r-id">
            <div class="modal-body p-4">
              <div class="form-joker row g-3">
                <div class="col-12"><label class="form-label">Titre *</label><input type="text" class="form-control" name="titre" id="modif-r-titre"></div>
                <div class="col-md-6"><label class="form-label">Date</label><input type="date" class="form-control" name="date_reunion" id="modif-r-date"></div>
                <div class="col-md-6"><label class="form-label">Heure</label><input type="time" class="form-control" name="heure" id="modif-r-heure"></div>
                <div class="col-md-6"><label class="form-label">Lieu</label><input type="text" class="form-control" name="lieu" id="modif-r-lieu"></div>
                <div class="col-md-6"><label class="form-label">Type</label>
                  <select class="form-select" name="type" id="modif-r-type">
                    <option value="bureau">Bureau</option><option value="projet">Projet</option>
                    <option value="generale">Générale</option><option value="formation">Formation</option>
                  </select>
                </div>
                <div class="col-12">
                  <label class="form-label"><i class="bi bi-camera-video text-blue me-1"></i>Lien Meet</label>
                  <input type="url" class="form-control" name="lien_meet" id="modif-r-lien" placeholder="https://meet.google.com/...">
                </div>
                <div class="col-12"><label class="form-label">Ordre du jour</label><textarea class="form-control" name="ordre_du_jour" rows="2" id="modif-r-odj"></textarea></div>
              </div>
            </div>
            <div class="modal-footer"><button type="submit" class="btn btn-joker-blue"><i class="bi bi-check-lg me-1"></i>Sauvegarder</button></div>
          </form>
        </div>
      </div>
    </div>

    <!-- ══════════════════════════════════
         TAB: PRESENCES
    ═══════════════════════════════════ -->
    <?php elseif ($tabActif === 'presences'): ?>
    <div class="mb-4">
      <h4 class="text-blue mb-0">Liste de présence</h4>
      <p class="text-muted small">Sélectionnez une réunion puis cochez les membres présents</p>
    </div>

    <!-- Sélecteur de réunion -->
    <div class="dash-panel mb-4">
      <div class="dash-panel-body p-3">
        <form method="GET" action="index.php" class="d-flex gap-3 align-items-end flex-wrap">
          <input type="hidden" name="page" value="admin_dashboard">
          <input type="hidden" name="tab" value="presences">
          <div class="flex-grow-1">
            <label class="form-label fw-bold">Choisir une réunion</label>
            <select class="form-select" name="reunion_id">
              <option value="">— Sélectionner —</option>
              <?php foreach ($reunions as $r): ?>
              <option value="<?= $r['id'] ?>" <?= (isset($_GET['reunion_id']) && $_GET['reunion_id'] == $r['id']) ? 'selected' : '' ?>>
                <?= htmlspecialchars($r['titre']) ?> — <?= formatDateFr($r['date_reunion']) ?>
              </option>
              <?php endforeach; ?>
            </select>
          </div>
          <button type="submit" class="btn btn-joker-blue">
            <i class="bi bi-search me-1"></i>Charger
          </button>
        </form>
      </div>
    </div>

    <?php if (!empty($presences) && $reunionPresence): ?>
    <div class="dash-panel">
      <div class="dash-panel-header">
        <h5><i class="bi bi-people me-2 text-blue"></i><?= htmlspecialchars($reunionPresence['titre']) ?> — <?= formatDateFr($reunionPresence['date_reunion']) ?></h5>
        <?php
          $nbPresents = count(array_filter($presences, fn($p) => $p['statut'] === 'present'));
          $total      = count($presences);
        ?>
        <span class="badge bg-success"><?= $nbPresents ?>/<?= $total ?> présents</span>
      </div>
      <div class="dash-panel-body">
        <form method="POST" action="index.php?page=admin_gerer_presences">
          <input type="hidden" name="id_reunion" value="<?= $reunionPresence['id'] ?>">
          <div class="table-responsive">
            <table class="joker-table">
              <thead><tr><th>Membre</th><th>Email</th><th class="text-center">Présent ✓</th></tr></thead>
              <tbody>
                <?php foreach ($presences as $p): ?>
                <tr>
                  <td>
                    <div class="d-flex align-items-center gap-2">
                      <div class="sidebar-avatar" style="width:32px;height:32px;font-size:.75rem;background:var(--blue)">
                        <?= mb_strtoupper(mb_substr($p['nom'],0,2)) ?>
                      </div>
                      <strong><?= htmlspecialchars($p['nom']) ?></strong>
                    </div>
                  </td>
                  <td class="small text-muted"><?= htmlspecialchars($p['email']) ?></td>
                  <td class="text-center">
                    <div class="form-check d-flex justify-content-center">
                      <input class="form-check-input" type="checkbox"
                             name="presences[<?= $p['id_membre'] ?>]"
                             value="present"
                             <?= $p['statut'] === 'present' ? 'checked' : '' ?>
                             style="width:1.3rem;height:1.3rem;cursor:pointer">
                    </div>
                  </td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
          <div class="mt-3 d-flex gap-2">
            <button type="submit" class="btn btn-joker-blue">
              <i class="bi bi-save me-1"></i>Enregistrer la présence
            </button>
            <button type="button" class="btn btn-joker-outline"
                    onclick="document.querySelectorAll('input[type=checkbox]').forEach(c=>c.checked=true)">
              Tous présents
            </button>
            <button type="button" class="btn btn-joker-outline"
                    onclick="document.querySelectorAll('input[type=checkbox]').forEach(c=>c.checked=false)">
              Tous absents
            </button>
          </div>
        </form>
      </div>
    </div>
    <?php elseif (isset($_GET['reunion_id']) && !empty($_GET['reunion_id'])): ?>
    <div class="empty-state"><i class="bi bi-people fs-1"></i><p>Aucun membre trouvé.</p></div>
    <?php endif; ?>

    <!-- ══════════════════════════════════
         TAB: REQUESTS
    ═══════════════════════════════════ -->
    <?php elseif ($tabActif === 'requests'): ?>
    <div class="mb-4">
      <h4 class="text-blue mb-0">Demandes d'adhésion</h4>
      <p class="text-muted small">Acceptez ou refusez les demandes pour rejoindre le club</p>
    </div>
    <div class="dash-panel">
      <div class="dash-panel-body p-0">
        <div class="table-responsive">
          <table class="joker-table">
            <thead><tr><th>Nom</th><th>Email</th><th>Date</th><th>Message</th><th>Statut</th><th>Actions</th></tr></thead>
            <tbody>
              <?php if (empty($demandes)): ?>
                <tr><td colspan="6" class="text-center text-muted py-4">Aucune demande</td></tr>
              <?php else: ?>
                <?php foreach ($demandes as $d): ?>
                <tr>
                  <td><strong><?= htmlspecialchars($d['nom']) ?></strong></td>
                  <td><?= htmlspecialchars($d['email']) ?></td>
                  <td><?= formatDateFr(substr($d['date_demande'], 0, 10)) ?></td>
                  <td class="small" style="max-width:200px"><?= htmlspecialchars(mb_substr($d['message'] ?? '', 0, 60)) ?>...</td>
                  <td>
                    <?php $sMap = ['en_attente'=>['pending','En attente'],'accepte'=>['accepted','Accepté'],'refuse'=>['refused','Refusé']];
                    [$cls,$label] = $sMap[$d['statut']] ?? ['pending','—']; ?>
                    <span class="badge-status <?= $cls ?>"><?= $label ?></span>
                  </td>
                  <td>
                    <?php if ($d['statut'] === 'en_attente'): ?>
                    <a href="index.php?page=admin_traiter_demande&id=<?= $d['id'] ?>&action=accepte" class="btn btn-sm btn-joker-blue me-1"><i class="bi bi-check-lg"></i> Accepter</a>
                    <a href="index.php?page=admin_traiter_demande&id=<?= $d['id'] ?>&action=refuse" class="btn btn-sm btn-joker-red"><i class="bi bi-x-lg"></i> Refuser</a>
                    <?php else: ?><span class="text-muted small">—</span><?php endif; ?>
                  </td>
                </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- ══════════════════════════════════
         TAB: TASKS
    ═══════════════════════════════════ -->
    <?php elseif ($tabActif === 'tasks'): ?>
    <div class="d-flex align-items-center justify-content-between mb-4">
      <div><h4 class="text-blue mb-0">Gestion des tâches</h4><p class="text-muted small mb-0">Assignez et suivez les tâches</p></div>
      <button class="btn btn-joker-red" data-bs-toggle="modal" data-bs-target="#modalAjouterTache">
        <i class="bi bi-plus-lg me-1"></i>Assigner une tâche
      </button>
    </div>
    <div class="dash-panel">
      <div class="dash-panel-body p-0">
        <div class="table-responsive">
          <table class="joker-table">
            <thead><tr><th>Tâche</th><th>Assigné à</th><th>Deadline</th><th>Priorité</th><th>Statut</th><th>Actions</th></tr></thead>
            <tbody>
              <?php if (empty($taches)): ?>
                <tr><td colspan="6" class="text-center text-muted py-4">Aucune tâche</td></tr>
              <?php else: ?>
                <?php foreach ($taches as $t): ?>
                <tr>
                  <td><strong><?= htmlspecialchars($t['titre']) ?></strong></td>
                  <td><?= htmlspecialchars($t['assigné_nom'] ?? '—') ?></td>
                  <td><?= $t['deadline'] ? formatDateFr($t['deadline']) : '—' ?></td>
                  <td><?= prioriteBadge($t['priorite']) ?></td>
                  <td><span class="badge-status <?= $t['statut'] === 'termine' ? 'accepted' : 'pending' ?>"><?= $t['statut'] === 'termine' ? 'Fait' : 'En cours' ?></span></td>
                  <td>
                    <a href="index.php?page=admin_toggle_tache&id=<?= $t['id'] ?>" class="btn btn-sm btn-joker-blue me-1">
                      <i class="bi bi-<?= $t['statut'] === 'termine' ? 'arrow-counterclockwise' : 'check-lg' ?>"></i>
                    </a>
                    <a href="index.php?page=admin_supprimer_tache&id=<?= $t['id'] ?>" class="btn btn-sm btn-joker-red" onclick="return confirm('Supprimer ?')"><i class="bi bi-trash"></i></a>
                  </td>
                </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Modal Ajouter Tâche -->
    <div class="modal fade modal-joker" id="modalAjouterTache" tabindex="-1">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header"><h5 class="modal-title">Assigner une tâche</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
          <form method="POST" action="index.php?page=admin_ajouter_tache">
            <div class="modal-body p-4">
              <div class="form-joker row g-3">
                <div class="col-12"><label class="form-label">Tâche *</label><input type="text" class="form-control" name="titre" required></div>
                <div class="col-md-6"><label class="form-label">Assigné à</label>
                  <select class="form-select" name="id_assigne">
                    <?php foreach ($membres as $m): ?><option value="<?= $m['id'] ?>"><?= htmlspecialchars($m['nom']) ?></option><?php endforeach; ?>
                  </select>
                </div>
                <div class="col-md-6"><label class="form-label">Priorité</label>
                  <select class="form-select" name="priorite">
                    <option value="haute">🔴 Haute</option><option value="moyenne" selected>🟡 Moyenne</option><option value="faible">🟢 Faible</option>
                  </select>
                </div>
                <div class="col-12"><label class="form-label">Deadline</label><input type="date" class="form-control" name="deadline"></div>
              </div>
            </div>
            <div class="modal-footer"><button type="submit" class="btn btn-joker-blue"><i class="bi bi-check-lg me-1"></i>Créer</button></div>
          </form>
        </div>
      </div>
    </div>

    <!-- ══════════════════════════════════
         TAB: STATISTIQUES
    ═══════════════════════════════════ -->
    <?php elseif ($tabActif === 'stats'): ?>
    <div class="mb-4">
      <h4 class="text-blue mb-0"><i class="bi bi-bar-chart-line me-2"></i>Rapports de synthèse & Statistiques</h4>
      <p class="text-muted small">Vue analytique complète de l'activité du club</p>
    </div>

    <!-- KPIs -->
    <div class="row g-3 mb-4">
      <div class="col-6 col-lg-3">
        <div class="stat-card">
          <div class="stat-icon blue"><i class="bi bi-calendar-event"></i></div>
          <div><div class="stat-value"><?= $stats['evenements'] ?></div><div class="stat-label">Total événements</div></div>
        </div>
      </div>
      <div class="col-6 col-lg-3">
        <div class="stat-card">
          <div class="stat-icon red"><i class="bi bi-people-fill"></i></div>
          <div><div class="stat-value"><?= $stats['membres'] ?></div><div class="stat-label">Membres actifs</div></div>
        </div>
      </div>
      <div class="col-6 col-lg-3">
        <div class="stat-card">
          <div class="stat-icon gold"><i class="bi bi-calendar2-check"></i></div>
          <div><div class="stat-value"><?= $stats['reunions'] ?></div><div class="stat-label">Réunions tenues</div></div>
        </div>
      </div>
      <div class="col-6 col-lg-3">
        <div class="stat-card">
          <div class="stat-icon green"><i class="bi bi-graph-up"></i></div>
          <div><div class="stat-value"><?= $tauxPresence ?>%</div><div class="stat-label">Taux de présence</div></div>
        </div>
      </div>
    </div>

    <div class="row g-4">
      <!-- Événements par type -->
      <div class="col-lg-4">
        <div class="dash-panel h-100">
          <div class="dash-panel-header"><h5><i class="bi bi-pie-chart me-2 text-blue"></i>Événements par type</h5></div>
          <div class="dash-panel-body">
            <?php foreach ($statsEvenements as $s): ?>
            <div class="d-flex justify-content-between align-items-center mb-3">
              <span class="fw-bold"><?= $s['type'] === 'public' ? '🌐 Public' : '🔒 Privé' ?></span>
              <div class="d-flex align-items-center gap-2">
                <div style="height:8px;border-radius:4px;background:<?= $s['type']==='public'?'var(--blue)':'var(--red)' ?>;width:<?= min($s['total']*20,150) ?>px"></div>
                <strong><?= $s['total'] ?></strong>
              </div>
            </div>
            <?php endforeach; ?>
          </div>
        </div>
      </div>

      <!-- Demandes par statut -->
      <div class="col-lg-4">
        <div class="dash-panel h-100">
          <div class="dash-panel-header"><h5><i class="bi bi-person-lines-fill me-2 text-red"></i>Demandes d'adhésion</h5></div>
          <div class="dash-panel-body">
            <?php
            $statutColors = ['en_attente'=>'#f39c12','accepte'=>'#27ae60','refuse'=>'#e74c3c'];
            $statutLabels = ['en_attente'=>'⏳ En attente','accepte'=>'✅ Acceptées','refuse'=>'❌ Refusées'];
            foreach ($statsDemandes as $s): ?>
            <div class="d-flex justify-content-between align-items-center mb-3">
              <span class="fw-bold"><?= $statutLabels[$s['statut']] ?? $s['statut'] ?></span>
              <div class="d-flex align-items-center gap-2">
                <div style="height:8px;border-radius:4px;background:<?= $statutColors[$s['statut']] ?? '#999' ?>;width:<?= min($s['total']*20,150) ?>px"></div>
                <strong><?= $s['total'] ?></strong>
              </div>
            </div>
            <?php endforeach; ?>
          </div>
        </div>
      </div>

      <!-- Tâches par statut -->
      <div class="col-lg-4">
        <div class="dash-panel h-100">
          <div class="dash-panel-header"><h5><i class="bi bi-check2-square me-2 text-blue"></i>Avancement des tâches</h5></div>
          <div class="dash-panel-body">
            <?php
            $totalTaches  = array_sum(array_column($statsTaches, 'total'));
            $tachesMap    = [];
            foreach ($statsTaches as $s) $tachesMap[$s['statut']] = (int)$s['total'];
            $terminees    = $tachesMap['termine']  ?? 0;
            $enCours      = $tachesMap['en_cours'] ?? 0;
            $pct          = $totalTaches > 0 ? round($terminees/$totalTaches*100) : 0;
            ?>
            <div class="text-center mb-3">
              <div style="font-size:2.5rem;font-weight:800;color:var(--blue)"><?= $pct ?>%</div>
              <div class="text-muted small">des tâches terminées</div>
            </div>
            <div class="progress mb-3" style="height:12px;border-radius:6px">
              <div class="progress-bar" role="progressbar" style="width:<?= $pct ?>%;background:var(--blue)" aria-valuenow="<?= $pct ?>" aria-valuemax="100"></div>
            </div>
            <div class="d-flex justify-content-between small">
              <span>✅ Terminées : <strong><?= $terminees ?></strong></span>
              <span>🔄 En cours : <strong><?= $enCours ?></strong></span>
            </div>
          </div>
        </div>
      </div>

      <!-- Liste complète des membres -->
      <div class="col-12">
        <div class="dash-panel">
          <div class="dash-panel-header">
            <h5><i class="bi bi-people me-2 text-blue"></i>Rapport membres</h5>
            <span class="badge bg-primary"><?= count($tousMembres) ?> au total</span>
          </div>
          <div class="dash-panel-body p-0">
            <div class="table-responsive">
              <table class="joker-table">
                <thead><tr><th>Nom</th><th>Email</th><th>Rôle</th><th>Statut</th><th>Téléphone</th><th>Inscription</th></tr></thead>
                <tbody>
                  <?php foreach ($tousMembres as $m): ?>
                  <tr>
                    <td><strong><?= htmlspecialchars($m['nom']) ?></strong></td>
                    <td><?= htmlspecialchars($m['email']) ?></td>
                    <td><span class="badge <?= $m['role']==='admin'?'bg-danger':'bg-primary' ?>"><?= $m['role'] ?></span></td>
                    <td><span class="badge-status <?= $m['statut']==='actif'?'active':'refused' ?>"><?= $m['statut'] ?></span></td>
                    <td><?= htmlspecialchars($m['telephone'] ?? '—') ?></td>
                    <td><?= $m['date_inscription'] ? formatDateFr($m['date_inscription']) : '—' ?></td>
                  </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- ══════════════════════════════════
         TAB: MEMBERS
    ═══════════════════════════════════ -->
    <?php elseif ($tabActif === 'members'): ?>
    <div class="mb-4">
      <h4 class="text-blue mb-0">Gestion des membres</h4>
      <p class="text-muted small">Liste de tous les utilisateurs du club</p>
    </div>
    <div class="dash-panel">
      <div class="dash-panel-body p-0">
        <div class="table-responsive">
          <table class="joker-table">
            <thead><tr><th>Nom</th><th>Email</th><th>Rôle</th><th>Statut</th><th>Téléphone</th><th>Inscription</th></tr></thead>
            <tbody>
              <?php foreach ($tousMembres as $m): ?>
              <tr>
                <td>
                  <div class="d-flex align-items-center gap-2">
                    <div class="sidebar-avatar" style="width:32px;height:32px;font-size:.75rem;background:var(--blue)">
                      <?= mb_strtoupper(mb_substr($m['nom'],0,2)) ?>
                    </div>
                    <strong><?= htmlspecialchars($m['nom']) ?></strong>
                  </div>
                </td>
                <td><?= htmlspecialchars($m['email']) ?></td>
                <td><span class="badge <?= $m['role']==='admin'?'bg-danger':'bg-primary' ?>"><?= $m['role'] ?></span></td>
                <td><span class="badge-status <?= $m['statut']==='actif'?'active':'refused' ?>"><?= $m['statut'] ?></span></td>
                <td><?= htmlspecialchars($m['telephone'] ?? '—') ?></td>
                <td><?= $m['date_inscription'] ? formatDateFr($m['date_inscription']) : '—' ?></td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <?php endif; ?>

  </div><!-- /dashboard-body -->
</main>
</div><!-- /dashboard-wrapper -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Sidebar toggle mobile
document.getElementById('sidebar-toggle')?.addEventListener('click', () => {
  document.getElementById('sidebar').classList.toggle('open');
  document.getElementById('sidebar-overlay').classList.toggle('active');
});
document.getElementById('sidebar-overlay')?.addEventListener('click', () => {
  document.getElementById('sidebar').classList.remove('open');
  document.getElementById('sidebar-overlay').classList.remove('active');
});

// Pré-remplir modal modifier événement
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

// Pré-remplir modal modifier réunion
document.getElementById('modalModifReunion')?.addEventListener('show.bs.modal', e => {
  const b = e.relatedTarget;
  document.getElementById('modif-r-id').value    = b.dataset.id;
  document.getElementById('modif-r-titre').value = b.dataset.titre;
  document.getElementById('modif-r-date').value  = b.dataset.date;
  document.getElementById('modif-r-heure').value = b.dataset.heure;
  document.getElementById('modif-r-lieu').value  = b.dataset.lieu;
  document.getElementById('modif-r-type').value  = b.dataset.type;
  document.getElementById('modif-r-lien').value  = b.dataset.lien;
  document.getElementById('modif-r-odj').value   = b.dataset.odj;
});
</script>
</body>