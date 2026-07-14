<?php
require __DIR__ . '/includes/csrf.php';
require __DIR__ . '/includes/db.php';
require __DIR__ . '/includes/functions.php';
require __DIR__ . '/includes/document-types.php';
require __DIR__ . '/includes/citizen-auth.php';

require_citizen($_SERVER['REQUEST_URI']);
$citoyen = current_citoyen($pdo);

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
        'lieu_naissance' => 'Chérif Lo',
    ];
}
$selectedActes = $old['actes'] ?? [];
if ($_SERVER['REQUEST_METHOD'] !== 'POST' && !empty($_GET['acte']) && array_key_exists($_GET['acte'], $ETAT_CIVIL_ACTES)) {
    $selectedActes = [$_GET['acte']];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) {
        $errors[] = "Session expirée, merci de renvoyer le formulaire.";
    }
    if (!empty($_POST['site_web'])) {
        $errors[] = "Requête invalide.";
    }

    $actes = array_values(array_intersect($_POST['actes'] ?? [], array_keys($ETAT_CIVIL_ACTES)));
    if (empty($actes)) {
        $errors[] = "Merci de sélectionner au moins un type d'acte.";
    }

    $nom = trim($_POST['nom'] ?? '');
    $prenom = trim($_POST['prenom'] ?? '');
    $dateNaissance = trim($_POST['date_naissance'] ?? '');
    $lieuNaissance = trim($_POST['lieu_naissance'] ?? '');
    $perePrenom = trim($_POST['pere_prenom'] ?? '');
    $pereNom = trim($_POST['pere_nom'] ?? '');
    $merePrenom = trim($_POST['mere_prenom'] ?? '');
    $mereNom = trim($_POST['mere_nom'] ?? '');
    $anneeRegistre = trim($_POST['annee_registre'] ?? '');
    $numeroRegistre = trim($_POST['numero_registre'] ?? '');
    $qualiteDemandeur = trim($_POST['qualite_demandeur'] ?? '');
    $adresse = trim($_POST['adresse'] ?? '');
    $telephone = trim($_POST['telephone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $modeDelivrance = trim($_POST['mode_delivrance'] ?? '');
    $modePaiement = trim($_POST['mode_paiement'] ?? '');
    $referencePaiement = trim($_POST['reference_paiement'] ?? '');
    $consentDonnees = !empty($_POST['consent_donnees']);
    $consentResponsabilite = !empty($_POST['consent_responsabilite']);

    if ($nom === '') $errors[] = "Le nom est requis.";
    if ($prenom === '') $errors[] = "Le prénom est requis.";
    if ($dateNaissance === '') $errors[] = "La date de naissance est requise.";
    if ($lieuNaissance === '') $errors[] = "Le lieu de naissance est requis.";
    if ($anneeRegistre === '') $errors[] = "L'année du registre est requise.";
    if ($numeroRegistre === '') $errors[] = "Le numéro dans le registre est requis.";
    if (!in_array($qualiteDemandeur, $ETAT_CIVIL_QUALITES, true)) $errors[] = "Merci de préciser votre qualité.";
    if ($adresse === '') $errors[] = "L'adresse actuelle est requise.";
    if ($telephone === '') $errors[] = "Le téléphone est requis.";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "L'adresse e-mail est invalide.";
    if (!in_array($modeDelivrance, $ETAT_CIVIL_MODES_DELIVRANCE, true)) $errors[] = "Merci de choisir un mode de délivrance.";
    if (!array_key_exists($modePaiement, $ETAT_CIVIL_MODES_PAIEMENT)) $errors[] = "Merci de choisir un mode de paiement.";
    if ($referencePaiement === '') $errors[] = "La référence de la transaction est requise.";
    if (!$consentDonnees) $errors[] = "Le consentement au traitement des données personnelles est requis.";
    if (!$consentResponsabilite) $errors[] = "L'acceptation de la clause de non-responsabilité est requise.";

    if (empty($errors)) {
        $trackingCode = generate_tracking_code('ETC');

        $details = [
            'types_actes' => implode(', ', array_map(fn($s) => $ETAT_CIVIL_ACTES[$s], $actes)),
            'pere_prenom' => $perePrenom,
            'pere_nom' => $pereNom,
            'mere_prenom' => $merePrenom,
            'mere_nom' => $mereNom,
            'annee_registre' => $anneeRegistre,
            'numero_registre' => $numeroRegistre,
            'qualite_demandeur' => $qualiteDemandeur,
            'mode_delivrance' => $modeDelivrance,
            'mode_paiement' => $ETAT_CIVIL_MODES_PAIEMENT[$modePaiement],
            'reference_paiement' => $referencePaiement,
        ];
        // Champs optionnels : ne pas garder de clés vides encombrantes
        foreach (['pere_prenom', 'pere_nom', 'mere_prenom', 'mere_nom'] as $optKey) {
            if ($details[$optKey] === '') unset($details[$optKey]);
        }

        $stmt = $pdo->prepare(
            "INSERT INTO demandes (code_suivi, citoyen_id, type_document, nom, prenom, date_naissance, lieu_naissance, telephone, email, adresse, mode_retrait, nombre_copies, details_json)
             VALUES (:code, :citoyen_id, 'etat-civil', :nom, :prenom, :date_naissance, :lieu_naissance, :telephone, :email, :adresse, 'guichet', 1, :details)"
        );
        $stmt->execute([
            'code' => $trackingCode,
            'citoyen_id' => $citoyen['id'],
            'nom' => $nom,
            'prenom' => $prenom,
            'date_naissance' => $dateNaissance ?: null,
            'lieu_naissance' => $lieuNaissance,
            'telephone' => $telephone,
            'email' => $email,
            'adresse' => $adresse,
            'details' => json_encode($details, JSON_UNESCAPED_UNICODE),
        ]);

        $actesLabel = $details['types_actes'];
        $recap = "<p><strong>Acte(s) demandé(s) :</strong> " . htmlspecialchars($actesLabel) . "</p>" .
                 "<p><strong>Demandeur :</strong> " . htmlspecialchars("$prenom $nom") . " (" . htmlspecialchars($qualiteDemandeur) . ")</p>" .
                 "<p><strong>Mode de délivrance :</strong> " . htmlspecialchars($modeDelivrance) . "</p>";

        send_notification(
            $email,
            "Accusé de réception — Demande $trackingCode",
            "<p>Bonjour $prenom $nom,</p>" .
            "<p>Nous avons bien reçu votre demande d'acte(s) d'état civil auprès de la Mairie de Chérif Lo.</p>" .
            $recap .
            "<p>Votre code de suivi est : <strong>$trackingCode</strong></p>" .
            "<p>Vous pouvez suivre l'avancement de votre dossier à tout moment depuis « Mon espace » sur le site, avec votre compte citoyen.</p>" .
            "<p>Cordialement,<br>La Mairie de Chérif Lo</p>"
        );
        send_notification(
            ADMIN_EMAIL,
            "Nouvelle demande d'état civil reçue — $trackingCode",
            "<p>Une nouvelle demande d'acte(s) d'état civil a été déposée sur le portail.</p>" .
            $recap .
            "<p><strong>Code :</strong> $trackingCode</p>" .
            "<p><strong>Contact :</strong> $email / $telephone</p>" .
            "<p><strong>Paiement :</strong> " . htmlspecialchars($details['mode_paiement']) . " — réf. " . htmlspecialchars($referencePaiement) . "</p>" .
            "<p>Connectez-vous à l'espace mairie pour la traiter.</p>"
        );

        $smsTexte = "Mairie Chérif Lo : nouvelle demande état civil $trackingCode de $prenom $nom, tel $telephone. Actes : $actesLabel";
        send_sms(ADMIN_PHONE_INTL, $smsTexte);
        log_whatsapp_attempt(ADMIN_PHONE_INTL, $smsTexte);
        $whatsappRecapLink = whatsapp_link(ADMIN_PHONE_INTL, $smsTexte);

        $submitted = true;
    }
}

$pageTitle = "Demande d'acte d'état civil — Mairie de Chérif Lo";
$pageDescription = "Formulaire de demande d'acte d'état civil (naissance, mariage, décès, résidence...) auprès de la Mairie de Chérif Lo.";
$activePage = "services.php";
include __DIR__ . "/includes/header.php";
?>

<?php if ($submitted): ?>

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
  $heroEyebrow = "État civil";
  $heroTitle = "Demande d'acte d'état civil";
  $heroDescription = "Extraits, copies littérales, certificats de résidence ou de vie : effectuez votre demande en ligne, paiement mobile à l'appui.";
  include __DIR__ . "/includes/page-hero.php";
  ?>

  <section class="container-page py-16">
    <div class="max-w-3xl mx-auto rounded-3xl border border-primary/20 bg-primary/5 p-6 md:p-8 mb-10">
      <h2 class="font-display text-xl flex items-center gap-2"><i data-lucide="info" class="h-5 w-5 text-primary"></i> Informations importantes</h2>
      <p class="mt-3 text-sm text-foreground/85 leading-relaxed">
        Ce formulaire permet de faire une demande d'acte d'état civil auprès de la Mairie de Chérif Lo.
      </p>
      <p class="mt-3 text-sm text-foreground/85 leading-relaxed">
        Les informations recueillies sont nécessaires au traitement de votre demande et sont protégées conformément à la
        <strong>Loi n°2008-12 du 25 janvier 2008 relative à la protection des données personnelles</strong>.
      </p>
      <p class="mt-3 text-sm text-foreground/85 leading-relaxed">
        Veuillez remplir tous les champs obligatoires (<span class="text-destructive">*</span>) avant de soumettre votre demande.
      </p>
    </div>

    <form method="post" action="etat-civil.php" class="max-w-3xl mx-auto rounded-3xl border border-border bg-card p-8 md:p-10 shadow-soft">
      <?= csrf_field() ?>
      <input type="text" name="site_web" class="hidden" tabindex="-1" autocomplete="off">

      <?php if (!empty($errors)): ?>
        <div class="mb-6 rounded-xl border border-destructive/30 bg-destructive/10 p-4 text-sm text-destructive">
          <ul class="list-disc list-inside space-y-1">
            <?php foreach ($errors as $err): ?><li><?= htmlspecialchars($err) ?></li><?php endforeach; ?>
          </ul>
        </div>
      <?php endif; ?>

      <!-- SECTION 1 -->
      <div class="flex items-center gap-3 mb-2">
        <span class="h-7 w-7 shrink-0 grid place-items-center rounded-full bg-primary text-primary-foreground text-xs font-bold">1</span>
        <h2 class="font-display text-xl">Type d'acte demandé</h2>
      </div>
      <p class="text-sm text-muted-foreground mb-4">Types d'actes demandés <span class="text-destructive">*</span> — vous pouvez sélectionner plusieurs types d'actes dans une même demande.</p>
      <div class="grid sm:grid-cols-2 gap-3">
        <?php foreach ($ETAT_CIVIL_ACTES as $slug => $label): ?>
          <label class="flex items-center gap-3 rounded-xl border border-input bg-background px-4 py-3 text-sm cursor-pointer hover:border-primary/40 transition">
            <input type="checkbox" name="actes[]" value="<?= $slug ?>" class="h-4 w-4 accent-primary" <?= in_array($slug, $selectedActes, true) ? 'checked' : '' ?>>
            <?= htmlspecialchars($label) ?>
          </label>
        <?php endforeach; ?>
      </div>

      <!-- SECTION 2 -->
      <div class="flex items-center gap-3 mt-10 mb-6">
        <span class="h-7 w-7 shrink-0 grid place-items-center rounded-full bg-primary text-primary-foreground text-xs font-bold">2</span>
        <h2 class="font-display text-xl">Informations du demandeur</h2>
      </div>
      <div class="grid md:grid-cols-2 gap-5">
        <label class="text-sm">
          <span class="text-foreground/85">Nom <span class="text-destructive">*</span></span>
          <input name="nom" value="<?= htmlspecialchars($old['nom'] ?? '') ?>" required class="mt-2 w-full rounded-xl border border-input bg-background px-4 py-3 outline-none focus:border-primary">
        </label>
        <label class="text-sm">
          <span class="text-foreground/85">Prénom(s) <span class="text-destructive">*</span></span>
          <input name="prenom" value="<?= htmlspecialchars($old['prenom'] ?? '') ?>" required class="mt-2 w-full rounded-xl border border-input bg-background px-4 py-3 outline-none focus:border-primary">
        </label>
        <label class="text-sm">
          <span class="text-foreground/85">Date de naissance <span class="text-destructive">*</span></span>
          <input type="date" name="date_naissance" value="<?= htmlspecialchars($old['date_naissance'] ?? '') ?>" required class="mt-2 w-full rounded-xl border border-input bg-background px-4 py-3 outline-none focus:border-primary">
        </label>
        <label class="text-sm">
          <span class="text-foreground/85">Lieu de naissance <span class="text-destructive">*</span></span>
          <input name="lieu_naissance" value="<?= htmlspecialchars($old['lieu_naissance'] ?? 'Chérif Lo') ?>" required class="mt-2 w-full rounded-xl border border-input bg-background px-4 py-3 outline-none focus:border-primary">
        </label>
        <label class="text-sm">
          <span class="text-foreground/85">Prénom du père (optionnel)</span>
          <input name="pere_prenom" value="<?= htmlspecialchars($old['pere_prenom'] ?? '') ?>" class="mt-2 w-full rounded-xl border border-input bg-background px-4 py-3 outline-none focus:border-primary">
        </label>
        <label class="text-sm">
          <span class="text-foreground/85">Nom du père (optionnel)</span>
          <input name="pere_nom" value="<?= htmlspecialchars($old['pere_nom'] ?? '') ?>" class="mt-2 w-full rounded-xl border border-input bg-background px-4 py-3 outline-none focus:border-primary">
        </label>
        <label class="text-sm">
          <span class="text-foreground/85">Prénom de la mère (optionnel)</span>
          <input name="mere_prenom" value="<?= htmlspecialchars($old['mere_prenom'] ?? '') ?>" class="mt-2 w-full rounded-xl border border-input bg-background px-4 py-3 outline-none focus:border-primary">
        </label>
        <label class="text-sm">
          <span class="text-foreground/85">Nom de la mère (optionnel)</span>
          <input name="mere_nom" value="<?= htmlspecialchars($old['mere_nom'] ?? '') ?>" class="mt-2 w-full rounded-xl border border-input bg-background px-4 py-3 outline-none focus:border-primary">
        </label>
        <label class="text-sm">
          <span class="text-foreground/85">Année du registre <span class="text-destructive">*</span></span>
          <input name="annee_registre" value="<?= htmlspecialchars($old['annee_registre'] ?? '') ?>" required placeholder="Ex : 1995" class="mt-2 w-full rounded-xl border border-input bg-background px-4 py-3 outline-none focus:border-primary">
        </label>
        <label class="text-sm">
          <span class="text-foreground/85">Numéro dans le registre <span class="text-destructive">*</span></span>
          <input name="numero_registre" value="<?= htmlspecialchars($old['numero_registre'] ?? '') ?>" required class="mt-2 w-full rounded-xl border border-input bg-background px-4 py-3 outline-none focus:border-primary">
        </label>
        <label class="text-sm md:col-span-2">
          <span class="text-foreground/85">Demandeur <span class="text-destructive">*</span></span>
          <select name="qualite_demandeur" required class="mt-2 w-full rounded-xl border border-input bg-background px-4 py-3 outline-none focus:border-primary">
            <option value="">Sélectionnez votre qualité</option>
            <?php foreach ($ETAT_CIVIL_QUALITES as $q): ?>
              <option value="<?= htmlspecialchars($q) ?>" <?= ($old['qualite_demandeur'] ?? '') === $q ? 'selected' : '' ?>><?= htmlspecialchars($q) ?></option>
            <?php endforeach; ?>
          </select>
        </label>
        <label class="text-sm md:col-span-2">
          <span class="text-foreground/85">Adresse actuelle <span class="text-destructive">*</span></span>
          <input name="adresse" value="<?= htmlspecialchars($old['adresse'] ?? '') ?>" required class="mt-2 w-full rounded-xl border border-input bg-background px-4 py-3 outline-none focus:border-primary">
        </label>
        <label class="text-sm">
          <span class="text-foreground/85">Téléphone <span class="text-destructive">*</span></span>
          <input name="telephone" value="<?= htmlspecialchars($old['telephone'] ?? '') ?>" required class="mt-2 w-full rounded-xl border border-input bg-background px-4 py-3 outline-none focus:border-primary">
        </label>
        <label class="text-sm">
          <span class="text-foreground/85">Adresse e-mail <span class="text-destructive">*</span></span>
          <input type="email" name="email" value="<?= htmlspecialchars($old['email'] ?? '') ?>" required class="mt-2 w-full rounded-xl border border-input bg-background px-4 py-3 outline-none focus:border-primary">
        </label>
      </div>

      <!-- SECTION 3 -->
      <div class="flex items-center gap-3 mt-10 mb-6">
        <span class="h-7 w-7 shrink-0 grid place-items-center rounded-full bg-primary text-primary-foreground text-xs font-bold">3</span>
        <h2 class="font-display text-xl">Mode de délivrance et paiement</h2>
      </div>
      <div class="grid md:grid-cols-2 gap-5">
        <label class="text-sm">
          <span class="text-foreground/85">Mode de délivrance souhaité <span class="text-destructive">*</span></span>
          <select name="mode_delivrance" required class="mt-2 w-full rounded-xl border border-input bg-background px-4 py-3 outline-none focus:border-primary">
            <option value="">Sélectionnez le mode de délivrance</option>
            <?php foreach ($ETAT_CIVIL_MODES_DELIVRANCE as $m): ?>
              <option value="<?= htmlspecialchars($m) ?>" <?= ($old['mode_delivrance'] ?? '') === $m ? 'selected' : '' ?>><?= htmlspecialchars($m) ?></option>
            <?php endforeach; ?>
          </select>
        </label>
        <label class="text-sm">
          <span class="text-foreground/85">Mode de paiement <span class="text-destructive">*</span></span>
          <select name="mode_paiement" required class="mt-2 w-full rounded-xl border border-input bg-background px-4 py-3 outline-none focus:border-primary">
            <option value="">Sélectionnez le mode de paiement</option>
            <?php foreach ($ETAT_CIVIL_MODES_PAIEMENT as $slug => $label): ?>
              <option value="<?= $slug ?>" <?= ($old['mode_paiement'] ?? '') === $slug ? 'selected' : '' ?>><?= htmlspecialchars($label) ?></option>
            <?php endforeach; ?>
          </select>
        </label>
      </div>

      <div class="mt-5 rounded-2xl border border-border bg-secondary/60 p-5">
        <div class="text-sm font-medium text-foreground/85 mb-3">Informations de paiement</div>
        <div class="grid sm:grid-cols-2 gap-4">
          <div class="rounded-xl bg-card border border-border p-4">
            <div class="text-sm font-semibold text-primary">Wave</div>
            <div class="mt-1 text-sm text-muted-foreground">Numéro : <?= htmlspecialchars($ETAT_CIVIL_PAIEMENT_NUMEROS['wave']) ?></div>
          </div>
          <div class="rounded-xl bg-card border border-border p-4">
            <div class="text-sm font-semibold text-primary">Orange Money (OM)</div>
            <div class="mt-1 text-sm text-muted-foreground">Numéro : <?= htmlspecialchars($ETAT_CIVIL_PAIEMENT_NUMEROS['orange_money']) ?></div>
          </div>
        </div>
        <p class="mt-3 text-xs text-muted-foreground"><strong>Important :</strong> effectuez le paiement et conservez la référence de transaction.</p>
        <label class="mt-4 text-sm block">
          <span class="text-foreground/85">Référence de la transaction <span class="text-destructive">*</span></span>
          <input name="reference_paiement" value="<?= htmlspecialchars($old['reference_paiement'] ?? '') ?>" required placeholder="Ex : WAVE-XXXXXXXX" class="mt-2 w-full rounded-xl border border-input bg-background px-4 py-3 outline-none focus:border-primary">
        </label>
      </div>

      <!-- CONSENTEMENTS -->
      <div class="mt-10">
        <h2 class="font-display text-xl mb-4">Consentements et validation</h2>
        <div class="space-y-3">
          <label class="flex items-start gap-3 rounded-xl border border-input bg-background p-4 text-sm cursor-pointer">
            <input type="checkbox" name="consent_donnees" value="1" required class="mt-0.5 h-4 w-4 accent-primary" <?= !empty($old['consent_donnees']) ? 'checked' : '' ?>>
            <span>
              Je donne mon consentement libre et éclairé au traitement de mes données personnelles.
              <span class="block text-xs text-muted-foreground mt-1">En soumettant ce formulaire, je consens à la collecte et au traitement de mes données personnelles destinées exclusivement au traitement de cette présente demande.</span>
            </span>
          </label>
          <label class="flex items-start gap-3 rounded-xl border border-input bg-background p-4 text-sm cursor-pointer">
            <input type="checkbox" name="consent_responsabilite" value="1" required class="mt-0.5 h-4 w-4 accent-primary" <?= !empty($old['consent_responsabilite']) ? 'checked' : '' ?>>
            <span>
              J'ai lu, compris et accepte la clause de non-responsabilité de la Mairie de Chérif Lo.
              <span class="block text-xs text-muted-foreground mt-1">La Mairie de Chérif Lo s'engage à protéger la confidentialité et la sécurité des données collectées.</span>
            </span>
          </label>
        </div>
      </div>

      <button type="submit" class="mt-8 inline-flex items-center gap-2 rounded-full bg-primary text-primary-foreground px-6 py-3.5 text-sm font-semibold hover:bg-primary-deep transition shadow-elegant">
        Soumettre ma demande <i data-lucide="send" class="h-4 w-4"></i>
      </button>
    </form>

    <!-- FAQ -->
    <div class="max-w-3xl mx-auto mt-16">
      <h2 class="font-display text-2xl mb-6">Questions Fréquentes</h2>
      <div class="space-y-3">
        <?php
        $faq = [
            ["Comment obtenir un extrait ou une copie littérale ?", "Remplissez ce formulaire en cochant l'acte souhaité, ou présentez-vous au guichet état civil muni d'une pièce d'identité. Délai indicatif : 24 à 72h."],
            ["Comment obtenir un certificat de mariage ?", "Cochez « Extrait d'acte de mariage » dans ce formulaire, ou présentez-vous au guichet avec les références du mariage (année et numéro d'acte si connus)."],
            ["Comment déclarer une naissance ?", "La déclaration doit être faite dans les 30 jours suivant la naissance, au service état civil, munie du certificat d'accouchement et des pièces d'identité des parents."],
            ["Comment obtenir un certificat de décès ?", "Cochez « Certificat de décès » ci-dessus, en indiquant si possible l'année et le numéro d'enregistrement de l'acte."],
            ["Comment obtenir un certificat de vie individuelle ?", "Cochez l'option correspondante dans ce formulaire. Ce certificat atteste que vous êtes en vie à la date de délivrance ; il est généralement valable 3 mois."],
            ["Comment obtenir un certificat de vie collective ?", "Même démarche que le certificat de vie individuelle, mais pour un groupe ou un foyer (utile notamment pour les pensions de réversion)."],
            ["Comment obtenir un certificat de résidence ?", "Cochez « Certificat de résidence » et indiquez votre adresse actuelle. Une preuve de domicile peut vous être demandée au guichet."],
            ["Comment obtenir un certificat de non inscription de naissance ?", "Ce document, qui atteste qu'aucun acte de naissance n'a été enregistré pour la personne concernée, se demande directement au guichet état civil."],
            ["Comment obtenir un certificat de non inscription de mariage ?", "Disponible au guichet état civil, sur présentation d'une pièce d'identité."],
            ["Comment obtenir un certificat de non divorce/non remariage ?", "Délivré au guichet état civil sur demande motivée, munie d'une pièce d'identité."],
            ["Quels sont les horaires de dépôt et de retrait de document d'État civil ?", "Lundi – Vendredi : 08h–17h. Samedi : 09h–13h. Fermé le dimanche."],
            ["Comment faire une reconstitution d'acte d'état civil ?", "En cas de perte des registres, une procédure judiciaire de reconstitution est nécessaire. Contactez le service état civil pour être orienté."],
            ["Comment faire une annulation d'acte d'état civil ?", "Une annulation ne peut être prononcée que par décision de justice. Le service état civil peut vous orienter vers le tribunal compétent."],
            ["Comment faire pour avoir un acte en distanciel ?", "En remplissant ce formulaire en ligne, avec paiement par Wave ou Orange Money, puis retrait au guichet ou envoi par e-mail/courrier selon l'option choisie."],
        ];
        foreach ($faq as [$q, $a]): ?>
          <details class="group rounded-xl border border-border bg-card px-5 py-4">
            <summary class="flex items-center justify-between cursor-pointer text-sm font-medium text-foreground/90 list-none">
              <?= htmlspecialchars($q) ?>
              <i data-lucide="chevron-down" class="h-4 w-4 text-muted-foreground group-open:rotate-180 transition-transform"></i>
            </summary>
            <p class="mt-3 text-sm text-muted-foreground leading-relaxed"><?= htmlspecialchars($a) ?></p>
          </details>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

<?php endif; ?>

<?php include __DIR__ . "/includes/footer.php"; ?>
