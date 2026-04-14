<?php
/**
 * controllers/AuthController.php
 * Gère la connexion et la déconnexion
 */

require_once __DIR__ . '/../models/UtilisateurModel.php';

class AuthController
{
    private UtilisateurModel $utilisateurModel;

    public function __construct()
    {
        $this->utilisateurModel = new UtilisateurModel();
    }

    // ── Afficher la page login ───────────────────────────────
    public function afficherLogin(): void
    {
        // Si déjà connecté → rediriger
        if (isset($_SESSION['user'])) {
            $role = $_SESSION['user']['role'];
            $this->redirigerSelonRole($role);
        }

        $erreur  = $_SESSION['flash_error']  ?? null;
        $succes  = $_SESSION['flash_success'] ?? null;
        unset($_SESSION['flash_error'], $_SESSION['flash_success']);

        require_once __DIR__ . '/../views/auth/login.php';
    }

    // ── Traiter le formulaire de connexion ───────────────────
    public function traiterLogin(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?page=login');
            exit;
        }

        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        // Validation basique
        if (empty($email) || empty($password)) {
            $_SESSION['flash_error'] = 'Email et mot de passe obligatoires.';
            header('Location: index.php?page=login');
            exit;
        }

        $utilisateur = $this->utilisateurModel->verifierConnexion($email, $password);

        if (!$utilisateur) {
            $_SESSION['flash_error'] = 'Email ou mot de passe incorrect.';
            header('Location: index.php?page=login');
            exit;
        }

        // Stocker en session (sans le mot de passe)
        $_SESSION['user'] = [
            'id'    => $utilisateur['id'],
            'nom'   => $utilisateur['nom'],
            'email' => $utilisateur['email'],
            'role'  => $utilisateur['role'],
        ];

        $_SESSION['flash_success'] = 'Bienvenue, ' . $utilisateur['nom'] . ' !';
        $this->redirigerSelonRole($utilisateur['role']);
    }

    // ── Déconnexion ──────────────────────────────────────────
    public function deconnecter(): void
    {
        session_destroy();
        header('Location: index.php?page=accueil');
        exit;
    }

    // ── Rediriger selon le rôle ──────────────────────────────
    private function redirigerSelonRole(string $role): void
    {
        if ($role === 'admin') {
            header('Location: index.php?page=admin_dashboard');
        } else {
            header('Location: index.php?page=membre_dashboard');
        }
        exit;
    }

    // ── Vérifier que l'utilisateur est connecté ──────────────
    public static function exigerConnexion(): void
    {
        if (!isset($_SESSION['user'])) {
            $_SESSION['flash_error'] = 'Veuillez vous connecter.';
            header('Location: index.php?page=login');
            exit;
        }
    }

    // ── Vérifier que c'est un admin ──────────────────────────
    public static function exigerAdmin(): void
    {
        self::exigerConnexion();
        if ($_SESSION['user']['role'] !== 'admin') {
            header('Location: index.php?page=accueil');
            exit;
        }
    }

    // ── Vérifier que c'est un membre ─────────────────────────
    public static function exigerMembre(): void
    {
        self::exigerConnexion();
        if (!in_array($_SESSION['user']['role'], ['membre', 'admin'])) {
            header('Location: index.php?page=accueil');
            exit;
        }
    }
}