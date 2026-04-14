<?php
$titreePage = 'Connexion';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Club Joker — Connexion</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
  <link rel="stylesheet" href="public/style.css">
</head>
<body class="login-page">

<div class="container px-3">
  <div class="d-flex justify-content-center">
    <div class="login-card animate-fadeInUp">

      <div class="login-logo">🃏 Club <span>Joker</span></div>
      <p class="text-center text-muted small mb-4">Connectez-vous à votre compte</p>

      <!-- Flash Messages -->
      <?php if (!empty($erreur)): ?>
        <div class="alert alert-danger rounded-joker small">
          <i class="bi bi-exclamation-circle me-1"></i><?= htmlspecialchars($erreur) ?>
        </div>
      <?php endif; ?>
      <?php if (!empty($succes)): ?>
        <div class="alert alert-success rounded-joker small">
          <i class="bi bi-check-circle me-1"></i><?= htmlspecialchars($succes) ?>
        </div>
      <?php endif; ?>

      <!-- Comptes démo -->
      <div class="alert"
           style="background:rgba(26,58,107,0.07);border:1px solid rgba(26,58,107,0.15);border-radius:var(--radius);font-size:.82rem;color:var(--blue)"
           role="alert">
        <strong><i class="bi bi-key-fill me-1"></i>Comptes de démonstration :</strong><br>
        <div class="mt-1">
          <span class="badge-status active me-1">Admin</span>
          <code>admin@joker.tn</code> / <code>admin123</code>
        </div>
        <div class="mt-1">
          <span class="badge-status accepted me-1">Membre</span>
          <code>sarra@joker.tn</code> / <code>membre123</code>
        </div>
      </div>

      <!-- Formulaire -->
      <form method="POST" action="index.php?page=login" class="form-joker mt-3">
        <div class="mb-3">
          <label for="email" class="form-label">Email</label>
          <div class="input-group">
            <span class="input-group-text"
                  style="background:var(--beige-light);border:2px solid var(--beige-dark);border-right:none;border-radius:var(--radius) 0 0 var(--radius)">
              <i class="bi bi-envelope-fill text-blue"></i>
            </span>
            <input type="email" class="form-control" id="email" name="email"
                   placeholder="votre@email.com"
                   style="border-left:none;border-radius:0 var(--radius) var(--radius) 0"
                   required autofocus>
          </div>
        </div>
        <div class="mb-4">
          <label for="password" class="form-label">Mot de passe</label>
          <div class="input-group">
            <span class="input-group-text"
                  style="background:var(--beige-light);border:2px solid var(--beige-dark);border-right:none;border-radius:var(--radius) 0 0 var(--radius)">
              <i class="bi bi-lock-fill text-blue"></i>
            </span>
            <input type="password" class="form-control" id="password" name="password"
                   placeholder="••••••••"
                   style="border-left:none;border-radius:0 var(--radius) var(--radius) 0"
                   required>
          </div>
        </div>
        <button type="submit" class="btn btn-joker-blue w-100 py-2">
          <i class="bi bi-box-arrow-in-right me-2"></i>Se connecter
        </button>
      </form>

      <hr style="border-color:var(--beige-dark);margin:1.5rem 0">

      <div class="text-center">
        <p class="small text-muted mb-2">Pas encore membre ?</p>
        <a href="index.php?page=accueil#rejoindre" class="btn btn-joker-outline w-100">
          <i class="bi bi-person-plus me-2"></i>Nous rejoindre
        </a>
      </div>

      <div class="text-center mt-3">
        <a href="index.php?page=accueil" class="small" style="color:var(--text-muted)">
          <i class="bi bi-arrow-left me-1"></i>Retour à l'accueil
        </a>
      </div>

    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>