<?php
/**
 * models/TacheModel.php
 * Gestion des tâches — PDO + POO
 * Table taches + jointure utilisateurs
 */

require_once __DIR__ . '/../config/database.php';

class TacheModel
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getInstance();
    }

    // ── Toutes les tâches (avec nom de l'assigné) ────────────
    public function getTous(): array
    {
        $stmt = $this->pdo->prepare(
            "SELECT t.*, u.nom AS assigné_nom
             FROM taches t
             LEFT JOIN utilisateurs u ON t.id_assigne = u.id
             ORDER BY
               FIELD(t.priorite, 'haute', 'moyenne', 'faible'),
               t.deadline ASC"
        );
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // ── Tâches d'un membre spécifique ────────────────────────
    public function getTachesParMembre(int $idUtilisateur): array
    {
        $stmt = $this->pdo->prepare(
            "SELECT t.*, u.nom AS assigné_nom
             FROM taches t
             LEFT JOIN utilisateurs u ON t.id_assigne = u.id
             WHERE t.id_assigne = :uid
             ORDER BY FIELD(t.priorite, 'haute', 'moyenne', 'faible'), t.deadline ASC"
        );
        $stmt->execute([':uid' => $idUtilisateur]);
        return $stmt->fetchAll();
    }

    // ── Une tâche par ID ─────────────────────────────────────
    public function getParId(int $id): array|false
    {
        $stmt = $this->pdo->prepare(
            "SELECT t.*, u.nom AS assigné_nom
             FROM taches t
             LEFT JOIN utilisateurs u ON t.id_assigne = u.id
             WHERE t.id = :id LIMIT 1"
        );
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    // ── Ajouter une tâche ────────────────────────────────────
    public function ajouter(array $data): bool
    {
        $stmt = $this->pdo->prepare(
            "INSERT INTO taches (titre, id_assigne, deadline, priorite, statut)
             VALUES (:titre, :uid, :deadline, :priorite, 'en_cours')"
        );
        return $stmt->execute([
            ':titre'    => $data['titre'],
            ':uid'      => $data['id_assigne'] ?? null,
            ':deadline' => $data['deadline'] ?? null,
            ':priorite' => $data['priorite'] ?? 'moyenne',
        ]);
    }

    // ── Modifier une tâche ───────────────────────────────────
    public function modifier(int $id, array $data): bool
    {
        $stmt = $this->pdo->prepare(
            "UPDATE taches
             SET titre = :titre,
                 id_assigne = :uid,
                 deadline = :deadline,
                 priorite = :priorite
             WHERE id = :id"
        );
        return $stmt->execute([
            ':titre'    => $data['titre'],
            ':uid'      => $data['id_assigne'] ?? null,
            ':deadline' => $data['deadline'] ?? null,
            ':priorite' => $data['priorite'] ?? 'moyenne',
            ':id'       => $id,
        ]);
    }

    // ── Basculer le statut (en_cours ↔ termine) ──────────────
    public function toggleStatut(int $id): bool
    {
        $stmt = $this->pdo->prepare(
            "UPDATE taches
             SET statut = IF(statut = 'en_cours', 'termine', 'en_cours')
             WHERE id = :id"
        );
        return $stmt->execute([':id' => $id]);
    }

    // ── Supprimer une tâche ──────────────────────────────────
    public function supprimer(int $id): bool
    {
        $stmt = $this->pdo->prepare("DELETE FROM taches WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    // ── Statistiques tâches ──────────────────────────────────
    public function stats(): array
    {
        $stmt = $this->pdo->prepare(
            "SELECT statut, COUNT(*) AS total FROM taches GROUP BY statut"
        );
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // ── To-do list personnelle d'un membre ───────────────────
    // (stockée dans taches avec id_assigne = uid ET sans deadline ni priorité gérée par admin)
    public function getTodoMembre(int $idUtilisateur): array
    {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM taches
             WHERE id_assigne = :uid AND priorite = 'faible'
             ORDER BY id DESC"
        );
        $stmt->execute([':uid' => $idUtilisateur]);
        return $stmt->fetchAll();
    }

    // ── Ajouter item todo personnel ──────────────────────────
    public function ajouterTodo(int $idUtilisateur, string $titre): bool
    {
        $stmt = $this->pdo->prepare(
            "INSERT INTO taches (titre, id_assigne, priorite, statut)
             VALUES (:titre, :uid, 'faible', 'en_cours')"
        );
        return $stmt->execute([':titre' => $titre, ':uid' => $idUtilisateur]);
    }
}