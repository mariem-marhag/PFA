<?php
/**
 * index.php — Front Controller (Point d'entrée unique)
 * Club Joker — Architecture MVC
 *
 * Toutes les URLs passent par : index.php?page=xxx&action=yyy
 */

// ── Démarrer la session ──────────────────────────────────────
session_start();

// ── Autoload des classes ─────────────────────────────────────
spl_autoload_register(function (string $classe) {
    $chemins = [
        __DIR__ . '/models/'      . $classe . '.php',
        __DIR__ . '/controllers/' . $classe . '.php',
    ];
    foreach ($chemins as $chemin) {
        if (file_exists($chemin)) {
            require_once $chemin;
            return;
        }
    }
});

// ── Récupérer la page et l'action demandées ──────────────────
$page   = $_GET['page']   ?? 'accueil';
$action = $_GET['action'] ?? null;

// ── Routeur principal ────────────────────────────────────────
switch ($page) {

    // ══════════════════════════════
    //  PAGES PUBLIQUES
    // ══════════════════════════════

    case 'accueil':
        require_once __DIR__ . '/models/EvenementModel.php';
        require_once __DIR__ . '/models/DemandeModel.php';

        $evenementModel  = new EvenementModel();
        $demandeModel    = new DemandeModel();
        $evenements      = $evenementModel->getPublics(3);

        $flash_success   = $_SESSION['flash_success'] ?? null;
        $flash_error     = $_SESSION['flash_error']   ?? null;
        unset($_SESSION['flash_success'], $_SESSION['flash_error']);

        require_once __DIR__ . '/views/public/accueil.php';
        break;

    case 'evenements':
        require_once __DIR__ . '/controllers/EvenementController.php';
        $ctrl = new EvenementController();
        $ctrl->afficherListe();
        break;

    case 'inscrire_evenement':
        require_once __DIR__ . '/controllers/EvenementController.php';
        $ctrl = new EvenementController();
        $ctrl->inscrire();
        break;

    case 'reunions':
        require_once __DIR__ . '/controllers/ReunionController.php';
        $ctrl = new ReunionController();
        $ctrl->afficherListe();
        break;

    case 'rejoindre':
        require_once __DIR__ . '/models/DemandeModel.php';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $demandeModel = new DemandeModel();
            $nom       = trim($_POST['nom']       ?? '');
            $email     = trim($_POST['email']     ?? '');
            $telephone = trim($_POST['telephone'] ?? '');
            $message   = trim($_POST['message']   ?? '');
            if (empty($nom) || empty($email)) {
                $_SESSION['flash_error'] = 'Nom et email sont obligatoires.';
            } else {
                $demandeModel->creer($nom, $email, $telephone, $message);
                $_SESSION['flash_success'] = '🎉 Demande envoyée ! L\'admin examinera votre candidature.';
            }
            header('Location: index.php?page=rejoindre');
            exit;
        }
        $flash_success = $_SESSION['flash_success'] ?? null;
        $flash_error   = $_SESSION['flash_error']   ?? null;
        unset($_SESSION['flash_success'], $_SESSION['flash_error']);
        require_once __DIR__ . '/views/public/rejoindre.php';
        break;

    // ══════════════════════════════
    //  AUTHENTIFICATION
    // ══════════════════════════════

    case 'login':
        require_once __DIR__ . '/controllers/AuthController.php';
        $ctrl = new AuthController();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $ctrl->traiterLogin();
        } else {
            $ctrl->afficherLogin();
        }
        break;

    case 'logout':
        require_once __DIR__ . '/controllers/AuthController.php';
        $ctrl = new AuthController();
        $ctrl->deconnecter();
        break;

    // ══════════════════════════════
    //  DASHBOARD ADMIN
    // ══════════════════════════════

    case 'admin_dashboard':
        require_once __DIR__ . '/controllers/AdminController.php';
        $ctrl = new AdminController();
        $ctrl->dashboard();
        break;

    case 'admin_ajouter_evenement':
        require_once __DIR__ . '/controllers/AdminController.php';
        $ctrl = new AdminController();
        $ctrl->ajouterEvenement();
        break;

    case 'admin_modifier_evenement':
        require_once __DIR__ . '/controllers/AdminController.php';
        $ctrl = new AdminController();
        $ctrl->modifierEvenement();
        break;

    case 'admin_supprimer_evenement':
        require_once __DIR__ . '/controllers/AdminController.php';
        $ctrl = new AdminController();
        $ctrl->supprimerEvenement();
        break;

    case 'admin_ajouter_reunion':
        require_once __DIR__ . '/controllers/AdminController.php';
        $ctrl = new AdminController();
        $ctrl->ajouterReunion();
        break;

    case 'admin_modifier_reunion':
        require_once __DIR__ . '/controllers/AdminController.php';
        $ctrl = new AdminController();
        $ctrl->modifierReunion();
        break;

    case 'admin_supprimer_reunion':
        require_once __DIR__ . '/controllers/AdminController.php';
        $ctrl = new AdminController();
        $ctrl->supprimerReunion();
        break;

    case 'admin_traiter_demande':
        require_once __DIR__ . '/controllers/AdminController.php';
        $ctrl = new AdminController();
        $ctrl->traiterDemande();
        break;

    case 'admin_ajouter_tache':
        require_once __DIR__ . '/controllers/AdminController.php';
        $ctrl = new AdminController();
        $ctrl->ajouterTache();
        break;

    case 'admin_toggle_tache':
        require_once __DIR__ . '/controllers/AdminController.php';
        $ctrl = new AdminController();
        $ctrl->toggleTache();
        break;

    case 'admin':
        header('Location: index.php?page=admin_dashboard');
        exit;
        break;

    case 'admin_supprimer_tache':
        require_once __DIR__ . '/controllers/AdminController.php';
        $ctrl = new AdminController();
        $ctrl->supprimerTache();
        break;

    case 'admin_gerer_presences':
        require_once __DIR__ . '/controllers/AdminController.php';
        $ctrl = new AdminController();
        $ctrl->gererPresences();
        break;

    // ══════════════════════════════
    //  DASHBOARD MEMBRE
    // ══════════════════════════════

    case 'membre_dashboard':
        require_once __DIR__ . '/controllers/MembreController.php';
        $ctrl = new MembreController();
        $ctrl->dashboard();
        break;

    case 'membre_ajouter_todo':
        require_once __DIR__ . '/controllers/MembreController.php';
        $ctrl = new MembreController();
        $ctrl->ajouterTodo();
        break;

    case 'membre_toggle_todo':
        require_once __DIR__ . '/controllers/MembreController.php';
        $ctrl = new MembreController();
        $ctrl->toggleTodo();
        break;

    case 'membre_supprimer_todo':
        require_once __DIR__ . '/controllers/MembreController.php';
        $ctrl = new MembreController();
        $ctrl->supprimerTodo();
        break;

    // ══════════════════════════════
    //  404
    // ══════════════════════════════

    default:
        require_once __DIR__ . '/models/EvenementModel.php';
        require_once __DIR__ . '/models/DemandeModel.php';
        $evenementModel = new EvenementModel();
        $evenements     = $evenementModel->getPublics(3);
        $flash_success  = $_SESSION['flash_success'] ?? null;
        $flash_error    = $_SESSION['flash_error']   ?? null;
        unset($_SESSION['flash_success'], $_SESSION['flash_error']);
        require_once __DIR__ . '/views/public/accueil.php';
    break;
}