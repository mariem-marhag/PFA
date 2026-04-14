<?php
/**
 * controllers/AdminController.php
 * CORRECTIONS + AJOUTS :
 *  - visiteurs & statsParMois dans dashboard()
 *  - supprimerMembre() ajouté
 *  - traiterDemande() utilise accepter() avec création auto de l'utilisateur
 *  - gererPresences() corrigé (sauvegarderPresences)
 */

require_once __DIR__ . '/../models/EvenementModel.php';
require_once __DIR__ . '/../models/ReunionModel.php';
require_once __DIR__ . '/../models/DemandeModel.php';
require_once __DIR__ . '/../models/TacheModel.php';
require_once __DIR__ . '/../models/UtilisateurModel.php';
require_once __DIR__ . '/../models/PresenceModel.php';
require_once __DIR__ . '/../controllers/AuthController.php';

class AdminController
{
    private EvenementModel   $evenementModel;
    private ReunionModel     $reunionModel;
    private DemandeModel     $demandeModel;
    private TacheModel       $tacheModel;
    private UtilisateurModel $utilisateurModel;
    private PresenceModel    $presenceModel;

    public function __construct()
    {
        AuthController::exigerAdmin();

        $this->evenementModel   = new EvenementModel();
        $this->reunionModel     = new ReunionModel();
        $this->demandeModel     = new DemandeModel();
        $this->tacheModel       = new TacheModel();
        $this->utilisateurModel = new UtilisateurModel();
        $this->presenceModel    = new PresenceModel();
    }

    // ════════════════════════════════════════════════════════
    //  DASHBOARD
    // ════════════════════════════════════════════════════════
    public function dashboard(): void
    {
        $stats = [
            'evenements' => $this->evenementModel->compter(),
            'reunions'   => $this->reunionModel->compter(),
            'membres'    => $this->utilisateurModel->compterMembres(),
            'demandes'   => $this->demandeModel->compterEnAttente(),
            'visiteurs'  => $this->demandeModel->compterVisiteurs(),  // AJOUT
        ];

        $evenements_recents  = $this->evenementModel->getTous();
        $demandes_en_attente = $this->demandeModel->getEnAttente();

        $tab           = $_GET['tab'] ?? 'overview';
        $flash_success = $_SESSION['flash_success'] ?? null;
        $flash_error   = $_SESSION['flash_error']   ?? null;
        unset($_SESSION['flash_success'], $_SESSION['flash_error']);

        $evenements  = $this->evenementModel->getTous();
        $reunions    = $this->reunionModel->getTous();
        $demandes    = $this->demandeModel->getTous();
        $taches      = $this->tacheModel->getTous();
        $membres     = $this->utilisateurModel->listeMembres();
        $tousMembres = $this->utilisateurModel->getTousMembres();

        // ── Données pour l'onglet présences ──────────────────
        $presences       = [];
        $reunionPresence = null;
        if ($tab === 'presences') {
            $idReunion = (int)($_GET['reunion_id'] ?? 0);
            if ($idReunion) {
                $reunionPresence = $this->reunionModel->getParId($idReunion);
                // Initialise les lignes absentes pour les nouveaux membres
                $this->presenceModel->initialiserPourReunion($idReunion, $membres);
                $presences = $this->presenceModel->getParReunion($idReunion);
            }
        }

        // ── Données pour l'onglet rapports ───────────────────
        $statsEvenements = $this->evenementModel->statsParType();
        $statsDemandes   = $this->demandeModel->stats();
        $statsTaches     = $this->tacheModel->stats();
        $tauxPresence    = $this->presenceModel->tauxPresenceGlobal();  // array
        $statsParMois    = $this->demandeModel->statsParMois();         // AJOUT

        // Stats tâches calculées
        $tachesStats = [
            'total'    => count($taches),
            'termines' => count(array_filter($taches, fn($t) => $t['statut'] === 'termine')),
            'en_cours' => count(array_filter($taches, fn($t) => $t['statut'] !== 'termine')),
        ];
        $demandesStats = [
            'total'    => count($demandes),
            'acceptes' => count(array_filter($demandes, fn($d) => $d['statut'] === 'accepte')),
            'refuses'  => count(array_filter($demandes, fn($d) => $d['statut'] === 'refuse')),
            'attente'  => count(array_filter($demandes, fn($d) => $d['statut'] === 'en_attente')),
        ];

        require_once __DIR__ . '/../views/admin/dashboard.php';
    }

    // ════════════════════════════════════════════════════════
    //  ÉVÉNEMENTS
    // ════════════════════════════════════════════════════════
    public function ajouterEvenement(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?page=admin_dashboard&tab=events'); exit;
        }
        $data = [
            'titre'            => trim($_POST['titre'] ?? ''),
            'description'      => trim($_POST['description'] ?? ''),
            'date_evenement'   => $_POST['date_evenement'] ?? '',
            'heure'            => $_POST['heure'] ?? null,
            'lieu'             => trim($_POST['lieu'] ?? ''),
            'type'             => $_POST['type'] ?? 'public',
            'max_participants' => (int)($_POST['max_participants'] ?? 30),
            'id_createur'      => $_SESSION['user']['id'],
        ];
        if (empty($data['titre']) || empty($data['date_evenement'])) {
            $_SESSION['flash_error'] = 'Titre et date sont obligatoires.';
        } else {
            $this->evenementModel->ajouter($data);
            $_SESSION['flash_success'] = 'Événement ajouté avec succès !';
        }
        header('Location: index.php?page=admin_dashboard&tab=events'); exit;
    }

    public function modifierEvenement(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?page=admin_dashboard&tab=events'); exit;
        }
        $id   = (int)($_POST['id'] ?? 0);
        $data = [
            'titre'            => trim($_POST['titre'] ?? ''),
            'description'      => trim($_POST['description'] ?? ''),
            'date_evenement'   => $_POST['date_evenement'] ?? '',
            'heure'            => $_POST['heure'] ?? null,
            'lieu'             => trim($_POST['lieu'] ?? ''),
            'type'             => $_POST['type'] ?? 'public',
            'max_participants' => (int)($_POST['max_participants'] ?? 30),
        ];
        if (!$id || empty($data['titre'])) {
            $_SESSION['flash_error'] = 'Données invalides.';
        } else {
            $this->evenementModel->modifier($id, $data);
            $_SESSION['flash_success'] = 'Événement modifié.';
        }
        header('Location: index.php?page=admin_dashboard&tab=events'); exit;
    }

    public function supprimerEvenement(): void
    {
        $id = (int)($_GET['id'] ?? 0);
        if ($id) {
            $this->evenementModel->supprimer($id);
            $_SESSION['flash_success'] = 'Événement supprimé.';
        }
        header('Location: index.php?page=admin_dashboard&tab=events'); exit;
    }

    // ════════════════════════════════════════════════════════
    //  RÉUNIONS (avec lien_meet)
    // ════════════════════════════════════════════════════════
    public function ajouterReunion(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?page=admin_dashboard&tab=meetings'); exit;
        }
        $data = [
            'titre'         => trim($_POST['titre'] ?? ''),
            'date_reunion'  => $_POST['date_reunion'] ?? '',
            'heure'         => $_POST['heure'] ?? null,
            'lieu'          => trim($_POST['lieu'] ?? ''),
            'ordre_du_jour' => trim($_POST['ordre_du_jour'] ?? ''),
            'lien_meet'     => trim($_POST['lien_meet'] ?? '') ?: null,
            'type'          => $_POST['type'] ?? 'bureau',
            'id_createur'   => $_SESSION['user']['id'],
        ];
        if (empty($data['titre']) || empty($data['date_reunion'])) {
            $_SESSION['flash_error'] = 'Titre et date obligatoires.';
        } else {
            $this->reunionModel->ajouter($data);
            $_SESSION['flash_success'] = 'Réunion planifiée !';
        }
        header('Location: index.php?page=admin_dashboard&tab=meetings'); exit;
    }

    public function modifierReunion(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?page=admin_dashboard&tab=meetings'); exit;
        }
        $id   = (int)($_POST['id'] ?? 0);
        $data = [
            'titre'         => trim($_POST['titre'] ?? ''),
            'date_reunion'  => $_POST['date_reunion'] ?? '',
            'heure'         => $_POST['heure'] ?? null,
            'lieu'          => trim($_POST['lieu'] ?? ''),
            'ordre_du_jour' => trim($_POST['ordre_du_jour'] ?? ''),
            'lien_meet'     => trim($_POST['lien_meet'] ?? '') ?: null,
            'type'          => $_POST['type'] ?? 'bureau',
        ];
        if (!$id || empty($data['titre'])) {
            $_SESSION['flash_error'] = 'Données invalides.';
        } else {
            $this->reunionModel->modifier($id, $data);
            $_SESSION['flash_success'] = 'Réunion modifiée.';
        }
        header('Location: index.php?page=admin_dashboard&tab=meetings'); exit;
    }

    public function supprimerReunion(): void
    {
        $id = (int)($_GET['id'] ?? 0);
        if ($id) {
            $this->reunionModel->supprimer($id);
            $_SESSION['flash_success'] = 'Réunion supprimée.';
        }
        header('Location: index.php?page=admin_dashboard&tab=meetings'); exit;
    }

    // ════════════════════════════════════════════════════════
    //  PRÉSENCES
    // ════════════════════════════════════════════════════════
    public function gererPresences(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?page=admin_dashboard&tab=presences'); exit;
        }

        $idReunion = (int)($_POST['id_reunion'] ?? 0);
        if (!$idReunion) {
            header('Location: index.php?page=admin_dashboard&tab=presences'); exit;
        }

        $membres  = $this->utilisateurModel->listeMembres();
        $presents = array_map('intval', $_POST['presences'] ?? []);

        $this->presenceModel->sauvegarderPresences($idReunion, $membres, $presents);

        $_SESSION['flash_success'] = 'Liste de présence sauvegardée !';
        header("Location: index.php?page=admin_dashboard&tab=presences&reunion_id={$idReunion}");
        exit;
    }

    // ════════════════════════════════════════════════════════
    //  DEMANDES — FIX : utilise accepter() qui crée l'utilisateur
    // ════════════════════════════════════════════════════════
    public function traiterDemande(): void
    {
        $id     = (int)($_GET['id']     ?? 0);
        $action = $_GET['action'] ?? '';

        if (!$id || !in_array($action, ['accepte', 'refuse'])) {
            header('Location: index.php?page=admin_dashboard&tab=requests'); exit;
        }

        $demande = $this->demandeModel->getParId($id);

        if ($demande && $action === 'accepte') {
            $ok = $this->demandeModel->accepter($id);
            $_SESSION['flash_success'] = $ok
                ? "✅ {$demande['nom']} est maintenant membre du club et peut se connecter !"
                : "⚠️ Demande acceptée mais erreur lors de la création du compte.";
        } elseif ($demande && $action === 'refuse') {
            $this->demandeModel->refuser($id);
            $_SESSION['flash_success'] = "Demande de {$demande['nom']} refusée.";
        }

        header('Location: index.php?page=admin_dashboard&tab=requests'); exit;
    }

    // ════════════════════════════════════════════════════════
    //  MEMBRES — AJOUT : supprimerMembre()
    // ════════════════════════════════════════════════════════
    public function supprimerMembre(): void
    {
        $id = (int)($_GET['id'] ?? 0);

        // Sécurité : l'admin ne peut pas se supprimer lui-même
        if ($id && $id !== (int)$_SESSION['user']['id']) {
            $this->utilisateurModel->supprimer($id);
            $_SESSION['flash_success'] = 'Membre supprimé définitivement.';
        } else {
            $_SESSION['flash_error'] = 'Impossible de supprimer ce compte.';
        }
        header('Location: index.php?page=admin_dashboard&tab=members'); exit;
    }

    // ════════════════════════════════════════════════════════
    //  TÂCHES
    // ════════════════════════════════════════════════════════
    public function ajouterTache(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?page=admin_dashboard&tab=tasks'); exit;
        }
        $data = [
            'titre'       => trim($_POST['titre'] ?? ''),
            'id_assigne'  => (int)($_POST['id_assigne'] ?? 0),
            'deadline'    => $_POST['deadline'] ?? null,
            'priorite'    => $_POST['priorite'] ?? 'moyenne',
            'id_createur' => $_SESSION['user']['id'],
        ];
        if (empty($data['titre'])) {
            $_SESSION['flash_error'] = 'Titre de tâche obligatoire.';
        } else {
            $this->tacheModel->ajouter($data);
            $_SESSION['flash_success'] = 'Tâche assignée !';
        }
        header('Location: index.php?page=admin_dashboard&tab=tasks'); exit;
    }

    public function toggleTache(): void
    {
        $id = (int)($_GET['id'] ?? 0);
        if ($id) { $this->tacheModel->toggleStatut($id); }
        header('Location: index.php?page=admin_dashboard&tab=tasks'); exit;
    }

    public function supprimerTache(): void
    {
        $id = (int)($_GET['id'] ?? 0);
        if ($id) {
            $this->tacheModel->supprimer($id);
            $_SESSION['flash_success'] = 'Tâche supprimée.';
        }
        header('Location: index.php?page=admin_dashboard&tab=tasks'); exit;
    }
}