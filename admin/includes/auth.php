<?php
require_once __DIR__ . '/../../includes/session.php';
ensure_session_started();

function require_admin(): void
{
    if (empty($_SESSION['admin_id'])) {
        header('Location: login.php');
        exit;
    }
}
