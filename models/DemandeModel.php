<?php
/**
 * models/DemandeModel.php
 * CORRECTIONS : __DIR__ (était _DIR_), accepter() crée l'utilisateur
 */

require_once __DIR__ . '/../config/database.php';

class DemandeModel
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getInstance();
    }

    public function getTous(): array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM demandes_adhesion ORDER BY date_demande DESC");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getEnAttente(): array
    {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM demandes_adhesion WHERE statut = 'en_attente' ORDER BY date_demande DESC"
        );
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function compterEnAttente(): int
    {
        $stmt = $this->pdo->prepare(
            "SELECT COUNT(*) AS total FROM demandes_adhesion WHERE statut = 'en_attente'"
        );
        $stmt->execute();
        return (int) $stmt->fetch()['total'];
    }

    public function getParId(int $id): array|false
    {
        $stmt = $this->pdo->prepare("SELECT * FROM demandes_adhesion WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    public function creer(string $nom, string $email,
                          string $telephone = '', string $message = '',
                          string $motDePasse = ''): bool
    {
        $hash = !empty($motDePasse) ? password_hash($motDePasse, PASSWORD_BCRYPT) : null;
        $stmt = $this->pdo->prepare(
            "INSERT INTO demandes_adhesion (nom, email, telephone, message, mot_de_passe, statut)
             VALUES (:nom, :email, :tel, :msg, :mdp, 'en_attente')"
        );
        return $stmt->execute([
            ':nom'   => $nom,
            ':email' => $email,
            ':tel'   => $telephone,
            ':msg'   => $message,
            ':mdp'   => $hash,
        ]);
    }

    // FIX MAJEUR : accepter() crée l'utilisateur dans la table utilisateurs
    public function accepter(int $id): bool
    {
        $demande = $this->getParId($id);
        if (!$demande) return false;

        $this->pdo->beginTransaction();
        try {
            // 1. Mettre à jour le statut de la demande
            $s1 = $this->pdo->prepare(
                "UPDATE demandes_adhesion SET statut = 'accepte' WHERE id = :id"
            );
            $s1->execute([':id' => $id]);

            // 2. Vérifier si l'email existe déjà dans utilisateurs
            $sCheck = $this->pdo->prepare(
                "SELECT id FROM utilisateurs WHERE email = :email LIMIT 1"
            );
            $sCheck->execute([':email' => $demande['email']]);
            $existant = $sCheck->fetch();

            if (!$existant) {
                // 3. Créer le compte utilisateur avec le mot de passe de la demande
                $mdp = !empty($demande['mot_de_passe'])
                    ? $demande['mot_de_passe']
                    : password_hash('joker2024', PASSWORD_BCRYPT);

                $s2 = $this->pdo->prepare(
                    "INSERT INTO utilisateurs
                        (nom, email, mot_de_passe, role, statut, telephone, date_inscription)
                     VALUES (:nom, :email, :mdp, 'membre', 'actif', :tel, CURDATE())"
                );
                $s2->execute([
                    ':nom'   => $demande['nom'],
                    ':email' => $demande['email'],
                    ':mdp'   => $mdp,
                    ':tel'   => $demande['telephone'] ?? '',
                ]);
            } else {
                // Réactiver si compte existant désactivé
                $sUpd = $this->pdo->prepare(
                    "UPDATE utilisateurs SET statut = 'actif' WHERE email = :email"
                );
                $sUpd->execute([':email' => $demande['email']]);
            }

            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            return false;
        }
    }

    public function refuser(int $id): bool
    {
        $stmt = $this->pdo->prepare(
            "UPDATE demandes_adhesion SET statut = 'refuse' WHERE id = :id"
        );
        return $stmt->execute([':id' => $id]);
    }

    public function changerStatut(int $id, string $statut): bool
    {
        if (!in_array($statut, ['en_attente', 'accepte', 'refuse'])) return false;
        $stmt = $this->pdo->prepare(
            "UPDATE demandes_adhesion SET statut = :statut WHERE id = :id"
        );
        return $stmt->execute([':statut' => $statut, ':id' => $id]);
    }

    public function supprimer(int $id): bool
    {
        $stmt = $this->pdo->prepare("DELETE FROM demandes_adhesion WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    public function stats(): array
    {
        $stmt = $this->pdo->prepare(
            "SELECT statut, COUNT(*) AS total FROM demandes_adhesion GROUP BY statut"
        );
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // Nombre total de demandes = visiteurs ayant voulu rejoindre
    public function compterVisiteurs(): int
    {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) AS total FROM demandes_adhesion");
        $stmt->execute();
        return (int) $stmt->fetch()['total'];
    }

    // Statistiques par mois (pour les rapports)
    public function statsParMois(): array
    {
        $stmt = $this->pdo->prepare(
            "SELECT DATE_FORMAT(date_demande, '%Y-%m') AS mois, COUNT(*) AS total
             FROM demandes_adhesion
             GROUP BY mois ORDER BY mois DESC LIMIT 6"
        );
        $stmt->execute();
        return $stmt->fetchAll();
    }
}