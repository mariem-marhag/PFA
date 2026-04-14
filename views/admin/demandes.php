<?php
/**
 * views/admin/demandes.php
 * Gestion des demandes d'adhésion — vue standalone
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
  <title>Club Joker — Demandes d'adhésion</title>
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
         class="nav-item <?= $key === 'requests' ? 'active' : '' ?>"
         style="text-decoration:none">
        <i class="bi bi-<?= $item['icon'] ?>"></i>
        <?= $item['label'] ?>
        <?php if ($key === 'requests'): ?>
          <?php
          $nbEnAttente = 0;
          foreach (($demandes ?? []) as $d) {
              if ($d['statut'] === 'en_attente') $nbEnAttente++;
          }
          if ($nbEnAttente > 0):
          ?>
          <span class="badge bg-danger ms-auto"><?= $nbEnAttente ?></span>
          <?php endif; ?>
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
  <div class="dashboard-topbar">
    <div class="d-flex align-items-center gap-3">
      <button class="btn btn-sm d-lg-none" id="sidebar-toggle"
              style="background:var(--beige);border:none;border-radius:8px;padding:.4rem .7rem">
        <i class="bi bi-list fs-5 text-blue"></i>
      </button>
      <h1>Demandes d'adhésion</h1>
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
    <div class="mb-4">
      <h4 class="text-blue mb-0">Candidatures reçues</h4>
      <p class="text-muted small">Acceptez ou refusez les demandes pour rejoindre le club</p>
    </div>

    <!-- Compteurs rapides -->
    <?php
    $nbAttente  = 0; $nbAccepte = 0; $nbRefuse = 0;
    foreach (($demandes ?? []) as $d) {
        if ($d['statut'] === 'en_attente') $nbAttente++;
        elseif ($d['statut'] === 'accepte') $nbAccepte++;
        else $nbRefuse++;
    }
    ?>
    <div class="row g-3 mb-4">
      <div class="col-sm-4">
        <div class="stat-card">
          <div class="stat-icon gold"><i class="bi bi-hourglass-split"></i></div>
          <div><div class="stat-value"><?= $nbAttente ?></div><div class="stat-label">En attente</div></div>
        </div>
      </div>
      <div class="col-sm-4">
        <div class="stat-card">
          <div class="stat-icon blue"><i class="bi bi-person-check-fill"></i></div>
          <div><div class="stat-value"><?= $nbAccepte ?></div><div class="stat-label">Acceptées</div></div>
        </div>
      </div>
      <div class="col-sm-4">
        <div class="stat-card">
          <div class="stat-icon red"><i class="bi bi-person-x-fill"></i></div>
          <div><div class="stat-value"><?= $nbRefuse ?></div><div class="stat-label">Refusées</div></div>
        </div>
      </div>
    </div>

    <!-- Tableau demandes -->
    <div class="dash-panel">
      <div class="dash-panel-body p-0">
        <div class="table-responsive">
          <table class="joker-table">
            <thead>
              <tr>
                <th>Candidat</th>
                <th>Contact</th>
                <th>Date</th>
                <th>Message</th>
                <th>Statut</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($demandes)): ?>
                <tr><td colspan="6" class="text-center text-muted py-5">
                  <i class="bi bi-inbox fs-2 d-block mb-2"></i>Aucune demande reçue
                </td></tr>
              <?php else: ?>
                <?php foreach ($demandes as $d):
                  $sMap = [
                    'en_attente' => ['pending',  '⏳ En attente'],
                    'accepte'    => ['accepted', '✅ Accepté'],
                    'refuse'     => ['refused',  '❌ Refusé'],
                  ];
                  [$cls, $label] = $sMap[$d['statut']] ?? ['pending','—'];
                ?>
                <tr>
                  <td>
                    <div class="d-flex align-items-center gap-2">
                      <div class="sidebar-avatar" style="width:36px;height:36px;font-size:.8rem;background:var(--blue);flex-shrink:0">
                        <?= mb_strtoupper(mb_substr($d['nom'], 0, 2)) ?>
                      </div>
                      <strong><?= htmlspecialchars($d['nom']) ?></strong>
                    </div>
                  </td>
                  <td>
                    <div><?= htmlspecialchars($d['email']) ?></div>
                    <?php if ($d['telephone']): ?>
                    <div class="small text-muted"><?= htmlspecialchars($d['telephone']) ?></div>
                    <?php endif; ?>
                  </td>
                  <td class="small"><?= formatDateFr(substr($d['date_demande'], 0, 10)) ?></td>
                  <td class="small" style="max-width:220px">
                    <?php if (!empty($d['message'])): ?>
                      <span title="<?= htmlspecialchars($d['message']) ?>">
                        <?= htmlspecialchars(mb_substr($d['message'], 0, 70)) ?>
                        <?= mb_strlen($d['message']) > 70 ? '...' : '' ?>
                      </span>
                    <?php else: ?>
                      <span class="text-muted fst-italic">Aucun message</span>
                    <?php endif; ?>
                  </td>
                  <td><span class="badge-status <?= $cls ?>"><?= $label ?></span></td>
                  <td>
                    <?php if ($d['statut'] === 'en_attente'): ?>
                    <a href="index.php?page=admin_traiter_demande&id=<?= $d['id'] ?>&action=accepte"
                       class="btn btn-sm btn-joker-blue me-1"
                       onclick="return confirm('Accepter <?= htmlspecialchars($d['nom']) ?> comme membre ? Un compte sera créé.')">
                      <i class="bi bi-check-lg"></i> Accepter
                    </a>
                    <a href="index.php?page=admin_traiter_demande&id=<?= $d['id'] ?>&action=refuse"
                       class="btn btn-sm btn-joker-red"
                       onclick="return confirm('Refuser la demande de <?= htmlspecialchars($d['nom']) ?> ?')">
                      <i class="bi bi-x-lg"></i> Refuser
                    </a>
                    <?php else: ?>
                      <span class="text-muted small">Traitée</span>
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

    <!-- Note info -->
    <div class="alert mt-3"
         style="background:rgba(26,58,107,0.07);border:1px solid rgba(26,58,107,0.15);border-radius:var(--radius);color:var(--blue)">
      <i class="bi bi-info-circle me-2"></i>
      <strong>Note :</strong> Accepter une demande crée automatiquement un compte membre avec le mot de passe temporaire
      <code>joker123</code>. Informez le nouveau membre de changer son mot de passe.
    </div>

  </div><!-- /dashboard-body -->
</main>
</div><!-- /dashboard-wrapper -->

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