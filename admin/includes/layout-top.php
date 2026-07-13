<?php
/**
 * Chrome partagé de l'espace mairie (back-office).
 * Expects $pageTitle, $activeAdmin ('dashboard'|'demandes'|'rendezvous'|'messages').
 */
$pageTitle = $pageTitle ?? "Espace Mairie";
$activeAdmin = $activeAdmin ?? "";

$adminNav = [
    "index.php" => ["label" => "Tableau de bord", "key" => "dashboard", "icon" => "layout-dashboard"],
    "demandes.php" => ["label" => "Demandes de documents", "key" => "demandes", "icon" => "file-text"],
    "rendezvous.php" => ["label" => "Rendez-vous", "key" => "rendezvous", "icon" => "calendar-check-2"],
    "actualites.php" => ["label" => "Actualités", "key" => "actualites", "icon" => "newspaper"],
    "medias.php" => ["label" => "Médiathèque", "key" => "medias", "icon" => "image"],
    "messages.php" => ["label" => "Messages de contact", "key" => "messages", "icon" => "mail"],
];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= htmlspecialchars($pageTitle) ?> — Espace Mairie</title>
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
          primary: 'rgb(29 127 72 / <alpha-value>)',
          'primary-foreground': 'rgb(255 255 255 / <alpha-value>)',
          'primary-deep': 'rgb(21 99 56 / <alpha-value>)',
          secondary: 'rgb(243 244 246 / <alpha-value>)',
          'secondary-foreground': 'rgb(21 99 56 / <alpha-value>)',
          muted: 'rgb(243 244 246 / <alpha-value>)',
          'muted-foreground': 'rgb(107 114 128 / <alpha-value>)',
          accent: 'rgb(244 233 66 / <alpha-value>)',
          'accent-foreground': 'rgb(21 99 56 / <alpha-value>)',
          destructive: 'rgb(220 38 38 / <alpha-value>)',
          border: 'rgb(229 231 235 / <alpha-value>)',
          input: 'rgb(229 231 235 / <alpha-value>)',
        },
      },
    },
  };
</script>
<link rel="stylesheet" href="/cahier-lumineux-php/assets/css/custom.css">
<script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js"></script>
</head>
<body class="min-h-screen bg-background text-foreground font-sans flex">

<aside class="hidden md:flex md:w-64 shrink-0 flex-col bg-primary-deep text-primary-foreground min-h-screen sticky top-0">
  <div class="p-6 border-b border-white/10">
    <div class="font-display text-lg">Espace Mairie</div>
    <div class="text-xs uppercase tracking-widest text-primary-foreground/60 mt-1">Chérif Lo</div>
  </div>
  <nav class="flex-1 p-4 space-y-1">
    <?php foreach ($adminNav as $href => $item): ?>
      <a href="<?= $href ?>" class="flex items-center gap-3 rounded-xl px-4 py-2.5 text-sm font-medium transition <?= $activeAdmin === $item['key'] ? 'bg-white/15 text-white' : 'text-primary-foreground/75 hover:bg-white/10' ?>">
        <i data-lucide="<?= $item['icon'] ?>" class="h-4 w-4"></i> <?= $item['label'] ?>
      </a>
    <?php endforeach; ?>
  </nav>
  <div class="p-4 border-t border-white/10 space-y-1">
    <a href="/cahier-lumineux-php/index.php" class="flex items-center gap-3 rounded-xl px-4 py-2.5 text-sm text-primary-foreground/75 hover:bg-white/10 transition">
      <i data-lucide="globe" class="h-4 w-4"></i> Voir le site
    </a>
    <a href="logout.php" class="flex items-center gap-3 rounded-xl px-4 py-2.5 text-sm text-primary-foreground/75 hover:bg-white/10 transition">
      <i data-lucide="log-out" class="h-4 w-4"></i> Déconnexion
    </a>
  </div>
</aside>

<div class="flex-1 min-w-0">
  <header class="md:hidden sticky top-0 z-30 bg-primary-deep text-primary-foreground px-4 h-16 flex items-center justify-between">
    <div class="font-display">Espace Mairie</div>
    <a href="logout.php" class="text-xs underline">Déconnexion</a>
  </header>
  <nav class="md:hidden flex overflow-x-auto gap-2 px-4 py-3 bg-secondary/60 border-b border-border">
    <?php foreach ($adminNav as $href => $item): ?>
      <a href="<?= $href ?>" class="shrink-0 rounded-full px-3 py-1.5 text-xs font-medium <?= $activeAdmin === $item['key'] ? 'bg-primary text-primary-foreground' : 'bg-card border border-border' ?>"><?= $item['label'] ?></a>
    <?php endforeach; ?>
  </nav>
  <main class="p-6 md:p-10 max-w-6xl mx-auto">
