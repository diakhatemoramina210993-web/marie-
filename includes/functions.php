<?php
/**
 * Fonctions utilitaires partagées : code de suivi, envoi de notifications,
 * libellés de statut, upload sécurisé de pièce jointe.
 */

/**
 * Formate une date en français ("12 Juillet 2026") sans dépendre de l'extension intl.
 */
function format_date_fr(string $ymd): string
{
    $mois = [1 => 'Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'];
    $d = DateTime::createFromFormat('Y-m-d', $ymd);
    if (!$d) {
        return $ymd;
    }
    return $d->format('d') . ' ' . $mois[(int) $d->format('n')] . ' ' . $d->format('Y');
}

/**
 * Normalise un numéro sénégalais saisi sous diverses formes ("77 123 45 67",
 * "0771234567", "+221771234567"...) vers le format international sans
 * séparateurs attendu par les liens wa.me (ex: "221771234567").
 */
function normalize_senegal_phone(string $raw): string
{
    $digits = preg_replace('/\D+/', '', $raw);
    if (strpos($digits, '221') === 0) {
        return $digits;
    }
    if (strlen($digits) === 10 && $digits[0] === '0') {
        return '221' . substr($digits, 1);
    }
    if (strlen($digits) === 9) {
        return '221' . $digits;
    }
    return $digits;
}

function icon_for_tag(string $tag): string
{
    return [
        'Événement' => 'calendar',
        'Communiqué' => 'megaphone',
        'Projet' => 'rocket',
    ][$tag] ?? 'newspaper';
}

/**
 * Photo d'illustration thématique (mots-clés) au lieu d'une photo aléatoire
 * sans rapport avec le sujet. Le paramètre lock rend l'image stable (la même
 * image est retournée à chaque affichage pour un même "seed").
 */
function themed_illustration_url(string $keywords, $seed, int $w = 800, int $h = 500): string
{
    $lock = is_numeric($seed) ? (int) $seed : (crc32((string) $seed) % 100000);
    return "https://loremflickr.com/{$w}/{$h}/" . rawurlencode($keywords) . "?lock={$lock}";
}

/**
 * URL d'affichage de la photo d'une actualité : la vraie photo si elle a été
 * téléversée par l'administrateur, sinon une photo d'illustration thématique
 * (choisie selon la catégorie) en attendant la vraie photo.
 */
function actualite_image_url(array $actualite): string
{
    if (!empty($actualite['image'])) {
        return '/cahier-lumineux-php/assets/img/actualites/' . rawurlencode($actualite['image']);
    }
    $keywords = [
        'Événement'  => 'community,celebration,africa',
        'Communiqué' => 'government,africa',
        'Projet'     => 'construction,development,africa',
    ][$actualite['tag']] ?? 'senegal,village';
    return themed_illustration_url($keywords, $actualite['id']);
}

function generate_tracking_code(string $prefix): string
{
    return strtoupper($prefix) . '-' . date('ym') . '-' . strtoupper(bin2hex(random_bytes(3)));
}

/**
 * Envoie un e-mail (best effort) et journalise systématiquement dans
 * data/emails.log — utile en environnement local où aucun serveur SMTP
 * n'est configuré (mail() échoue alors silencieusement).
 */
function send_notification(string $to, string $subject, string $bodyHtml): bool
{
    require_once __DIR__ . '/mail-config.php';

    $sent = false;
    $smtpError = null;

    if (SMTP_USERNAME !== '' && SMTP_PASSWORD !== '') {
        require_once __DIR__ . '/PHPMailer/Exception.php';
        require_once __DIR__ . '/PHPMailer/PHPMailer.php';
        require_once __DIR__ . '/PHPMailer/SMTP.php';

        $mail = new PHPMailer\PHPMailer\PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = SMTP_HOST;
            $mail->SMTPAuth = true;
            $mail->Username = SMTP_USERNAME;
            $mail->Password = SMTP_PASSWORD;
            $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = SMTP_PORT;
            $mail->CharSet = 'UTF-8';

            $mail->setFrom(SMTP_USERNAME, SMTP_FROM_NAME);
            $mail->addAddress($to);
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $bodyHtml;
            $mail->AltBody = trim(strip_tags(str_replace(['<br>', '<br/>', '<br />', '</p>'], "\n", $bodyHtml)));

            $mail->send();
            $sent = true;
        } catch (\Throwable $e) {
            $smtpError = $mail->ErrorInfo ?: $e->getMessage();
        }
    } elseif (function_exists('mail')) {
        $headers  = "MIME-Version: 1.0\r\n";
        $headers .= "Content-type: text/html; charset=UTF-8\r\n";
        $headers .= "From: Mairie de Chérif Lo <" . ADMIN_EMAIL . ">\r\n";
        $sent = @mail($to, '=?UTF-8?B?' . base64_encode($subject) . '?=', $bodyHtml, $headers);
    }

    $logDir = __DIR__ . '/../data';
    if (!is_dir($logDir)) {
        @mkdir($logDir, 0777, true);
    }
    $statutEnvoi = 'oui';
    if (!$sent) {
        $statutEnvoi = $smtpError ? "non (erreur SMTP : $smtpError)" : 'non (SMTP non configuré — voir includes/mail-config.php)';
    }
    $line = sprintf(
        "[%s] À: %-30s Sujet: %-45s Envoyé: %s\n",
        date('Y-m-d H:i:s'),
        $to,
        $subject,
        $statutEnvoi
    );
    @file_put_contents($logDir . '/emails.log', $line, FILE_APPEND | LOCK_EX);
    @file_put_contents(
        $logDir . '/emails.log',
        "    --- contenu ---\n    " . strip_tags(str_replace(['<br>', '<br/>', '<br />', '</p>'], "\n    ", $bodyHtml)) . "\n\n",
        FILE_APPEND | LOCK_EX
    );

    return $sent;
}

/**
 * Envoi de SMS vers la mairie. Aucune passerelle SMS payante (Twilio, Orange
 * Sénégal, etc.) n'est configurée par défaut : le message est journalisé dans
 * data/sms.log. Pour activer l'envoi réel, brancher l'API du fournisseur ici
 * (ex: appel HTTP vers son endpoint) et faire retourner true en cas de succès.
 */
function send_sms(string $to, string $message): bool
{
    $sent = false; // brancher ici un vrai fournisseur SMS quand des identifiants seront disponibles

    $logDir = __DIR__ . '/../data';
    if (!is_dir($logDir)) {
        @mkdir($logDir, 0777, true);
    }
    $line = sprintf(
        "[%s] SMS -> %-15s Envoyé: %s\n    %s\n\n",
        date('Y-m-d H:i:s'),
        $to,
        $sent ? 'oui' : 'non (aucune passerelle SMS configurée — voir data/sms.log)',
        $message
    );
    @file_put_contents($logDir . '/sms.log', $line, FILE_APPEND | LOCK_EX);

    return $sent;
}

/**
 * Construit un lien "click-to-chat" WhatsApp pré-rempli vers un numéro donné.
 * L'envoi automatique serveur->WhatsApp nécessite l'API WhatsApp Business de
 * Meta (compte professionnel vérifié) ; en attendant, ce lien permet un envoi
 * en un clic et la tentative est journalisée dans data/whatsapp.log.
 */
function whatsapp_link(string $phoneInternational, string $message): string
{
    return 'https://wa.me/' . $phoneInternational . '?text=' . rawurlencode($message);
}

function log_whatsapp_attempt(string $to, string $message): void
{
    $logDir = __DIR__ . '/../data';
    if (!is_dir($logDir)) {
        @mkdir($logDir, 0777, true);
    }
    $line = sprintf(
        "[%s] WhatsApp -> %-15s (lien click-to-chat généré, API Business non configurée)\n    %s\n\n",
        date('Y-m-d H:i:s'),
        $to,
        $message
    );
    @file_put_contents($logDir . '/whatsapp.log', $line, FILE_APPEND | LOCK_EX);
}

function statut_demande_label(string $statut): string
{
    return [
        'recue'    => 'Reçue',
        'en_cours' => 'En cours de traitement',
        'prete'    => 'Prête (à retirer / envoyée)',
        'rejetee'  => 'Rejetée',
    ][$statut] ?? $statut;
}

function statut_demande_badge_class(string $statut): string
{
    return [
        'recue'    => 'bg-secondary text-secondary-foreground',
        'en_cours' => 'bg-accent/20 text-primary-deep',
        'prete'    => 'bg-primary/15 text-primary',
        'rejetee'  => 'bg-destructive/10 text-destructive',
    ][$statut] ?? 'bg-secondary text-secondary-foreground';
}

function statut_rdv_label(string $statut): string
{
    return [
        'en_attente' => 'En attente de confirmation',
        'confirme'   => 'Confirmé',
        'annule'     => 'Annulé',
    ][$statut] ?? $statut;
}

function statut_rdv_badge_class(string $statut): string
{
    return [
        'en_attente' => 'bg-secondary text-secondary-foreground',
        'confirme'   => 'bg-primary/15 text-primary',
        'annule'     => 'bg-destructive/10 text-destructive',
    ][$statut] ?? 'bg-secondary text-secondary-foreground';
}

/**
 * Déplace un upload (optionnel) vers data/uploads avec un nom assaini.
 * Retourne le nom de fichier stocké, ou null si aucun fichier valide fourni.
 */
function handle_upload(string $fieldName, string $trackingCode, array &$errors): ?string
{
    if (empty($_FILES[$fieldName]) || $_FILES[$fieldName]['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }
    $file = $_FILES[$fieldName];
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $errors[] = "Échec de l'envoi de la pièce jointe.";
        return null;
    }
    if ($file['size'] > 5 * 1024 * 1024) {
        $errors[] = "La pièce jointe dépasse la taille maximale autorisée (5 Mo).";
        return null;
    }
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed = ['pdf', 'jpg', 'jpeg', 'png'];
    if (!in_array($ext, $allowed, true)) {
        $errors[] = "Format de pièce jointe non autorisé (pdf, jpg, jpeg, png uniquement).";
        return null;
    }

    $uploadDir = __DIR__ . '/../data/uploads';
    if (!is_dir($uploadDir)) {
        @mkdir($uploadDir, 0777, true);
    }
    $storedName = $trackingCode . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
    if (!move_uploaded_file($file['tmp_name'], $uploadDir . '/' . $storedName)) {
        $errors[] = "Impossible d'enregistrer la pièce jointe.";
        return null;
    }
    return $storedName;
}

/**
 * Déplace une photo d'actualité (upload admin) vers assets/img/actualites,
 * dossier public puisque ces images sont destinées à être affichées sur le site.
 * Retourne le nom de fichier stocké, ou null si aucun fichier valide fourni.
 */
function handle_actualite_image(string $fieldName, array &$errors): ?string
{
    if (empty($_FILES[$fieldName]) || $_FILES[$fieldName]['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }
    $file = $_FILES[$fieldName];
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $errors[] = "Échec de l'envoi de la photo.";
        return null;
    }
    if ($file['size'] > 6 * 1024 * 1024) {
        $errors[] = "La photo dépasse la taille maximale autorisée (6 Mo).";
        return null;
    }
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'webp'];
    if (!in_array($ext, $allowed, true)) {
        $errors[] = "Format de photo non autorisé (jpg, jpeg, png, webp uniquement).";
        return null;
    }
    if (!@getimagesize($file['tmp_name'])) {
        $errors[] = "Le fichier envoyé n'est pas une image valide.";
        return null;
    }

    $uploadDir = __DIR__ . '/../assets/img/actualites';
    if (!is_dir($uploadDir)) {
        @mkdir($uploadDir, 0777, true);
    }
    $storedName = 'actu-' . date('ymd-His') . '-' . bin2hex(random_bytes(3)) . '.' . $ext;
    if (!move_uploaded_file($file['tmp_name'], $uploadDir . '/' . $storedName)) {
        $errors[] = "Impossible d'enregistrer la photo.";
        return null;
    }
    return $storedName;
}

/**
 * Déplace une photo de médiathèque (upload admin) vers assets/img/medias.
 * Retourne le nom de fichier stocké, ou null si aucun fichier valide fourni.
 */
function handle_media_photo(string $fieldName, array &$errors): ?string
{
    if (empty($_FILES[$fieldName]) || $_FILES[$fieldName]['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }
    $file = $_FILES[$fieldName];
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $errors[] = "Échec de l'envoi de la photo.";
        return null;
    }
    if ($file['size'] > 6 * 1024 * 1024) {
        $errors[] = "La photo dépasse la taille maximale autorisée (6 Mo).";
        return null;
    }
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'webp'];
    if (!in_array($ext, $allowed, true)) {
        $errors[] = "Format de photo non autorisé (jpg, jpeg, png, webp uniquement).";
        return null;
    }
    if (!@getimagesize($file['tmp_name'])) {
        $errors[] = "Le fichier envoyé n'est pas une image valide.";
        return null;
    }

    $uploadDir = __DIR__ . '/../assets/img/medias';
    if (!is_dir($uploadDir)) {
        @mkdir($uploadDir, 0777, true);
    }
    $storedName = 'photo-' . date('ymd-His') . '-' . bin2hex(random_bytes(3)) . '.' . $ext;
    if (!move_uploaded_file($file['tmp_name'], $uploadDir . '/' . $storedName)) {
        $errors[] = "Impossible d'enregistrer la photo.";
        return null;
    }
    return $storedName;
}

/**
 * Déplace un fichier vidéo de médiathèque (upload admin) vers assets/img/medias.
 * Retourne le nom de fichier stocké, ou null si aucun fichier valide fourni.
 */
function handle_media_video(string $fieldName, array &$errors): ?string
{
    if (empty($_FILES[$fieldName]) || $_FILES[$fieldName]['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }
    $file = $_FILES[$fieldName];
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $errors[] = "Échec de l'envoi de la vidéo.";
        return null;
    }
    if ($file['size'] > 35 * 1024 * 1024) {
        $errors[] = "La vidéo dépasse la taille maximale autorisée (35 Mo). Utilisez plutôt un lien vidéo pour les fichiers plus lourds.";
        return null;
    }
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed = ['mp4', 'webm', 'mov'];
    if (!in_array($ext, $allowed, true)) {
        $errors[] = "Format de vidéo non autorisé (mp4, webm, mov uniquement).";
        return null;
    }

    $uploadDir = __DIR__ . '/../assets/img/medias';
    if (!is_dir($uploadDir)) {
        @mkdir($uploadDir, 0777, true);
    }
    $storedName = 'video-' . date('ymd-His') . '-' . bin2hex(random_bytes(3)) . '.' . $ext;
    if (!move_uploaded_file($file['tmp_name'], $uploadDir . '/' . $storedName)) {
        $errors[] = "Impossible d'enregistrer la vidéo.";
        return null;
    }
    return $storedName;
}
