<?php
$pageTitle = "Actualités & Communiqués — Chérif Lo";
$pageDescription = "Retrouvez les actualités, communiqués officiels et projets-programmes de la commune de Chérif Lo.";
$activePage = "actualites.php";
include __DIR__ . "/includes/header.php";

$items = $pdo->query("SELECT * FROM actualites WHERE statut = 'publie' ORDER BY date_publication DESC, created_at DESC")->fetchAll();

$heroEyebrow = "Actualités";
$heroTitle = "Toute la vie de Chérif Lo, au fil des jours.";
$heroDescription = "Événements, communiqués officiels, projets-programmes : suivez l'action municipale en temps réel.";
include __DIR__ . "/includes/page-hero.php";
?>

<section class="container-page py-20">
  <?php if (!$items): ?>
    <div class="rounded-3xl border border-border bg-card p-10 text-center text-muted-foreground">
      Aucune actualité publiée pour le moment. Revenez bientôt !
    </div>
  <?php endif; ?>
  <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
    <?php foreach ($items as $it): ?>
      <article class="group rounded-3xl border border-border overflow-hidden bg-card hover:shadow-elegant transition flex flex-col">
        <a href="actualite.php?id=<?= $it['id'] ?>" class="aspect-[16/10] bg-gradient-hero relative overflow-hidden block">
          <img src="<?= htmlspecialchars(actualite_image_url($it)) ?>" alt="" class="absolute inset-0 h-full w-full object-cover <?= $it['image'] ? '' : 'mix-blend-luminosity opacity-80' ?> group-hover:scale-105 transition duration-500" loading="lazy">
          <div class="absolute inset-0 bg-gradient-to-t from-primary-deep/70 via-primary-deep/10 to-transparent"></div>
          <div class="absolute top-4 left-4 rounded-full bg-white/95 text-primary-deep text-xs font-semibold px-3 py-1 inline-flex items-center gap-1.5">
            <i data-lucide="<?= icon_for_tag($it['tag']) ?>" class="h-3.5 w-3.5"></i> <?= htmlspecialchars($it['tag']) ?>
          </div>
          <?php if (!$it['image']): ?>
            <div class="absolute bottom-3 right-3 rounded bg-black/50 text-white text-[10px] px-2 py-0.5">Photo d'illustration</div>
          <?php endif; ?>
        </a>
        <div class="p-6 flex-1 flex flex-col">
          <div class="text-xs text-muted-foreground"><?= format_date_fr($it['date_publication']) ?></div>
          <h3 class="mt-2 font-display text-xl leading-snug group-hover:text-primary transition"><?= htmlspecialchars($it['titre']) ?></h3>
          <p class="mt-2 text-sm text-muted-foreground flex-1"><?= htmlspecialchars($it['extrait']) ?></p>
          <a href="actualite.php?id=<?= $it['id'] ?>" class="mt-4 self-start text-sm font-medium text-primary hover:text-primary-deep">Lire l'article →</a>
        </div>
      </article>
    <?php endforeach; ?>
  </div>

  <?php
  $socialLinks = [];
  if (!empty($SETTINGS['social_facebook'])) {
      $socialLinks[] = ['label' => 'Facebook', 'icon' => 'facebook', 'href' => $SETTINGS['social_facebook'], 'bg' => 'bg-blue-50', 'text' => 'text-blue-600'];
  }
  if (!empty($SETTINGS['social_youtube'])) {
      $socialLinks[] = ['label' => 'YouTube', 'icon' => 'youtube', 'href' => $SETTINGS['social_youtube'], 'bg' => 'bg-red-50', 'text' => 'text-red-600'];
  }
  $socialLinks[] = [
      'label' => 'WhatsApp', 'icon' => 'message-circle',
      'href' => whatsapp_link(ADMIN_PHONE_INTL, "Bonjour, je souhaite suivre l'actualité de la Mairie de Chérif Lo."),
      'bg' => 'bg-green-50', 'text' => 'text-green-600',
  ];
  ?>
  <div class="mt-16 rounded-3xl border border-border bg-card shadow-soft p-8 md:p-10 text-center">
    <h2 class="font-display text-2xl">Suivez-nous sur nos réseaux sociaux</h2>
    <div class="mt-6 grid grid-cols-2 sm:grid-cols-3 gap-4 max-w-2xl mx-auto">
      <?php foreach ($socialLinks as $s): ?>
        <a href="<?= htmlspecialchars($s['href']) ?>" target="_blank" rel="noopener noreferrer"
           class="flex flex-col items-center gap-2 rounded-2xl <?= $s['bg'] ?> px-4 py-5 hover:shadow-elegant hover:-translate-y-0.5 transition-all">
          <i data-lucide="<?= $s['icon'] ?>" class="h-6 w-6 <?= $s['text'] ?>"></i>
          <span class="text-sm font-medium text-foreground"><?= $s['label'] ?></span>
        </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<?php include __DIR__ . "/includes/footer.php"; ?>
