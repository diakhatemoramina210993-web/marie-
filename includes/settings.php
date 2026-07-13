<?php
/**
 * Paramètres du site éditables depuis l'espace admin (contact, réseaux
 * sociaux, textes de l'accueil...). Chargés une seule fois par requête.
 */

function get_all_settings(PDO $pdo): array
{
    static $cache = null;
    if ($cache === null) {
        $cache = [];
        $rows = $pdo->query("SELECT setting_key, setting_value FROM site_settings")->fetchAll();
        foreach ($rows as $row) {
            $cache[$row['setting_key']] = $row['setting_value'];
        }
    }
    return $cache;
}

function get_setting(PDO $pdo, string $key, string $default = ''): string
{
    $all = get_all_settings($pdo);
    return $all[$key] ?? $default;
}
