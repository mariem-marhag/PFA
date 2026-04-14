<?php
/**
 * models/PresenceModel.php
 * CORRECTIONS : __DIR__ (était _DIR_), ajout initialiserPourReunion(),
 *               tauxPresenceGlobal() retourne un array (pour les rapports)
 */

require_once __DIR__ . '/../config/database.php';

class PresenceModel
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getInstance();
    }

    // ── Récupérer les présences d'une réunion (avec nom membre) ──
    public function getParReunion(int $idReunion): array
    {
        $stmt = $this->pdo->prepare(
            "SELECT u.id AS id_membre, u.nom, u.email,
                    COALESCE(p.statut, 'absent') AS statut
             FROM utilisateurs u
             LEFT JOIN presences p
               ON p.id_membre = u.id AND p.id_reunion = :id_reunion
             WHERE u.role = 'membre' AND u.statut = 'actif'
             ORDER BY u.nom ASC"
        );
        $stmt->execute([':id_reunion' => $idReunion]);
        return $stmt->fetchAll();
    }

    // ── Initialiser les présences (tous absents par défaut) ───────
    public function initialiserPourReunion(int $idReunion, array $membres): void
    {
        $stmt = $this->pdo->prepare(
            "INSERT IGNORE INTO presences (id_reunion, id_membre, statut)
             VALUES (:reunion, :membre, 'absent')"
        );
        foreach ($membres as $m) {
            $stmt->execute([':reunion' => $idReunion, ':membre' => $m['id']]);
        }
    }

    // ── Enregistrer ou mettre à jour une présence ─────────────────
    public function enregistrer(int $idReunion, int $idMembre, string $statut): bool
    {
        if (!in_array($statut, ['present', 'absent'])) return false;

        $stmt = $this->pdo->prepare(
            "INSERT INTO presences (id_reunion, id_membre, statut)
             VALUES (:id_reunion, :id_membre, :statut)
             ON DUPLICATE KEY UPDATE statut = :statut"
        );
        return $stmt->execute([
            ':id_reunion' => $idReunion,
            ':id_membre'  => $idMembre,
            ':statut'     => $statut,
        ]);
    }

    // ── Enregistrer toute la liste d'une réunion en une fois ──────
    public function enregistrerTout(int $idReunion, array $presences): bool
    {
        // $presences = [ id_membre => 'present'|'absent', ... ]
        foreach ($presences as $idMembre => $statut) {
            $this->enregistrer($idReunion, (int)$idMembre, $statut);
        }
        return true;
    }

    // ── Sauvegarder depuis un tableau de cochés (POST checkboxes) ─
    public function sauvegarderPresences(int $idReunion, array $membres, array $presents): bool
    {
        $stmt = $this->pdo->prepare(
            "INSERT INTO presences (id_reunion, id_membre, statut)
             VALUES (:reunion, :membre, :statut)
             ON DUPLICATE KEY UPDATE statut = :statut"
        );
        foreach ($membres as $m) {
            $statut = in_array((int)$m['id'], $presents) ? 'present' : 'absent';
            $stmt->execute([
                ':reunion' => $idReunion,
                ':membre'  => $m['id'],
                ':statut'  => $statut,
            ]);
        }
        return true;
    }

    // ── Stats présences pour une réunion ──────────────────────────
    public function statsReunion(int $idReunion): array
    {
        $stmt = $this->pdo->prepare(
            "SELECT statut, COUNT(*) AS total
             FROM presences
             WHERE id_reunion = :id_reunion
             GROUP BY statut"
        );
        $stmt->execute([':id_reunion' => $idReunion]);
        $rows   = $stmt->fetchAll();
        $result = ['present' => 0, 'absent' => 0];
        foreach ($rows as $r) {
            $result[$r['statut']] = (int)$r['total'];
        }
        return $result;
    }

    // ── Taux de présence global — retourne un ARRAY pour les rapports ──
    public function tauxPresenceGlobal(): array
    {
        $stmt = $this->pdo->prepare(
            "SELECT
                r.titre,
                r.date_reunion,
                COUNT(p.id) AS total,
                SUM(p.statut = 'present') AS presents
             FROM reunions r
             LEFT JOIN presences p ON p.id_reunion = r.id
             GROUP BY r.id
             ORDER BY r.date_reunion DESC
             LIMIT 10"
        );
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // ── Taux global en pourcentage (pour la stat card) ─────────────
    public function tauxGlobalPourcentage(): float
    {
        $stmt = $this->pdo->prepare(
            "SELECT SUM(statut = 'present') AS presents, COUNT(*) AS total FROM presences"
        );
        $stmt->execute();
        $r = $stmt->fetch();
        if (!$r || (int)$r['total'] === 0) return 0.0;
        return round((int)$r['presents'] / (int)$r['total'] * 100, 1);
    }
}