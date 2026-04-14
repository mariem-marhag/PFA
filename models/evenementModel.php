<?php
/**
 * models/EvenementModel.php
 * Gestion des événements — PDO + POO
 */

require_once __DIR__ . '/../config/database.php';

class EvenementModel
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getInstance();
    }

    // ── Tous les événements ──────────────────────────────────
    public function getTous(string $filtre = 'tous'): array
    {
        $sql = "SELECT e.*,
               u.nom AS createur_nom,
               (SELECT COUNT(*) FROM inscriptions_evenements i
                WHERE i.id_evenement = e.id) AS nb_inscrits
        FROM evenements e
        LEFT JOIN utilisateurs u ON e.id_createur = u.id
        WHERE 1=1";

        $params = [];

        if ($filtre === 'public') {
            $sql .= " AND e.type = :type";
            $params[':type'] = 'public';
        } elseif ($filtre === 'prive') {
            $sql .= " AND e.type = :type";
            $params[':type'] = 'prive';
        }

        $sql .= " ORDER BY e.date_evenement ASC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    // ── Événements publics (accueil) ─────────────────────────
    public function getPublics(int $limite = 3): array
{
    $stmt = $this->pdo->prepare(
        "SELECT e.*,
                u.nom AS createur_nom,
                (SELECT COUNT(*) FROM inscriptions_evenements i
                 WHERE i.id_evenement = e.id) AS nb_inscrits
         FROM evenements e
         LEFT JOIN utilisateurs u ON e.id_createur = u.id
         WHERE e.type = 'public'
         ORDER BY e.date_evenement DESC
         LIMIT :limite"
    );
    $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}

    // ── Un événement par ID ──────────────────────────────────
    public function getParId(int $id): array|false
    {
        $stmt = $this->pdo->prepare(
            "SELECT e.*,
                    u.nom AS createur_nom,
                    (SELECT COUNT(*) FROM inscriptions_evenements i
                    WHERE i.id_evenement = e.id) AS nb_inscrits
            FROM evenements e
            LEFT JOIN utilisateurs u ON e.id_createur = u.id
            WHERE e.id = :id LIMIT 1"
);
        
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    // ── Compter les événements ───────────────────────────────
    public function compter(): int
    {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) AS total FROM evenements");
        $stmt->execute();
        return (int) $stmt->fetch()['total'];
    }

    // ── Ajouter un événement ─────────────────────────────────
    public function ajouter(array $data): bool
    {
        $stmt = $this->pdo->prepare(
            "INSERT INTO evenements
                (titre, description, date_evenement, heure, lieu, type, max_participants, id_createur)
             VALUES
                (:titre, :desc, :date, :heure, :lieu, :type, :max, :createur)"
        );
        return $stmt->execute([
            ':titre'    => $data['titre'],
            ':desc'     => $data['description'] ?? '',
            ':date'     => $data['date_evenement'],
            ':heure'    => $data['heure'] ?? null,
            ':lieu'     => $data['lieu'] ?? '',
            ':type'     => $data['type'],
            ':max'      => (int) ($data['max_participants'] ?? 30),
            ':createur' => $data['id_createur'] ?? null,
        ]);
    }

    // ── Modifier un événement ────────────────────────────────
    public function modifier(int $id, array $data): bool
    {
        $stmt = $this->pdo->prepare(
            "UPDATE evenements
             SET titre = :titre,
                 description = :desc,
                 date_evenement = :date,
                 heure = :heure,
                 lieu = :lieu,
                 type = :type,
                 max_participants = :max
             WHERE id = :id"
        );
        return $stmt->execute([
            ':titre' => $data['titre'],
            ':desc'  => $data['description'] ?? '',
            ':date'  => $data['date_evenement'],
            ':heure' => $data['heure'] ?? null,
            ':lieu'  => $data['lieu'] ?? '',
            ':type'  => $data['type'],
            ':max'   => (int) ($data['max_participants'] ?? 30),
            ':id'    => $id,
        ]);
    }

    // ── Supprimer un événement ───────────────────────────────
    public function supprimer(int $id): bool
    {
        $stmt = $this->pdo->prepare("DELETE FROM evenements WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    // ── Inscrire un visiteur à un événement ──────────────────
    public function inscrire(int $idEvenement, string $nom, string $email,
                             string $telephone = '', ?int $idUtilisateur = null): bool
    {
        $stmt = $this->pdo->prepare(
            "INSERT INTO inscriptions_evenements
                (id_evenement, nom_visiteur, email_visiteur, telephone, id_utilisateur)
             VALUES (:evt, :nom, :email, :tel, :uid)"
        );
        return $stmt->execute([
            ':evt'   => $idEvenement,
            ':nom'   => $nom,
            ':email' => $email,
            ':tel'   => $telephone,
            ':uid'   => $idUtilisateur,
        ]);
    }

    // ── Vérifier si une place est disponible ─────────────────
    public function placesDisponibles(int $idEvenement): bool
    {
        $evt = $this->getParId($idEvenement);
        if (!$evt) return false;
        return (int) $evt['nb_inscrits'] < (int) $evt['max_participants'];
    }

    // ── Recherche par titre ou lieu ──────────────────────────
    public function rechercher(string $terme): array
    {
       $stmt = $this->pdo->prepare(
            "SELECT e.*,
                    u.nom AS createur_nom,
                    (SELECT COUNT(*) FROM inscriptions_evenements i
                    WHERE i.id_evenement = e.id) AS nb_inscrits
            FROM evenements e
            LEFT JOIN utilisateurs u ON e.id_createur = u.id
            WHERE e.titre LIKE :terme OR e.lieu LIKE :terme
            ORDER BY e.date_evenement ASC"
);
        $stmt->execute([':terme' => '%' . $terme . '%']);
        return $stmt->fetchAll();
    }

    // ── Statistiques par type ────────────────────────────────
    public function statsParType(): array
    {
        $stmt = $this->pdo->prepare(
            "SELECT type, COUNT(*) AS total
             FROM evenements
             GROUP BY type"
        );
        $stmt->execute();
        return $stmt->fetchAll();
    }
}