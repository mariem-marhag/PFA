<?php
/**
 * models/ReunionModel.php
 * Gestion des réunions — PDO + POO
 * Mise à jour : colonne lien_meet ajoutée
 */

require_once _DIR_ . '/../config/database.php';

class ReunionModel
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getInstance();
    }

    // ── Toutes les réunions ──────────────────────────────────
    public function getTous(): array
    {
        $stmt = $this->pdo->prepare(
            "SELECT r.*, u.nom AS createur_nom
             FROM reunions r
             LEFT JOIN utilisateurs u ON r.id_createur = u.id
             ORDER BY r.date_reunion ASC"
        );
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // ── Une réunion par ID ───────────────────────────────────
    public function getParId(int $id): array|false
    {
        $stmt = $this->pdo->prepare(
            "SELECT r.*, u.nom AS createur_nom
             FROM reunions r
             LEFT JOIN utilisateurs u ON r.id_createur = u.id
             WHERE r.id = :id LIMIT 1"
        );
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    // ── Compter les réunions ─────────────────────────────────
    public function compter(): int
    {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) AS total FROM reunions");
        $stmt->execute();
        return (int) $stmt->fetch()['total'];
    }

    // ── Réunions à venir ─────────────────────────────────────
    public function getAVenir(): array
    {
        $stmt = $this->pdo->prepare(
            "SELECT r.*, u.nom AS createur_nom
             FROM reunions r
             LEFT JOIN utilisateurs u ON r.id_createur = u.id
             WHERE r.date_reunion >= CURDATE()
             ORDER BY r.date_reunion ASC"
        );
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // ── Ajouter une réunion ──────────────────────────────────
    public function ajouter(array $data): bool
    {
        $stmt = $this->pdo->prepare(
            "INSERT INTO reunions
                (titre, date_reunion, heure, lieu, ordre_du_jour, lien_meet, type, id_createur)
             VALUES
                (:titre, :date, :heure, :lieu, :odj, :lien, :type, :createur)"
        );
        return $stmt->execute([
            ':titre'    => $data['titre'],
            ':date'     => $data['date_reunion'],
            ':heure'    => $data['heure'] ?? null,
            ':lieu'     => $data['lieu'] ?? '',
            ':odj'      => $data['ordre_du_jour'] ?? '',
            ':lien'     => $data['lien_meet'] ?? null,
            ':type'     => $data['type'] ?? 'bureau',
            ':createur' => $data['id_createur'] ?? null,
        ]);
    }

    // ── Modifier une réunion ─────────────────────────────────
    public function modifier(int $id, array $data): bool
    {
        $stmt = $this->pdo->prepare(
            "UPDATE reunions
             SET titre         = :titre,
                 date_reunion  = :date,
                 heure         = :heure,
                 lieu          = :lieu,
                 ordre_du_jour = :odj,
                 lien_meet     = :lien,
                 type          = :type
             WHERE id = :id"
        );
        return $stmt->execute([
            ':titre' => $data['titre'],
            ':date'  => $data['date_reunion'],
            ':heure' => $data['heure'] ?? null,
            ':lieu'  => $data['lieu'] ?? '',
            ':odj'   => $data['ordre_du_jour'] ?? '',
            ':lien'  => $data['lien_meet'] ?? null,
            ':type'  => $data['type'] ?? 'bureau',
            ':id'    => $id,
        ]);
    }

    // ── Supprimer une réunion ────────────────────────────────
    public function supprimer(int $id): bool
    {
        $stmt = $this->pdo->prepare("DELETE FROM reunions WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    // ── Réunions par type ────────────────────────────────────
    public function getParType(string $type): array
    {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM reunions WHERE type = :type ORDER BY date_reunion ASC"
        );
        $stmt->execute([':type' => $type]);
        return $stmt->fetchAll();
    }
}