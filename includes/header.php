<?php
/**
 * Shared header: <head> + site header/nav.
 * Expects (optionally) these vars set before include:
 *   $pageTitle, $pageDescription, $activePage
 */
require_once __DIR__ . '/session.php';
ensure_session_started();

$pageTitle = $pageTitle ?? "Mairie de Chérif Lo — Plateforme officielle";
$pageDescription = $pageDescription ?? "Site officiel de la Mairie de Chérif Lo (Tivaouane, Pambal) : services aux usagers, état civil, actualités, projets et démarches en ligne.";
$activePage = $activePage ?? "";
$base = "/mairie";

$navItems = [
    "index.php" => "Accueil",
    "mairie.php" => "La Mairie",
    "services.php" => "Services",
    "actualites.php" => "Actualités",
    "affaires-sociales.php" => "Affaires Sociales",
    "mediatheque.php" => "Médiathèque",
];

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/settings.php';
require_once __DIR__ . '/citizen-auth.php';
$__citoyenConnecte = current_citoyen($pdo);
$SETTINGS = get_all_settings($pdo);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= htmlspecialchars($pageTitle) ?></title>
<meta name="description" content="<?= htmlspecialchars($pageDescription) ?>">
<meta name="author" content="Mairie de Chérif Lo">
<meta property="og:title" content="<?= htmlspecialchars($pageTitle) ?>">
<meta property="og:description" content="<?= htmlspecialchars($pageDescription) ?>">
<meta property="og:type" content="website">
<meta name="twitter:card" content="summary_large_image">
<link rel="icon" href="/cahier-lumineux-php/favicon.ico" type="image/x-icon">

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap">

<script src="https://cdn.tailwindcss.com"></script>
<script>
  tailwind.config = {
    theme: {
      extend: {
        fontFamily: {
          display: ['Inter', 'ui-sans-serif', 'system-ui', 'sans-serif'],
          sans: ['Inter', 'ui-sans-serif', 'system-ui', 'sans-serif'],
        },
        colors: {
          background: 'rgb(249 250 251 / <alpha-value>)',
          foreground: 'rgb(17 24 39 / <alpha-value>)',
          card: 'rgb(255 255 255 / <alpha-value>)',
          'card-foreground': 'rgb(17 24 39 / <alpha-value>)',
          primary: 'rgb(29 127 72 / <alpha-value>)',
          'primary-foreground': 'rgb(255 255 255 / <alpha-value>)',
          'primary-deep': 'rgb(21 99 56 / <alpha-value>)',
          secondary: 'rgb(243 244 246 / <alpha-value>)',
          'secondary-foreground': 'rgb(21 99 56 / <alpha-value>)',
          muted: 'rgb(243 244 246 / <alpha-value>)',
          'muted-foreground': 'rgb(107 114 128 / <alpha-value>)',
          accent: 'rgb(244 233 66 / <alpha-value>)',
          'accent-foreground': 'rgb(21 99 56 / <alpha-value>)',
          gold: 'rgb(244 233 66 / <alpha-value>)',
          destructive: 'rgb(220 38 38 / <alpha-value>)',
          border: 'rgb(229 231 235 / <alpha-value>)',
          input: 'rgb(229 231 235 / <alpha-value>)',
          ring: 'rgb(29 127 72 / <alpha-value>)',
        },
        borderRadius: {
          sm: 'calc(0.75rem - 4px)',
          md: 'calc(0.75rem - 2px)',
          lg: '0.75rem',
          xl: 'calc(0.75rem + 4px)',
          '2xl': 'calc(0.75rem + 8px)',
          '3xl': 'calc(0.75rem + 12px)',
        },
      },
    },
  };
</script>
<link rel="stylesheet" href="/cahier-lumineux-php/assets/css/custom.css">
<script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js"></script>
</head>
<body class="min-h-screen flex flex-col bg-background text-foreground font-sans">

<header class="sticky top-0 z-40 border-b border-border/60 bg-background/85 backdrop-blur-xl">
  <div class="container-page flex h-20 items-center justify-between gap-6">
    <a href="<?= $base ?>/index.php" class="flex items-center gap-3 group">
      <img src="/cahier-lumineux-php/assets/img/logo.jpg" alt="Armoiries de la Mairie de Chérif Lo" class="h-12 w-12 rounded-full object-cover ring-1 ring-primary/20">
      <div class="leading-tight">
        <div class="font-display text-lg text-primary-deep">Mairie de Chérif Lo</div>
      </div>
    </a>

    <nav class="hidden lg:flex items-center gap-1">
      <?php foreach ($navItems as $short => $label):
        $isActive = $activePage === $short; ?>
        <a href="<?= $base ?>/<?= $short ?>"
           class="px-3 py-2 text-sm font-medium rounded-md hover:text-primary hover:bg-secondary transition-colors <?= $isActive ? 'text-primary bg-secondary' : 'text-foreground/75' ?>">
          <?= $label ?>
        </a>
      <?php endforeach; ?>
    </nav>

    <div class="hidden lg:flex items-center gap-3">
      <div class="relative">
        <button id="user-menu-toggle" class="flex items-center gap-2 rounded-full border border-border pl-3 pr-2.5 py-2 text-sm font-medium text-foreground/80 hover:border-primary/40 hover:text-primary transition-colors">
          <span class="h-6 w-6 grid place-items-center rounded-full bg-primary/10 text-primary">
            <i data-lucide="user" class="h-3.5 w-3.5"></i>
          </span>
          <?= $__citoyenConnecte ? htmlspecialchars($__citoyenConnecte['prenom']) : 'Connexion' ?>
          <i data-lucide="chevron-down" class="h-3.5 w-3.5 text-foreground/50"></i>
        </button>
        <div id="user-menu-dropdown" class="hidden absolute right-0 top-[calc(100%+0.5rem)] w-60 rounded-2xl border border-border bg-card shadow-elegant overflow-hidden">
          <?php if ($__citoyenConnecte): ?>
            <div class="px-4 py-3 border-b border-border">
              <div class="text-sm font-medium truncate"><?= htmlspecialchars($__citoyenConnecte['prenom'] . ' ' . $__citoyenConnecte['nom']) ?></div>
              <div class="text-xs text-muted-foreground truncate"><?= htmlspecialchars($__citoyenConnecte['email']) ?></div>
            </div>
            <a href="<?= $base ?>/compte/index.php" class="flex items-center gap-2.5 px-4 py-3 text-sm hover:bg-secondary transition">
              <i data-lucide="layout-dashboard" class="h-4 w-4 text-primary"></i> Mon espace
            </a>
            <a href="<?= $base ?>/compte/deconnexion.php" class="flex items-center gap-2.5 px-4 py-3 text-sm hover:bg-secondary transition border-t border-border">
              <i data-lucide="log-out" class="h-4 w-4 text-muted-foreground"></i> Déconnexion
            </a>
          <?php else: ?>
            <a href="<?= $base ?>/compte/inscription.php" class="flex items-center gap-2.5 px-4 py-3 text-sm font-medium text-primary hover:bg-secondary transition">
              <i data-lucide="user-plus" class="h-4 w-4"></i> Créer un compte
            </a>
            <a href="<?= $base ?>/compte/connexion.php" class="flex items-center gap-2.5 px-4 py-3 text-sm hover:bg-secondary transition border-t border-border">
              <i data-lucide="log-in" class="h-4 w-4 text-muted-foreground"></i> Se connecter
            </a>
          <?php endif; ?>
          <a href="<?= $base ?>/admin/login.php" class="flex items-center gap-2.5 px-4 py-3 text-sm text-muted-foreground hover:bg-secondary transition border-t border-border">
            <i data-lucide="shield" class="h-4 w-4"></i> Espace Mairie (agents)
          </a>
        </div>
      </div>
      <a href="<?= $base ?>/services.php" class="rounded-full bg-primary text-primary-foreground px-5 py-2.5 text-sm font-medium shadow-soft hover:bg-primary-deep transition-colors">
        Démarches en ligne
      </a>
    </div>

    <button id="menu-toggle" class="lg:hidden p-2 rounded-md hover:bg-secondary" aria-label="Menu">
      <i data-lucide="menu" class="h-5 w-5" id="icon-menu"></i>
      <i data-lucide="x" class="h-5 w-5 hidden" id="icon-close"></i>
    </button>
  </div>

  <div id="mobile-menu" class="hidden lg:hidden border-t border-border bg-background">
    <div class="container-page py-3 flex flex-col">
      <?php foreach ($navItems as $short => $label):
        $isActive = $activePage === $short; ?>
        <a href="<?= $base ?>/<?= $short ?>"
           class="px-2 py-3 text-sm font-medium border-b border-border/50 last:border-0 <?= $isActive ? 'text-primary' : 'text-foreground/80' ?>">
          <?= $label ?>
        </a>
      <?php endforeach; ?>
      <a href="<?= $base ?>/services.php" class="mt-3 rounded-full bg-primary text-primary-foreground px-5 py-3 text-sm font-semibold text-center">Démarches en ligne</a>
      <div class="mt-4 pt-4 border-t border-border">
        <?php if ($__citoyenConnecte): ?>
          <div class="px-2 pb-2 text-xs uppercase tracking-widest text-muted-foreground">Connecté en tant que <?= htmlspecialchars($__citoyenConnecte['prenom']) ?></div>
          <a href="<?= $base ?>/compte/index.php" class="block px-2 py-2.5 text-sm font-medium text-foreground/80">Mon espace</a>
          <a href="<?= $base ?>/compte/deconnexion.php" class="block px-2 py-2.5 text-sm font-medium text-foreground/80">Déconnexion</a>
        <?php else: ?>
          <a href="<?= $base ?>/compte/inscription.php" class="block px-2 py-2.5 text-sm font-medium text-primary">Créer un compte</a>
          <a href="<?= $base ?>/compte/connexion.php" class="block px-2 py-2.5 text-sm font-medium text-foreground/80">Se connecter</a>
        <?php endif; ?>
      </div>
    </div>
  </div>
</header>

<script>
  (function () {
    var toggle = document.getElementById('menu-toggle');
    var menu = document.getElementById('mobile-menu');
    var iconMenu = document.getElementById('icon-menu');
    var iconClose = document.getElementById('icon-close');
    toggle.addEventListener('click', function () {
      menu.classList.toggle('hidden');
      iconMenu.classList.toggle('hidden');
      iconClose.classList.toggle('hidden');
    });
  })();
  (function () {
    var toggle = document.getElementById('user-menu-toggle');
    var dropdown = document.getElementById('user-menu-dropdown');
    if (!toggle || !dropdown) return;
    toggle.addEventListener('click', function (e) {
      e.stopPropagation();
      dropdown.classList.toggle('hidden');
    });
    document.addEventListener('click', function (e) {
      if (!dropdown.contains(e.target) && e.target !== toggle) {
        dropdown.classList.add('hidden');
      }
    });
  })();
</script>

<main class="flex-1">
