<?php
require __DIR__ . '/includes/csrf.php';
require __DIR__ . '/includes/db.php';
require __DIR__ . '/includes/functions.php';
require __DIR__ . '/includes/citizen-auth.php';

require_citizen($_SERVER['REQUEST_URI']);
$citoyen = current_citoyen($pdo);

$objets = [
    "Audience avec le Maire",
    "Doléance",
    "État civil",
    "Affaires sociales",
    "Urbanisme",
    "Autre",
];

// Créneaux possibles (validation stricte du jour faite côté serveur plus bas)
$creneaux = ['08:00', '09:00', '10:00', '11:00', '12:00', '13:00', '14:00', '15:00', '16:00'];

$errors = [];
$submitted = false;
$trackingCode = '';
$old = $_POST;
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $old = [
        'nom' => $citoyen['nom'],
        'prenom' => $citoyen['prenom'],
        'telephone' => $citoyen['telephone'],
        'email' => $citoyen['email'],
    ];
}
if (empty($old['objet']) && !empty($_GET['objet'])) {
    $old['objet'] = $_GET['objet'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) {
        $errors[] = "Session expirée, merci de renvoyer le formulaire.";
    }
    if (!empty($_POST['site_web'])) {
        $errors[] = "Requête invalide.";
    }

    $objet = trim($_POST['objet'] ?? '');
    $nom = trim($_POST['nom'] ?? '');
    $prenom = trim($_POST['prenom'] ?? '');
    $telephone = trim($_POST['telephone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $dateRdv = trim($_POST['date_rdv'] ?? '');
    $heureRdv = trim($_POST['heure_rdv'] ?? '');
    $motif = trim($_POST['motif'] ?? '');

    if ($objet === '') $errors[] = "L'objet du rendez-vous est requis.";
    if ($nom === '') $errors[] = "Le nom est requis.";
    if ($prenom === '') $errors[] = "Le prénom est requis.";
    if ($telephone === '') $errors[] = "Le téléphone est requis.";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "L'adresse e-mail est invalide.";

    $dateObj = DateTime::createFromFormat('Y-m-d', $dateRdv);
    $today = new DateTime('today');
    if (!$dateObj || $dateObj <= $today) {
        $errors[] = "Merci de choisir une date valide, à partir de demain.";
    } elseif ((int) $dateObj->diff($today)->format('%a') > 90 && $dateObj > $today) {
        // pas de limite stricte, juste garde-fou soft — laissé volontairement permissif
    }

    $dayOfWeek = $dateObj ? (int) $dateObj->format('N') : null; // 1=lundi ... 7=dimanche
    if ($dayOfWeek === 7) {
        $errors[] = "La mairie est fermée le dimanche. Merci de choisir un autre jour.";
    } elseif ($dayOfWeek === 6 && !in_array($heureRdv, ['09:00', '10:00', '11:00', '12:00'], true)) {
        $errors[] = "Le samedi, les rendez-vous sont disponibles uniquement entre 09h et 13h.";
    } elseif ($dayOfWeek !== null && $dayOfWeek <= 5 && !in_array($heureRdv, $creneaux, true)) {
        $errors[] = "Merci de choisir un créneau horaire valide.";
    }

    if (empty($errors)) {
        $check = $pdo->prepare("SELECT COUNT(*) FROM rendezvous WHERE date_rdv = :d AND heure_rdv = :h AND statut != 'annule'");
        $check->execute(['d' => $dateRdv, 'h' => $heureRdv]);
        if ((int) $check->fetchColumn() > 0) {
            $errors[] = "Ce créneau est déjà réservé. Merci de choisir une autre date ou un autre horaire.";
        }
    }

    if (empty($errors)) {
        $trackingCode = generate_tracking_code('RDV');
        $stmt = $pdo->prepare(
            "INSERT INTO rendezvous (code_suivi, citoyen_id, objet, nom, prenom, telephone, email, date_rdv, heure_rdv, motif)
             VALUES (:code, :citoyen_id, :objet, :nom, :prenom, :telephone, :email, :date_rdv, :heure_rdv, :motif)"
        );
        $stmt->execute([
            'code' => $trackingCode,
            'citoyen_id' => $citoyen['id'],
            'objet' => $objet,
            'nom' => $nom,
            'prenom' => $prenom,
            'telephone' => $telephone,
            'email' => $email,
            'date_rdv' => $dateRdv,
            'heure_rdv' => $heureRdv,
            'motif' => $motif ?: null,
        ]);

        $dateFr = $dateObj->format('d/m/Y');
        send_notification(
            $email,
            "Accusé de réception — Rendez-vous $trackingCode",
            "<p>Bonjour $prenom $nom,</p>" .
            "<p>Votre demande de rendez-vous auprès de la Mairie de Chérif Lo a bien été enregistrée.</p>" .
            "<p><strong>Objet :</strong> " . htmlspecialchars($objet) . "</p>" .
            "<p><strong>Date souhaitée :</strong> $dateFr à $heureRdv</p>" .
            "<p>Votre code de suivi est : <strong>$trackingCode</strong></p>" .
            "<p>Votre créneau est réservé et sera confirmé par nos services (statut consultable dans « Mon espace »).</p>" .
            "<p>Cordialement,<br>La Mairie de Chérif Lo</p>"
        );
        send_notification(
            ADMIN_EMAIL,
            "Nouvelle demande de rendez-vous — $trackingCode",
            "<p>Une nouvelle demande de rendez-vous a été déposée.</p>" .
            "<p><strong>Objet :</strong> " . htmlspecialchars($objet) . "</p>" .
            "<p><strong>Demandeur :</strong> $prenom $nom ($telephone / $email)</p>" .
            "<p><strong>Créneau :</strong> $dateFr à $heureRdv</p>" .
            "<p>Connectez-vous à l'espace mairie pour confirmer.</p>"
        );

        $smsTexte = "Mairie Chérif Lo : nouveau RDV $trackingCode ($objet) de $prenom $nom le $dateFr à $heureRdv, tel $telephone.";
        send_sms(ADMIN_PHONE_INTL, $smsTexte);
        log_whatsapp_attempt(ADMIN_PHONE_INTL, $smsTexte);
        $whatsappRecapLink = whatsapp_link(ADMIN_PHONE_INTL, $smsTexte);

        $submitted = true;
    }
}

$pageTitle = "Prendre rendez-vous — Mairie de Chérif Lo";
$pageDescription = "Réservez un créneau pour une audience avec le Maire, une doléance ou tout autre service de la mairie.";
$activePage = "";
include __DIR__ . "/includes/header.php";

$heroEyebrow = "Rendez-vous";
$heroTitle = "Prendre rendez-vous avec votre mairie.";
$heroDescription = "Audience, doléance, état civil… choisissez un créneau disponible, en quelques clics.";
include __DIR__ . "/includes/page-hero.php";
?>

<section class="container-page py-20">
  <?php if ($submitted): ?>
    <div class="max-w-2xl mx-auto rounded-3xl border border-border bg-card p-10 text-center shadow-elegant">
      <div class="mx-auto h-16 w-16 grid place-items-center rounded-full bg-primary/10 text-primary">
        <i data-lucide="calendar-check-2" class="h-8 w-8"></i>
      </div>
      <h1 class="mt-6 font-display text-3xl">Rendez-vous enregistré !</h1>
      <p class="mt-3 text-muted-foreground">
        Un accusé de réception a été envoyé automatiquement à votre adresse e-mail.
        Retrouvez-le à tout moment dans « Mon espace ». Code de suivi :
      </p>
      <div class="mt-6 inline-block rounded-2xl bg-secondary px-6 py-4 font-display text-2xl tracking-widest text-primary-deep">
        <?= htmlspecialchars($trackingCode) ?>
      </div>
      <div class="mt-8 flex flex-wrap justify-center gap-3">
        <a href="/mairie/compte/index.php" class="rounded-full bg-primary text-primary-foreground px-6 py-3 text-sm font-semibold hover:bg-primary-deep transition">Voir dans Mon espace</a>
        <a href="<?= htmlspecialchars($whatsappRecapLink) ?>" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-2 rounded-full bg-[#25D366] text-white px-6 py-3 text-sm font-semibold hover:opacity-90 transition">
          <i data-lucide="message-circle" class="h-4 w-4"></i> Prévenir la mairie sur WhatsApp
        </a>
        <a href="/mairie/index.php" class="rounded-full border border-input px-6 py-3 text-sm font-medium hover:bg-secondary transition">Retour à l'accueil</a>
      </div>
    </div>
  <?php else: ?>
    <form method="post" action="rendezvous.php" class="max-w-3xl mx-auto rounded-3xl border border-border bg-card p-8 md:p-10 shadow-soft">
      <?= csrf_field() ?>
      <input type="text" name="site_web" class="hidden" tabindex="-1" autocomplete="off">

      <?php if (!empty($errors)): ?>
        <div class="mb-6 rounded-xl border border-destructive/30 bg-destructive/10 p-4 text-sm text-destructive">
          <ul class="list-disc list-inside space-y-1">
            <?php foreach ($errors as $err): ?><li><?= htmlspecialchars($err) ?></li><?php endforeach; ?>
          </ul>
        </div>
      <?php endif; ?>

      <h2 class="font-display text-2xl">Votre rendez-vous</h2>
      <p class="mt-2 text-sm text-muted-foreground">Lundi – Vendredi : 08h–17h · Samedi : 09h–13h · Dimanche : fermé.</p>

      <div class="mt-6 grid md:grid-cols-2 gap-5">
        <label class="text-sm md:col-span-2">
          <span class="text-foreground/85">Objet du rendez-vous</span>
          <select name="objet" required class="mt-2 w-full rounded-xl border border-input bg-background px-4 py-3 outline-none focus:border-primary">
            <option value="">— Sélectionner —</option>
            <?php foreach ($objets as $o): ?>
              <option value="<?= htmlspecialchars($o) ?>" <?= ($old['objet'] ?? '') === $o ? 'selected' : '' ?>><?= htmlspecialchars($o) ?></option>
            <?php endforeach; ?>
          </select>
        </label>
        <label class="text-sm">
          <span class="text-foreground/85">Nom</span>
          <input name="nom" value="<?= htmlspecialchars($old['nom'] ?? '') ?>" required class="mt-2 w-full rounded-xl border border-input bg-background px-4 py-3 outline-none focus:border-primary">
        </label>
        <label class="text-sm">
          <span class="text-foreground/85">Prénom</span>
          <input name="prenom" value="<?= htmlspecialchars($old['prenom'] ?? '') ?>" required class="mt-2 w-full rounded-xl border border-input bg-background px-4 py-3 outline-none focus:border-primary">
        </label>
        <label class="text-sm">
          <span class="text-foreground/85">Téléphone</span>
          <input name="telephone" value="<?= htmlspecialchars($old['telephone'] ?? '') ?>" required class="mt-2 w-full rounded-xl border border-input bg-background px-4 py-3 outline-none focus:border-primary">
        </label>
        <label class="text-sm">
          <span class="text-foreground/85">E-mail</span>
          <input type="email" name="email" value="<?= htmlspecialchars($old['email'] ?? '') ?>" required class="mt-2 w-full rounded-xl border border-input bg-background px-4 py-3 outline-none focus:border-primary">
        </label>
        <label class="text-sm">
          <span class="text-foreground/85">Date souhaitée</span>
          <input type="date" name="date_rdv" min="<?= (new DateTime('tomorrow'))->format('Y-m-d') ?>" value="<?= htmlspecialchars($old['date_rdv'] ?? '') ?>" required class="mt-2 w-full rounded-xl border border-input bg-background px-4 py-3 outline-none focus:border-primary">
        </label>
        <label class="text-sm">
          <span class="text-foreground/85">Créneau horaire</span>
          <select name="heure_rdv" required class="mt-2 w-full rounded-xl border border-input bg-background px-4 py-3 outline-none focus:border-primary">
            <option value="">— Sélectionner —</option>
            <?php foreach ($creneaux as $c): ?>
              <option value="<?= $c ?>" <?= ($old['heure_rdv'] ?? '') === $c ? 'selected' : '' ?>><?= $c ?></option>
            <?php endforeach; ?>
          </select>
        </label>
        <label class="text-sm md:col-span-2">
          <span class="text-foreground/85">Motif / message (optionnel)</span>
          <textarea name="motif" rows="4" class="mt-2 w-full rounded-xl border border-input bg-background px-4 py-3 outline-none focus:border-primary resize-none"><?= htmlspecialchars($old['motif'] ?? '') ?></textarea>
        </label>
      </div>

      <button type="submit" class="mt-8 inline-flex items-center gap-2 rounded-full bg-primary text-primary-foreground px-6 py-3.5 text-sm font-semibold hover:bg-primary-deep transition shadow-elegant">
        Réserver ce créneau <i data-lucide="calendar-check-2" class="h-4 w-4"></i>
      </button>
    </form>
  <?php endif; ?>
</section>

<?php include __DIR__ . "/includes/footer.php"; ?>
