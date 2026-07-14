<?php
require __DIR__ . '/includes/csrf.php';
require __DIR__ . '/includes/db.php';
require __DIR__ . '/includes/functions.php';
require __DIR__ . '/includes/document-types.php';
require __DIR__ . '/includes/citizen-auth.php';

require_citizen($_SERVER['REQUEST_URI']);
$citoyen = current_citoyen($pdo);

$type = $_GET['type'] ?? $_POST['type'] ?? '';

if ($type === 'etat-civil') {
    header('Location: etat-civil.php');
    exit;
}

$typeInfo = $DOCUMENT_TYPES[$type] ?? null;

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

if ($typeInfo && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) {
        $errors[] = "Session expirée, merci de renvoyer le formulaire.";
    }
    if (!empty($_POST['site_web'])) {
        // honeypot rempli => bot
        $errors[] = "Requête invalide.";
    }

    $civilite = trim($_POST['civilite'] ?? '');
    $nom = trim($_POST['nom'] ?? '');
    $prenom = trim($_POST['prenom'] ?? '');
    $dateNaissance = trim($_POST['date_naissance'] ?? '');
    $lieuNaissance = trim($_POST['lieu_naissance'] ?? '');
    $telephone = trim($_POST['telephone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $adresse = trim($_POST['adresse'] ?? '');
    $modeRetrait = ($_POST['mode_retrait'] ?? 'guichet') === 'postal' ? 'postal' : 'guichet';
    $nombreCopies = max(1, (int) ($_POST['nombre_copies'] ?? 1));

    if ($nom === '') $errors[] = "Le nom est requis.";
    if ($prenom === '') $errors[] = "Le prénom est requis.";
    if ($telephone === '') $errors[] = "Le téléphone est requis.";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "L'adresse e-mail est invalide.";
    if ($modeRetrait === 'postal' && $adresse === '') $errors[] = "L'adresse est requise pour un envoi postal.";

    $details = [];
    foreach ($typeInfo['fields'] as $fieldKey) {
        $value = trim($_POST[$fieldKey] ?? '');
        if ($value === '') {
            $errors[] = $FIELD_META[$fieldKey]['label'] . " est requis.";
        }
        $details[$fieldKey] = $value;
    }

    $pieceJointe = null;
    if (empty($errors)) {
        $trackingCode = generate_tracking_code('DOC');
        $pieceJointe = handle_upload('piece_identite', $trackingCode, $errors);
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare(
            "INSERT INTO demandes (code_suivi, citoyen_id, type_document, civilite, nom, prenom, date_naissance, lieu_naissance, telephone, email, adresse, mode_retrait, nombre_copies, details_json, piece_jointe)
             VALUES (:code, :citoyen_id, :type, :civilite, :nom, :prenom, :date_naissance, :lieu_naissance, :telephone, :email, :adresse, :mode_retrait, :nombre_copies, :details, :piece)"
        );
        $stmt->execute([
            'code' => $trackingCode,
            'citoyen_id' => $citoyen['id'],
            'type' => $type,
            'civilite' => $civilite ?: null,
            'nom' => $nom,
            'prenom' => $prenom,
            'date_naissance' => $dateNaissance ?: null,
            'lieu_naissance' => $lieuNaissance ?: null,
            'telephone' => $telephone,
            'email' => $email,
            'adresse' => $adresse ?: null,
            'mode_retrait' => $modeRetrait,
            'nombre_copies' => $nombreCopies,
            'details' => json_encode($details, JSON_UNESCAPED_UNICODE),
            'piece' => $pieceJointe,
        ]);

        $recapLines = "<p><strong>Type de demande :</strong> " . htmlspecialchars($typeInfo['label']) . "</p>";
        $recapLines .= "<p><strong>Demandeur :</strong> " . htmlspecialchars("$prenom $nom") . "</p>";
        $recapLines .= "<p><strong>Mode de retrait :</strong> " . ($modeRetrait === 'postal' ? 'Envoi postal' : 'Retrait au guichet') . "</p>";

        send_notification(
            $email,
            "Accusé de réception — Demande $trackingCode",
            "<p>Bonjour $prenom $nom,</p>" .
            "<p>Nous avons bien reçu votre demande auprès de la Mairie de Chérif Lo.</p>" .
            $recapLines .
            "<p>Votre code de suivi est : <strong>$trackingCode</strong></p>" .
            "<p>Vous pouvez suivre l'avancement de votre dossier à tout moment depuis « Mon espace » sur le site, avec votre compte citoyen.</p>" .
            "<p>Délai indicatif de traitement : " . htmlspecialchars($typeInfo['delai']) . ".</p>" .
            "<p>Cordialement,<br>La Mairie de Chérif Lo</p>"
        );

        send_notification(
            ADMIN_EMAIL,
            "Nouvelle demande reçue — $trackingCode",
            "<p>Une nouvelle demande a été déposée sur le portail.</p>" .
            $recapLines .
            "<p><strong>Code :</strong> $trackingCode</p>" .
            "<p><strong>Contact :</strong> $email / $telephone</p>" .
            "<p>Connectez-vous à l'espace mairie pour la traiter.</p>"
        );

        $smsTexte = "Mairie Chérif Lo : nouvelle demande $trackingCode (" . $typeInfo['label'] . ") de $prenom $nom, tel $telephone.";
        send_sms(ADMIN_PHONE_INTL, $smsTexte);
        log_whatsapp_attempt(ADMIN_PHONE_INTL, $smsTexte);
        $whatsappRecapLink = whatsapp_link(ADMIN_PHONE_INTL, $smsTexte);

        $submitted = true;
    }
}

$pageTitle = $typeInfo ? $typeInfo['label'] . " — Mairie de Chérif Lo" : "Demander un document — Mairie de Chérif Lo";
$pageDescription = "Effectuez votre demande de document administratif en ligne auprès de la Mairie de Chérif Lo.";
$activePage = "services.php";
include __DIR__ . "/includes/header.php";
?>

<?php if (!$typeInfo): ?>

  <?php
  $heroEyebrow = "Démarches en ligne";
  $heroTitle = "Quel document souhaitez-vous demander ?";
  $heroDescription = "Sélectionnez le type de document. Vous recevrez immédiatement un accusé de réception avec un code de suivi.";
  include __DIR__ . "/includes/page-hero.php";
  ?>
  <section class="container-page py-20">
    <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
      <?php foreach ($DOCUMENT_TYPES as $slug => $info): ?>
        <a href="<?= $slug === 'etat-civil' ? 'etat-civil.php' : 'demande.php?type=' . urlencode($slug) ?>" class="group rounded-3xl border border-border bg-card p-7 hover:shadow-elegant hover:border-primary/40 transition">
          <div class="h-12 w-12 grid place-items-center rounded-2xl bg-primary/10 text-primary group-hover:bg-primary group-hover:text-primary-foreground transition">
            <i data-lucide="<?= $info['icon'] ?>" class="h-6 w-6"></i>
          </div>
          <h3 class="mt-5 font-display text-xl"><?= htmlspecialchars($info['label']) ?></h3>
          <p class="mt-2 text-xs uppercase tracking-widest text-primary/70">Délai · <?= htmlspecialchars($info['delai']) ?></p>
        </a>
      <?php endforeach; ?>
    </div>
  </section>

<?php elseif ($submitted): ?>

  <section class="container-page py-24">
    <div class="max-w-2xl mx-auto rounded-3xl border border-border bg-card p-10 text-center shadow-elegant">
      <div class="mx-auto h-16 w-16 grid place-items-center rounded-full bg-primary/10 text-primary">
        <i data-lucide="check-circle-2" class="h-8 w-8"></i>
      </div>
      <h1 class="mt-6 font-display text-3xl">Demande bien reçue !</h1>
      <p class="mt-3 text-muted-foreground">
        Un accusé de réception a été envoyé automatiquement à votre adresse e-mail.
        Retrouvez à tout moment son avancement dans « Mon espace ». Code de suivi :
      </p>
      <div class="mt-6 inline-block rounded-2xl bg-secondary px-6 py-4 font-display text-2xl tracking-widest text-primary-deep">
        <?= htmlspecialchars($trackingCode) ?>
      </div>
      <p class="mt-4 text-sm text-muted-foreground">Délai indicatif de traitement : <?= htmlspecialchars($typeInfo['delai']) ?></p>
      <div class="mt-8 flex flex-wrap justify-center gap-3">
        <a href="/mairie/compte/index.php" class="rounded-full bg-primary text-primary-foreground px-6 py-3 text-sm font-semibold hover:bg-primary-deep transition">Voir dans Mon espace</a>
        <a href="<?= htmlspecialchars($whatsappRecapLink) ?>" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-2 rounded-full bg-[#25D366] text-white px-6 py-3 text-sm font-semibold hover:opacity-90 transition">
          <i data-lucide="message-circle" class="h-4 w-4"></i> Prévenir la mairie sur WhatsApp
        </a>
        <a href="/mairie/index.php" class="rounded-full border border-input px-6 py-3 text-sm font-medium hover:bg-secondary transition">Retour à l'accueil</a>
      </div>
    </div>
  </section>

<?php else: ?>

  <?php
  $heroEyebrow = "Démarches en ligne";
  $heroTitle = $typeInfo['label'];
  $heroDescription = "Délai indicatif de traitement : " . $typeInfo['delai'] . ". Tous les champs sont requis sauf mention contraire.";
  include __DIR__ . "/includes/page-hero.php";
  ?>

  <section class="container-page py-20">
    <form method="post" action="demande.php" enctype="multipart/form-data" class="max-w-3xl mx-auto rounded-3xl border border-border bg-card p-8 md:p-10 shadow-soft">
      <?= csrf_field() ?>
      <input type="hidden" name="type" value="<?= htmlspecialchars($type) ?>">
      <input type="text" name="site_web" class="hidden" tabindex="-1" autocomplete="off">

      <?php if (!empty($errors)): ?>
        <div class="mb-6 rounded-xl border border-destructive/30 bg-destructive/10 p-4 text-sm text-destructive">
          <ul class="list-disc list-inside space-y-1">
            <?php foreach ($errors as $err): ?><li><?= htmlspecialchars($err) ?></li><?php endforeach; ?>
          </ul>
        </div>
      <?php endif; ?>

      <h2 class="font-display text-2xl">Vos informations</h2>
      <div class="mt-6 grid md:grid-cols-2 gap-5">
        <label class="text-sm">
          <span class="text-foreground/85">Civilité</span>
          <select name="civilite" class="mt-2 w-full rounded-xl border border-input bg-background px-4 py-3 outline-none focus:border-primary">
            <option value="M." <?= ($old['civilite'] ?? '') === 'M.' ? 'selected' : '' ?>>M.</option>
            <option value="Mme" <?= ($old['civilite'] ?? '') === 'Mme' ? 'selected' : '' ?>>Mme</option>
            <option value="Mlle" <?= ($old['civilite'] ?? '') === 'Mlle' ? 'selected' : '' ?>>Mlle</option>
          </select>
        </label>
        <div></div>
        <label class="text-sm">
          <span class="text-foreground/85">Nom</span>
          <input name="nom" value="<?= htmlspecialchars($old['nom'] ?? '') ?>" required class="mt-2 w-full rounded-xl border border-input bg-background px-4 py-3 outline-none focus:border-primary">
        </label>
        <label class="text-sm">
          <span class="text-foreground/85">Prénom</span>
          <input name="prenom" value="<?= htmlspecialchars($old['prenom'] ?? '') ?>" required class="mt-2 w-full rounded-xl border border-input bg-background px-4 py-3 outline-none focus:border-primary">
        </label>
        <label class="text-sm">
          <span class="text-foreground/85">Date de naissance</span>
          <input type="date" name="date_naissance" value="<?= htmlspecialchars($old['date_naissance'] ?? '') ?>" class="mt-2 w-full rounded-xl border border-input bg-background px-4 py-3 outline-none focus:border-primary">
        </label>
        <label class="text-sm">
          <span class="text-foreground/85">Lieu de naissance</span>
          <input name="lieu_naissance" value="<?= htmlspecialchars($old['lieu_naissance'] ?? '') ?>" class="mt-2 w-full rounded-xl border border-input bg-background px-4 py-3 outline-none focus:border-primary">
        </label>
        <label class="text-sm">
          <span class="text-foreground/85">Téléphone</span>
          <input name="telephone" value="<?= htmlspecialchars($old['telephone'] ?? '') ?>" required class="mt-2 w-full rounded-xl border border-input bg-background px-4 py-3 outline-none focus:border-primary">
        </label>
        <label class="text-sm">
          <span class="text-foreground/85">E-mail</span>
          <input type="email" name="email" value="<?= htmlspecialchars($old['email'] ?? '') ?>" required class="mt-2 w-full rounded-xl border border-input bg-background px-4 py-3 outline-none focus:border-primary">
        </label>
      </div>

      <h2 class="mt-10 font-display text-2xl">Détails de la demande</h2>
      <div class="mt-6 grid md:grid-cols-2 gap-5">
        <?php foreach ($typeInfo['fields'] as $fieldKey): $meta = $FIELD_META[$fieldKey]; ?>
          <label class="text-sm <?= $meta['type'] === 'textarea' ? 'md:col-span-2' : '' ?>">
            <span class="text-foreground/85"><?= htmlspecialchars($meta['label']) ?></span>
            <?php if ($meta['type'] === 'textarea'): ?>
              <textarea name="<?= $fieldKey ?>" required rows="4" class="mt-2 w-full rounded-xl border border-input bg-background px-4 py-3 outline-none focus:border-primary resize-none"><?= htmlspecialchars($old[$fieldKey] ?? '') ?></textarea>
            <?php else: ?>
              <input type="<?= $meta['type'] ?>" name="<?= $fieldKey ?>" value="<?= htmlspecialchars($old[$fieldKey] ?? '') ?>" required class="mt-2 w-full rounded-xl border border-input bg-background px-4 py-3 outline-none focus:border-primary">
            <?php endif; ?>
          </label>
        <?php endforeach; ?>
      </div>

      <h2 class="mt-10 font-display text-2xl">Retrait du document</h2>
      <div class="mt-6 grid md:grid-cols-2 gap-5">
        <div class="flex gap-6 items-center text-sm">
          <label class="flex items-center gap-2">
            <input type="radio" name="mode_retrait" value="guichet" <?= ($old['mode_retrait'] ?? 'guichet') === 'guichet' ? 'checked' : '' ?>> Retrait au guichet
          </label>
          <label class="flex items-center gap-2">
            <input type="radio" name="mode_retrait" value="postal" <?= ($old['mode_retrait'] ?? '') === 'postal' ? 'checked' : '' ?>> Envoi postal
          </label>
        </div>
        <label class="text-sm">
          <span class="text-foreground/85">Nombre de copies</span>
          <input type="number" min="1" name="nombre_copies" value="<?= htmlspecialchars($old['nombre_copies'] ?? '1') ?>" class="mt-2 w-full rounded-xl border border-input bg-background px-4 py-3 outline-none focus:border-primary">
        </label>
        <label class="text-sm md:col-span-2">
          <span class="text-foreground/85">Adresse postale (si envoi postal)</span>
          <input name="adresse" value="<?= htmlspecialchars($old['adresse'] ?? '') ?>" class="mt-2 w-full rounded-xl border border-input bg-background px-4 py-3 outline-none focus:border-primary">
        </label>
        <label class="text-sm md:col-span-2">
          <span class="text-foreground/85">Pièce jointe — copie de pièce d'identité (optionnel, pdf/jpg/png, 5 Mo max)</span>
          <input type="file" name="piece_identite" accept=".pdf,.jpg,.jpeg,.png" class="mt-2 w-full rounded-xl border border-input bg-background px-4 py-3 outline-none focus:border-primary">
        </label>
      </div>

      <button type="submit" class="mt-8 inline-flex items-center gap-2 rounded-full bg-primary text-primary-foreground px-6 py-3.5 text-sm font-semibold hover:bg-primary-deep transition shadow-elegant">
        Envoyer ma demande <i data-lucide="send" class="h-4 w-4"></i>
      </button>
    </form>
  </section>

<?php endif; ?>

<?php include __DIR__ . "/includes/footer.php"; ?>
