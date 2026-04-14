<?php
/**
 * controllers/EvenementController.php
 * Gère les pages publiques d'événements + inscription visiteur
 */

require_once __DIR__ . '/../models/EvenementModel.php';
require_once __DIR__ . '/../controllers/AuthController.php';

class EvenementController
{
    private EvenementModel $evenementModel;

    public function __construct()
    {
        $this->evenementModel = new EvenementModel();
    }

    // ── Page liste des événements (public) ───────────────────
    public function afficherListe(): void
    {
        $filtre    = $_GET['filtre'] ?? 'tous';
        $recherche = trim($_GET['q'] ?? '');

        if (!empty($recherche)) {
            $evenements = $this->evenementModel->rechercher($recherche);
        } else {
            $evenements = $this->evenementModel->getTous($filtre);
        }

        $flash_success = $_SESSION['flash_success'] ?? null;
        $flash_error   = $_SESSION['flash_error']   ?? null;
        unset($_SESSION['flash_success'], $_SESSION['flash_error']);

        require_once __DIR__ . '/../views/public/evenements.php';
    }

    // ── Inscription visiteur à un événement ──────────────────
    public function inscrire(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?page=evenements');
            exit;
        }

        $idEvenement = (int) ($_POST['id_evenement'] ?? 0);
        $nom         = trim($_POST['nom']   ?? '');
        $email       = trim($_POST['email'] ?? '');
        $telephone   = trim($_POST['telephone'] ?? '');

        // Validation
        if (!$idEvenement || empty($nom) || empty($email)) {
            $_SESSION['flash_error'] = 'Nom et email sont obligatoires.';
            header('Location: index.php?page=evenements');
            exit;
        }

        // Vérifier places disponibles
        if (!$this->evenementModel->placesDisponibles($idEvenement)) {
            $_SESSION['flash_error'] = 'Désolé, cet événement est complet.';
            header('Location: index.php?page=evenements');
            exit;
        }

        // Récupérer l'ID utilisateur si connecté
        $idUtilisateur = $_SESSION['user']['id'] ?? null;

        $ok = $this->evenementModel->inscrire($idEvenement, $nom, $email, $telephone, $idUtilisateur);

        if ($ok) {
            $_SESSION['flash_success'] = "✅ Inscription confirmée pour $nom ! Vous recevrez un email de confirmation.";
        } else {
            $_SESSION['flash_error'] = "Une erreur est survenue. Veuillez réessayer.";
        }

        header('Location: index.php?page=evenements');
        exit;
    }
}