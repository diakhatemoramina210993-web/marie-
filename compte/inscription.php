<?php
require __DIR__ . '/../includes/citizen-auth.php';
require __DIR__ . '/../includes/db.php';
require __DIR__ . '/../includes/functions.php';
require __DIR__ . '/../includes/csrf.php';

if (!empty($_SESSION['citoyen_id'])) {
    header('Location: index.php');
    exit;
}

$redirect = $_GET['redirect'] ?? $_POST['redirect'] ?? '';
$errors = [];
$old = $_POST;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) {
        $errors[] = "Session expirée, merci de réessayer.";
    }
    $nom = trim($_POST['nom'] ?? '');
    $prenom = trim($_POST['prenom'] ?? '');
    $telephone = trim($_POST['telephone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($nom === '') $errors[] = "Le nom est requis.";
    if ($prenom === '') $errors[] = "Le prénom est requis.";
    if ($telephone === '') $errors[] = "Le contact (téléphone) est requis.";
    if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "L'adresse e-mail est invalide.";
    if (strlen($password) < 6) $errors[] = "Le mot de passe doit contenir au moins 6 caractères.";

    $telephoneNorm = normalize_senegal_phone($telephone);

    if (empty($errors)) {
        $check = $pdo->prepare("SELECT id FROM citoyens WHERE telephone = :tel");
        $check->execute(['tel' => $telephoneNorm]);
        if ($check->fetch()) {
            $errors[] = "Un compte existe déjà avec ce numéro de contact.";
        }
    }
    if (empty($errors) && $email !== '') {
        $checkEmail = $pdo->prepare("SELECT id FROM citoyens WHERE email = :email");
        $checkEmail->execute(['email' => $email]);
        if ($checkEmail->fetch()) {
            $errors[] = "Un compte existe déjà avec cette adresse e-mail.";
        }
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare(
            "INSERT INTO citoyens (nom, prenom, telephone, email, password_hash) VALUES (:nom, :prenom, :telephone, :email, :hash)"
        );
        $stmt->execute([
            'nom' => $nom,
            'prenom' => $prenom,
            'telephone' => $telephoneNorm,
            'email' => $email !== '' ? $email : null,
            'hash' => password_hash($password, PASSWORD_DEFAULT),
        ]);

        session_regenerate_id(true);
        $_SESSION['citoyen_id'] = (int) $pdo->lastInsertId();

        $smsTexte = "Mairie Chérif Lo : bienvenue $prenom $nom ! Votre compte citoyen a été créé avec succès.";
        send_sms($telephoneNorm, $smsTexte);

        if ($email !== '') {
            send_notification(
                $email,
                "Bienvenue sur le portail de la Mairie de Chérif Lo",
                "<p>Bonjour $prenom $nom,</p>" .
                "<p>Votre compte citoyen a bien été créé. Vous pouvez désormais déposer vos demandes de documents et vos rendez-vous, et retrouver l'historique de vos démarches à tout moment depuis « Mon espace ».</p>" .
                "<p>Cordialement,<br>La Mairie de Chérif Lo</p>"
            );
        }

        header('Location: ' . ($redirect !== '' ? $redirect : 'index.php'));
        exit;
    }
}

?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Créer un compte — Mairie de Chérif Lo</title>
<meta name="description" content="Créez votre compte citoyen pour effectuer vos démarches en ligne et suivre leur avancement.">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap">
<script src="https://cdn.tailwindcss.com"></script>
<script>
  tailwind.config = {
    theme: { extend: {
      fontFamily: { display: ['Inter','ui-sans-serif','system-ui','sans-serif'], sans: ['Inter','ui-sans-serif','system-ui','sans-serif'] },
      colors: {
        background: 'rgb(249 250 251 / <alpha-value>)',
        foreground: 'rgb(17 24 39 / <alpha-value>)',
        card: 'rgb(255 255 255 / <alpha-value>)',
        primary: 'rgb(29 127 72 / <alpha-value>)',
        'primary-foreground': 'rgb(255 255 255 / <alpha-value>)',
        'primary-deep': 'rgb(21 99 56 / <alpha-value>)',
        muted: 'rgb(243 244 246 / <alpha-value>)',
        'muted-foreground': 'rgb(107 114 128 / <alpha-value>)',
        accent: 'rgb(244 233 66 / <alpha-value>)',
        'accent-foreground': 'rgb(21 99 56 / <alpha-value>)',
        destructive: 'rgb(220 38 38 / <alpha-value>)',
        border: 'rgb(229 231 235 / <alpha-value>)',
        input: 'rgb(229 231 235 / <alpha-value>)',
      },
    } },
  };
</script>
<link rel="stylesheet" href="/mairie/assets/css/custom.css">
<script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js"></script>
</head>
<body class="min-h-screen bg-background text-foreground font-sans">

<div class="grid lg:grid-cols-2 min-h-screen">
  <!-- LEFT: form -->
  <div class="flex flex-col px-6 md:px-16 py-8 relative">
    <div class="flex items-center justify-between">
      <a href="/mairie/index.php" class="flex items-center gap-2.5">
        <img src="/mairie/assets/img/logo.jpg" alt="Mairie de Chérif Lo" class="h-10 w-10 rounded-full object-cover ring-1 ring-primary/20">
        <span class="font-display font-bold text-primary-deep">Mairie de Chérif Lo</span>
      </a>
      <a href="/mairie/index.php" class="hidden sm:inline-flex items-center gap-2 rounded-full border border-border px-4 py-2 text-xs font-medium text-foreground/70 hover:border-primary/40 hover:text-primary transition">
        <i data-lucide="arrow-left" class="h-3.5 w-3.5"></i> Retour sur la page d'accueil
      </a>
    </div>

    <div class="flex-1 flex items-center">
      <div class="w-full max-w-md mx-auto py-10">
        <div class="rounded-3xl border border-border bg-card shadow-elegant p-7 md:p-8">
          <div class="text-sm italic text-primary font-medium">« Rejoignez le portail de Chérif Lo »</div>
          <h1 class="mt-2 font-display font-extrabold text-2xl">Créer votre compte citoyen</h1>
          <p class="mt-1 text-sm text-muted-foreground">Un compte unique pour toutes vos démarches auprès de la mairie.</p>

          <?php if ($redirect): ?>
            <div class="mt-5 rounded-xl border border-primary/30 bg-primary/10 p-3 text-sm text-primary">
              Un compte citoyen est nécessaire pour déposer une demande ou prendre rendez-vous. Créez-le en 1 minute, c'est gratuit.
            </div>
          <?php endif; ?>
          <?php if (!empty($errors)): ?>
            <div class="mt-5 rounded-xl border border-destructive/30 bg-destructive/10 p-3 text-sm text-destructive">
              <ul class="list-disc list-inside space-y-1">
                <?php foreach ($errors as $err): ?><li><?= htmlspecialchars($err) ?></li><?php endforeach; ?>
              </ul>
            </div>
          <?php endif; ?>

          <form method="post" action="inscription.php<?= $redirect ? '?redirect=' . urlencode($redirect) : '' ?>" class="mt-6" id="signup-form">
            <?= csrf_field() ?>
            <input type="hidden" name="redirect" value="<?= htmlspecialchars($redirect) ?>">

            <div class="grid sm:grid-cols-2 gap-4">
              <label class="text-sm">
                <span class="text-foreground/85">Prénom <span class="text-destructive">*</span></span>
                <input name="prenom" value="<?= htmlspecialchars($old['prenom'] ?? '') ?>" required class="mt-2 w-full rounded-xl border border-input bg-background px-4 py-3 outline-none focus:border-primary">
              </label>
              <label class="text-sm">
                <span class="text-foreground/85">Nom <span class="text-destructive">*</span></span>
                <input name="nom" value="<?= htmlspecialchars($old['nom'] ?? '') ?>" required class="mt-2 w-full rounded-xl border border-input bg-background px-4 py-3 outline-none focus:border-primary">
              </label>
              <label class="text-sm sm:col-span-2">
                <span class="text-foreground/85">Numéro de téléphone <span class="text-destructive">*</span></span>
                <div class="mt-2 flex rounded-xl border border-input bg-background overflow-hidden focus-within:border-primary">
                  <span class="flex items-center gap-1.5 px-3 border-r border-input bg-secondary/60 text-sm text-foreground/70 select-none">
                    <span aria-hidden="true">🇸🇳</span> +221
                  </span>
                  <input name="telephone" value="<?= htmlspecialchars($old['telephone'] ?? '') ?>" required placeholder="70 123 45 67" class="w-full px-4 py-3 outline-none bg-transparent">
                </div>
              </label>
              <label class="text-sm sm:col-span-2">
                <span class="text-foreground/85">E-mail <span class="text-muted-foreground font-normal">(optionnel)</span></span>
                <input type="email" name="email" value="<?= htmlspecialchars($old['email'] ?? '') ?>" placeholder="vous@exemple.com" class="mt-2 w-full rounded-xl border border-input bg-background px-4 py-3 outline-none focus:border-primary">
                <span class="mt-1 block text-xs text-muted-foreground">Pour recevoir vos accusés de réception par e-mail en plus du SMS.</span>
              </label>
              <label class="text-sm sm:col-span-2">
                <span class="text-foreground/85">Mot de passe <span class="text-destructive">*</span></span>
                <div class="relative mt-2">
                  <input type="password" name="password" id="password-field" required minlength="6" class="w-full rounded-xl border border-input bg-background px-4 py-3 pr-11 outline-none focus:border-primary">
                  <button type="button" id="toggle-password" aria-label="Afficher le mot de passe" class="absolute inset-y-0 right-0 px-3 flex items-center text-muted-foreground hover:text-foreground">
                    <i data-lucide="eye" class="h-4 w-4"></i>
                  </button>
                </div>
              </label>
            </div>

            <button type="submit" id="signup-submit" class="mt-6 w-full rounded-full bg-primary text-primary-foreground px-6 py-3.5 text-sm font-semibold hover:bg-primary-deep transition shadow-elegant disabled:opacity-40 disabled:cursor-not-allowed disabled:hover:bg-primary">
              Créer mon compte
            </button>
          </form>

          <p class="mt-6 text-center text-sm text-muted-foreground">
            Déjà un compte ?
            <a href="connexion.php<?= $redirect ? '?redirect=' . urlencode($redirect) : '' ?>" class="text-primary font-medium hover:text-primary-deep">Se connecter</a>
          </p>
        </div>
        <p class="mt-4 text-center text-xs text-muted-foreground">
          Vous êtes agent de la mairie ?
          <a href="/mairie/admin/login.php" class="text-primary font-medium hover:text-primary-deep">Espace Mairie</a>
        </p>
      </div>
    </div>
  </div>

  <!-- RIGHT: visual panel -->
  <div class="hidden lg:block relative overflow-hidden bg-gradient-hero">
    <div class="absolute inset-0 opacity-30">
      <img src="/mairie/assets/img/hero-cherif-lo.jpg" alt="" class="h-full w-full object-cover">
    </div>
    <div class="absolute -right-32 -top-32 h-96 w-96 rounded-full bg-white/5"></div>
    <div class="absolute -left-24 bottom-0 h-72 w-72 rounded-full bg-accent/10"></div>
    <div class="relative h-full flex flex-col items-center justify-center text-center px-16 text-primary-foreground">
      <div class="h-20 w-20 rounded-full bg-white/10 backdrop-blur-sm border border-white/20 grid place-items-center mb-6">
        <i data-lucide="user-plus" class="h-9 w-9 text-accent"></i>
      </div>
      <h2 class="font-display font-extrabold text-3xl text-balance">Rejoignez le portail citoyen</h2>
      <p class="mt-3 max-w-sm text-primary-foreground/80">Créez votre compte en une minute et accédez à toutes les démarches de la Mairie de Chérif Lo depuis un seul endroit.</p>

      <div class="mt-10 w-full max-w-xs space-y-3 text-left">
        <div class="flex items-center gap-3 rounded-2xl bg-white/10 backdrop-blur-sm border border-white/15 px-4 py-3">
          <i data-lucide="file-text" class="h-5 w-5 text-accent shrink-0"></i>
          <span class="text-sm">Actes d'état civil en ligne</span>
        </div>
        <div class="flex items-center gap-3 rounded-2xl bg-white/10 backdrop-blur-sm border border-white/15 px-4 py-3">
          <i data-lucide="calendar-check-2" class="h-5 w-5 text-accent shrink-0"></i>
          <span class="text-sm">Rendez-vous avec la mairie</span>
        </div>
        <div class="flex items-center gap-3 rounded-2xl bg-white/10 backdrop-blur-sm border border-white/15 px-4 py-3">
          <i data-lucide="layout-dashboard" class="h-5 w-5 text-accent shrink-0"></i>
          <span class="text-sm">Historique de toutes vos démarches</span>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
  lucide.createIcons();
  (function () {
    function wireToggle(toggleId, fieldId) {
      var toggle = document.getElementById(toggleId);
      var field = document.getElementById(fieldId);
      if (!toggle || !field) return;
      toggle.addEventListener('click', function () {
        var showing = field.type === 'text';
        field.type = showing ? 'password' : 'text';
        toggle.innerHTML = showing ? '<i data-lucide="eye" class="h-4 w-4"></i>' : '<i data-lucide="eye-off" class="h-4 w-4"></i>';
        lucide.createIcons();
      });
    }
    wireToggle('toggle-password', 'password-field');
  })();
  (function () {
    var form = document.getElementById('signup-form');
    var submit = document.getElementById('signup-submit');
    if (!form || !submit) return;
    function sync() {
      submit.disabled = !form.checkValidity();
    }
    form.addEventListener('input', sync);
    sync();
  })();
</script>
</body>
</html>
