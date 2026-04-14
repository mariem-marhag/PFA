<?php
/**
 * config/database.php
 * Connexion PDO — Singleton
 * Club Joker
 */

class Database
{
    // ── Paramètres de connexion ──────────────────────────────
    private static string $host     = 'localhost';
    private static string $dbname   = 'joker_club';
    private static string $user     = 'root';
    private static string $password = '';          // Modifier selon votre XAMPP/WAMP
    private static string $charset  = 'utf8mb4';

    private static ?PDO $instance = null;

    // Constructeur privé → empêche l'instanciation directe
    private function __construct() {}

    /**
     * Retourne l'unique instance PDO (Singleton)
     */
    public static function getInstance(): PDO
    {
        if (self::$instance === null) {
            $dsn = "mysql:host=" . self::$host
                 . ";dbname=" . self::$dbname
                 . ";charset=" . self::$charset;

            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];

            try {
                self::$instance = new PDO($dsn, self::$user, self::$password, $options);
            } catch (PDOException $e) {
                // En production, ne jamais afficher le message d'erreur brut
                die(json_encode([
                    'error' => 'Connexion à la base de données échouée.',
                    'detail' => $e->getMessage()
                ]));
            }
        }

        return self::$instance;
    }
}