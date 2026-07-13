<?php
require __DIR__ . '/includes/csrf.php';
require __DIR__ . '/includes/db.php';
require __DIR__ . '/includes/functions.php';

$pageTitle = "Contact — Mairie de Chérif Lo";
$pageDescription = "Contactez la Mairie de Chérif Lo : adresse, téléphone, e-mail et formulaire de contact.";
$activePage = "contact.php";

$sent = false;
$errors = [];
$name = $email = $phone = $subject = $message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) {
        $errors[] = "Session expirée, merci de renvoyer le formulaire.";
    }
    if (!empty($_POST['site_web'])) {
        $errors[] = "Requête invalide.";
    }

    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if ($name === '') $errors[] = "Le nom complet est requis.";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "L'adresse e-mail est invalide.";
    if ($phone === '') $errors[] = "Le téléphone est requis.";
    if ($subject === '') $errors[] = "Le sujet est requis.";
    if ($message === '') $errors[] = "Le message est requis.";

    if (empty($errors)) {
        $phoneIntl = normalize_senegal_phone($phone);
        $stmt = $pdo->prepare("INSERT INTO messages_contact (nom, email, telephone, sujet, message) VALUES (:nom, :email, :telephone, :sujet, :message)");
        $stmt->execute(['nom' => $name, 'email' => $email, 'telephone' => $phoneIntl, 'sujet' => $subject, 'message' => $message]);

        send_notification(
            $email,
            "Accusé de réception — " . $subject,
            "<p>Bonjour $name,</p>" .
            "<p>Nous avons bien reçu votre message concernant : <strong>" . htmlspecialchars($subject) . "</strong>.</p>" .
            "<p>Notre équipe vous répondra sous 48h ouvrées.</p>" .
            "<p>Cordialement,<br>La Mairie de Chérif Lo</p>"
        );
        send_notification(
            ADMIN_EMAIL,
            "Nouveau message de contact — " . $subject,
            "<p>Nouveau message reçu via le formulaire de contact.</p>" .
            "<p><strong>De :</strong> $name ($email)</p>" .
            "<p><strong>Sujet :</strong> " . htmlspecialchars($subject) . "</p>" .
            "<p>" . nl2br(htmlspecialchars($message)) . "</p>"
        );

        $smsTexte = "Mairie Chérif Lo : nouveau message de contact de $name ($email / $phone) - $subject";
        send_sms(ADMIN_PHONE_INTL, $smsTexte);
        log_whatsapp_attempt(ADMIN_PHONE_INTL, $smsTexte);

        $sent = true;
        $name = $email = $phone = $subject = $message = "";
    }
}

include __DIR__ . "/includes/header.php";

$heroEyebrow = "Contact";
$heroTitle = "Nous sommes à votre écoute.";
$heroDescription = "Une question, une demande, une doléance ? Notre équipe vous répond dans les meilleurs délais.";
include __DIR__ . "/includes/page-hero.php";

$coords = [
    ["icon" => "map-pin", "title" => "Adresse", "value" => "Mairie de Chérif Lo, Arrondissement de Pambal, Département de Tivaouane, Sénégal"],
    ["icon" => "phone", "title" => "Téléphone", "value" => "78 352 62 23"],
    ["icon" => "mail", "title" => "E-mail", "value" => "diakhatemoramina210993@gmail.com"],
    ["icon" => "clock", "title" => "Horaires", "value" => "Lun–Ven : 08h — 17h · Sam : 09h — 13h"],
];
?>

<section class="container-page py-20 grid lg:grid-cols-5 gap-10">
  <div class="lg:col-span-2 space-y-6">
    <?php foreach ($coords as $c): ?>
      <div class="flex gap-4 rounded-2xl border border-border p-5 bg-card">
        <div class="h-11 w-11 shrink-0 grid place-items-center rounded-xl bg-primary/10 text-primary">
          <i data-lucide="<?= $c['icon'] ?>" class="h-5 w-5"></i>
        </div>
        <div>
          <div class="text-xs uppercase tracking-widest text-primary/70"><?= $c['title'] ?></div>
          <div class="mt-1 text-sm text-foreground/85"><?= $c['value'] ?></div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>

  <form method="post" action="contact.php" class="lg:col-span-3 rounded-3xl border border-border bg-card p-8 md:p-10 shadow-soft">
    <?= csrf_field() ?>
    <input type="text" name="site_web" class="hidden" tabindex="-1" autocomplete="off">
    <h2 class="font-display text-3xl">Écrivez-nous</h2>
    <p class="mt-2 text-muted-foreground">Réponse sous 48h ouvrées.</p>

    <?php if (!empty($errors)): ?>
      <div class="mt-6 rounded-xl border border-destructive/30 bg-destructive/10 p-4 text-sm text-destructive">
        <ul class="list-disc list-inside space-y-1">
          <?php foreach ($errors as $err): ?><li><?= htmlspecialchars($err) ?></li><?php endforeach; ?>
        </ul>
      </div>
    <?php endif; ?>

    <div class="mt-8 grid md:grid-cols-2 gap-5">
      <label class="text-sm">
        <span class="text-foreground/85">Nom complet</span>
        <input name="name" value="<?= htmlspecialchars($name) ?>" required class="mt-2 w-full rounded-xl border border-input bg-background px-4 py-3 outline-none focus:border-primary">
      </label>
      <label class="text-sm">
        <span class="text-foreground/85">E-mail</span>
        <input type="email" name="email" value="<?= htmlspecialchars($email) ?>" required class="mt-2 w-full rounded-xl border border-input bg-background px-4 py-3 outline-none focus:border-primary">
      </label>
      <label class="text-sm md:col-span-2">
        <span class="text-foreground/85">Téléphone (WhatsApp si possible)</span>
        <input name="phone" value="<?= htmlspecialchars($phone) ?>" required placeholder="77 123 45 67" class="mt-2 w-full rounded-xl border border-input bg-background px-4 py-3 outline-none focus:border-primary">
      </label>
      <label class="text-sm md:col-span-2">
        <span class="text-foreground/85">Sujet</span>
        <input name="subject" value="<?= htmlspecialchars($subject) ?>" required class="mt-2 w-full rounded-xl border border-input bg-background px-4 py-3 outline-none focus:border-primary">
      </label>
      <label class="text-sm md:col-span-2">
        <span class="text-foreground/85">Message</span>
        <textarea name="message" required rows="6" class="mt-2 w-full rounded-xl border border-input bg-background px-4 py-3 outline-none focus:border-primary resize-none"><?= htmlspecialchars($message) ?></textarea>
      </label>
    </div>

    <button type="submit" class="mt-6 inline-flex items-center gap-2 rounded-full bg-primary text-primary-foreground px-6 py-3.5 text-sm font-semibold hover:bg-primary-deep transition shadow-elegant">
      Envoyer le message <i data-lucide="send" class="h-4 w-4"></i>
    </button>
    <?php if ($sent): ?>
      <p class="mt-4 text-sm text-primary">Merci ! Votre message a bien été enregistré.</p>
    <?php endif; ?>
  </form>
</section>

<section class="container-page pb-20">
  <div class="flex items-center gap-3 mb-6">
    <div class="h-11 w-11 shrink-0 grid place-items-center rounded-xl bg-primary/10 text-primary">
      <i data-lucide="map" class="h-5 w-5"></i>
    </div>
    <div>
      <div class="text-xs uppercase tracking-widest text-primary/70">Localisation</div>
      <h2 class="font-display text-2xl">Où se trouve la commune de Chérif Lo</h2>
    </div>
  </div>
  <div class="rounded-3xl border border-border overflow-hidden shadow-soft">
    <iframe
      src="https://www.google.com/maps?q=Ch%C3%A9rif+L%C3%B4,+Tivaouane,+S%C3%A9n%C3%A9gal&output=embed"
      width="100%" height="420" style="border:0" loading="lazy"
      referrerpolicy="no-referrer-when-downgrade"
      title="Localisation de la commune de Chérif Lo sur la carte">
    </iframe>
  </div>
  <a href="https://www.google.com/maps/search/?api=1&query=Ch%C3%A9rif+L%C3%B4%2C+Tivaouane%2C+S%C3%A9n%C3%A9gal" target="_blank" rel="noopener noreferrer"
     class="mt-4 inline-flex items-center gap-2 text-sm font-medium text-primary hover:text-primary-deep">
    Ouvrir dans Google Maps <i data-lucide="external-link" class="h-3.5 w-3.5"></i>
  </a>
</section>

<?php include __DIR__ . "/includes/footer.php"; ?>
