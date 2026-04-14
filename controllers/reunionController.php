<?php
/**
 * controllers/ReunionController.php
 * Page publique des réunions
 */

require_once __DIR__ . '/../models/ReunionModel.php';

class ReunionController
{
    private ReunionModel $reunionModel;

    public function __construct()
    {
        $this->reunionModel = new ReunionModel();
    }

    // ── Page publique des réunions ───────────────────────────
    public function afficherListe(): void
    {
        $reunions = $this->reunionModel->getAVenir();
        require_once __DIR__ . '/../views/public/reunions.php';
    }
}