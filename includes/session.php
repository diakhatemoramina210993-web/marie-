<?php
/**
 * Démarre la session PHP avec des paramètres de cookie explicites, pour
 * maximiser la compatibilité entre navigateurs (certains navigateurs/
 * extensions se montrent stricts si les attributs ne sont pas explicites).
 * Centralisé ici et utilisé par tous les points d'entrée de session
 * (admin, citoyen, CSRF) pour garantir une configuration cohérente.
 */
function ensure_session_started(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'domain' => '',
        'secure' => false,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}
