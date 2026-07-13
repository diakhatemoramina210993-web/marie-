<?php
require __DIR__ . '/includes/auth.php';
require_admin();

$file = basename($_GET['file'] ?? '');
$path = __DIR__ . '/../data/uploads/' . $file;

if ($file === '' || !is_file($path)) {
    http_response_code(404);
    die('Fichier introuvable.');
}

$ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
$mime = ['pdf' => 'application/pdf', 'jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png'][$ext] ?? 'application/octet-stream';

header('Content-Type: ' . $mime);
header('Content-Disposition: inline; filename="' . $file . '"');
header('Content-Length: ' . filesize($path));
readfile($path);
exit;
