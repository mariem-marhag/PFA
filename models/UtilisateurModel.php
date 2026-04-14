<?php
/**
 * models/UtilisateurModel.php
 * Gestion des utilisateurs — PDO + POO
 * Toutes les requêtes utilisent prepare() + execute()
 */

require_once __DIR__ . '/../config/database.php';

class UtilisateurModel
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getInstance();
    }

    // ── Récupérer tous les membres ───────────────────────────
    public function getTousMembres(): array
    {
        $stmt = $this->pdo->prepare(
            "SELECT id, nom, email, role, statut, date_inscription, telephone
             FROM utilisateurs
             ORDER BY date_inscription DESC"
        );
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // ── Compter les membres actifs ───────────────────────────
    public function compterMembres(): int
    {
        $stmt = $this->pdo->prepare(
            "SELECT COUNT(*) AS total
             FROM utilisateurs
             WHERE role = 'membre' AND statut = 'actif'"
        );
        $stmt->execute();
        $row = $stmt->fetch();
        return (int) $row['total'];
    }

    // ── Trouver un utilisateur par email ─────────────────────
    public function trouverParEmail(string $email): array|false
    {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM utilisateurs WHERE email = :email LIMIT 1"
        );
        $stmt->execute([':email' => $email]);
        return $stmt->fetch();
    }

    // ── Trouver un utilisateur par ID ────────────────────────
    public function trouverParId(int $id): array|false
    {
        $stmt = $this->pdo->prepare(
            "SELECT id, nom, email, role, statut, telephone, date_inscription
             FROM utilisateurs
             WHERE id = :id LIMIT 1"
        );
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

   // ── Créer un utilisateur avec hash déjà prêt ────────────
public function creerAvecHash(string $nom, string $email, string $hashMdp,
                               string $role = 'membre', string $telephone = ''): bool
{
    $stmt = $this->pdo->prepare(
        "INSERT INTO utilisateurs 
            (nom, email, mot_de_passe, role, statut, telephone, date_inscription)
         VALUES (:nom, :email, :mdp, :role, 'actif', :tel, CURDATE())"
    );
    return $stmt->execute([
        ':nom'   => $nom,
        ':email' => $email,
        ':mdp'   => $hashMdp,
        ':role'  => $role,
        ':tel'   => $telephone,
    ]);
}
    // ── Modifier un utilisateur ──────────────────────────────
    public function modifier(int $id, string $nom, string $email,
                             string $telephone, string $statut): bool
    {
        $stmt = $this->pdo->prepare(
            "UPDATE utilisateurs
             SET nom = :nom, email = :email, telephone = :tel, statut = :statut
             WHERE id = :id"
        );
        return $stmt->execute([
            ':nom'    => $nom,
            ':email'  => $email,
            ':tel'    => $telephone,
            ':statut' => $statut,
            ':id'     => $id,
        ]);
    }

    // ── Supprimer un utilisateur ─────────────────────────────
    public function supprimer(int $id): bool
    {
        $stmt = $this->pdo->prepare(
            "DELETE FROM utilisateurs WHERE id = :id"
        );
        return $stmt->execute([':id' => $id]);
    }

    // ── Vérifier les identifiants (connexion) ────────────────
    public function verifierConnexion(string $email, string $motDePasse): array|false
    {
        $utilisateur = $this->trouverParEmail($email);
        if (!$utilisateur) {
            return false;
        }
        if (!password_verify($motDePasse, $utilisateur['mot_de_passe'])) {
            return false;
        }
        if ($utilisateur['statut'] !== 'actif') {
            return false;
        }
        return $utilisateur;
    }

    // ── Liste des membres pour le sélecteur (assignation tâche) ──
    public function listeMembres(): array
    {
        $stmt = $this->pdo->prepare(
            "SELECT id, nom FROM utilisateurs
             WHERE role = 'membre' AND statut = 'actif'
             ORDER BY nom ASC"
        );
        $stmt->execute();
        return $stmt->fetchAll();
    }
}