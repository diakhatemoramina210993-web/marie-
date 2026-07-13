<?php
require_once __DIR__ . '/session.php';
ensure_session_started();

function require_citizen(string $redirectTo = ''): void
{
    if (empty($_SESSION['citoyen_id'])) {
        $target = $redirectTo !== '' ? '?redirect=' . urlencode($redirectTo) : '';
        header('Location: /cahier-lumineux-php/compte/inscription.php' . $target);
        exit;
    }
}

function current_citoyen(PDO $pdo): ?array
{
    if (empty($_SESSION['citoyen_id'])) {
        return null;
    }
    $stmt = $pdo->prepare("SELECT * FROM citoyens WHERE id = :id");
    $stmt->execute(['id' => $_SESSION['citoyen_id']]);
    $citoyen = $stmt->fetch();
    return $citoyen ?: null;
}
