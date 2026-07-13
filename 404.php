<?php
$pageTitle = "Page introuvable — Mairie de Chérif Lo";
$pageDescription = "Cette page n'existe pas ou a été déplacée.";
$activePage = "";
include __DIR__ . "/includes/header.php";
?>

<div class="flex min-h-[70vh] items-center justify-center bg-background px-4">
  <div class="max-w-md text-center py-24">
    <h1 class="text-7xl font-display text-primary">404</h1>
    <h2 class="mt-4 text-xl font-display text-foreground">Page introuvable</h2>
    <p class="mt-2 text-sm text-muted-foreground">
      Cette page n'existe pas ou a été déplacée.
    </p>
    <div class="mt-6">
      <a href="/cahier-lumineux-php/index.php" class="inline-flex items-center justify-center rounded-full bg-primary px-5 py-2.5 text-sm font-medium text-primary-foreground hover:bg-primary-deep transition-colors">
        Retour à l'accueil
      </a>
    </div>
  </div>
</div>

<?php include __DIR__ . "/includes/footer.php"; ?>
