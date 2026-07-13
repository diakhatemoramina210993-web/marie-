<?php
require __DIR__ . '/includes/auth.php';
require __DIR__ . '/../includes/db.php';
require __DIR__ . '/../includes/csrf.php';

if (!empty($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) {
        $error = "Votre navigateur n'a pas renvoyé le cookie de session (souvent parce que les cookies sont bloqués, ou que la page était ouverte depuis trop longtemps). Vérifiez que les cookies sont autorisés pour ce site, puis réessayez ci-dessous.";
    } else {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        $stmt = $pdo->prepare("SELECT * FROM admin_users WHERE username = :u");
        $stmt->execute(['u' => $username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            session_regenerate_id(true);
            $_SESSION['admin_id'] = $user['id'];
            $_SESSION['admin_name'] = $user['nom_complet'];
            header('Location: index.php');
            exit;
        }
        $error = "Identifiant ou mot de passe incorrect. Identifiant : admin — attention aux majuscules dans le mot de passe.";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Connexion — Espace Mairie</title>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap">
<script src="https://cdn.tailwindcss.com"></script>
<script>
  tailwind.config = { theme: { extend: { fontFamily: {
    display: ['Inter', 'ui-sans-serif', 'system-ui', 'sans-serif'],
    sans: ['Inter', 'ui-sans-serif', 'system-ui', 'sans-serif'],
  }, colors: {
    background: 'rgb(249 250 251 / <alpha-value>)',
    foreground: 'rgb(17 24 39 / <alpha-value>)',
    card: 'rgb(255 255 255 / <alpha-value>)',
    primary: 'rgb(29 127 72 / <alpha-value>)',
    'primary-foreground': 'rgb(255 255 255 / <alpha-value>)',
    'primary-deep': 'rgb(21 99 56 / <alpha-value>)',
    muted: 'rgb(243 244 246 / <alpha-value>)',
    'muted-foreground': 'rgb(107 114 128 / <alpha-value>)',
    destructive: 'rgb(220 38 38 / <alpha-value>)',
    border: 'rgb(229 231 235 / <alpha-value>)',
    input: 'rgb(229 231 235 / <alpha-value>)',
  } } } };
</script>
<link rel="stylesheet" href="/cahier-lumineux-php/assets/css/custom.css">
</head>
<body class="min-h-screen bg-gradient-hero flex flex-col items-center justify-center px-4 py-10 font-sans">
  <div class="w-full max-w-sm mb-6 grid grid-cols-3 rounded-full border border-white/25 bg-white/10 p-1 text-sm font-medium">
    <a href="/cahier-lumineux-php/compte/inscription.php" class="rounded-full py-2.5 text-center text-primary-foreground/80 hover:text-white transition">Créer un compte</a>
    <a href="/cahier-lumineux-php/compte/connexion.php" class="rounded-full py-2.5 text-center text-primary-foreground/80 hover:text-white transition">Se connecter</a>
    <span class="rounded-full bg-card text-primary py-2.5 text-center shadow-soft">Espace Mairie</span>
  </div>
  <form method="post" action="login.php" class="w-full max-w-sm rounded-3xl bg-card p-8 shadow-elegant">
    <?= csrf_field() ?>
    <div class="text-xs uppercase tracking-widest text-primary/70">Mairie de Chérif Lo</div>
    <h1 class="mt-2 font-display text-2xl text-foreground">Espace Mairie</h1>
    <p class="mt-1 text-sm text-muted-foreground">Connexion réservée au personnel habilité.</p>

    <?php if ($error): ?>
      <div class="mt-5 rounded-xl border border-destructive/30 bg-destructive/10 p-4 text-sm text-destructive leading-relaxed"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <label class="mt-6 block text-sm">
      <span class="text-foreground/85">Identifiant</span>
      <input name="username" required autofocus class="mt-2 w-full rounded-xl border border-input bg-background px-4 py-3 outline-none focus:border-primary">
    </label>
    <label class="mt-4 block text-sm">
      <span class="text-foreground/85">Mot de passe</span>
      <input type="password" name="password" required class="mt-2 w-full rounded-xl border border-input bg-background px-4 py-3 outline-none focus:border-primary">
    </label>

    <button type="submit" class="mt-6 w-full rounded-full bg-primary text-primary-foreground px-6 py-3 text-sm font-semibold hover:bg-primary-deep transition">
      Se connecter
    </button>
    <a href="/cahier-lumineux-php/index.php" class="mt-4 block text-center text-xs text-muted-foreground hover:text-primary">← Retour au site</a>
  </form>
</body>
</html>
