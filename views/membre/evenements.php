<?php
/**
 * views/membre/evenements.php
 * Page événements vue depuis l'espace membre
 * Redirige vers le dashboard membre onglet events
 */

// Cette vue est un alias — on redirige vers le dashboard tab events
header('Location: index.php?page=membre_dashboard&tab=events');
exit;