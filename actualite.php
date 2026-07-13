<?php
$id = (int) ($_GET['id'] ?? 0);

$pageTitle = "Actualité — Mairie de Chérif Lo";
$pageDescription = "Actualités de la Mairie de Chérif Lo.";
$activePage = "actualites.php";
include __DIR__ . "/includes/header.php";

$stmt = $pdo->prepare("SELECT * FROM actualites WHERE id = :id AND statut = 'publie'");
$stmt->execute(['id' => $id]);
$actu = $stmt->fetch();

if (!$actu) {
    http_response_code(404);
    ?>
    <section class="container-page py-24 text-center">
      <h1 class="font-display text-3xl">Actualité introuvable</h1>
      <p class="mt-3 text-muted-foreground">Cet article n'existe pas ou a été dépublié.</p>
      <a href="actualites.php" class="mt-6 inline-flex rounded-full bg-primary text-primary-foreground px-6 py-3 text-sm font-semibold">Retour aux actualités</a>
    </section>
    <?php
    include __DIR__ . "/includes/footer.php";
    exit;
}

$related = $pdo->prepare("SELECT * FROM actualites WHERE statut = 'publie' AND id != :id ORDER BY date_publication DESC LIMIT 3");
$related->execute(['id' => $id]);
$relatedItems = $related->fetchAll();
?>

<article class="container-page py-16 max-w-3xl mx-auto">
  <a href="actualites.php" class="text-sm text-muted-foreground hover:text-primary">← Toutes les actualités</a>

  <div class="mt-4 inline-flex items-center gap-1.5 rounded-full bg-secondary text-secondary-foreground text-xs font-semibold px-3 py-1">
    <i data-lucide="<?= icon_for_tag($actu['tag']) ?>" class="h-3.5 w-3.5"></i> <?= htmlspecialchars($actu['tag']) ?>
  </div>
  <h1 class="mt-4 font-display text-3xl md:text-4xl text-balance"><?= htmlspecialchars($actu['titre']) ?></h1>
  <div class="mt-3 text-sm text-muted-foreground flex items-center gap-1.5">
    <i data-lucide="calendar" class="h-3.5 w-3.5"></i> <?= format_date_fr($actu['date_publication']) ?>
  </div>

  <div class="mt-8 aspect-[16/9] rounded-3xl overflow-hidden bg-gradient-hero relative">
    <img src="<?= htmlspecialchars(actualite_image_url($actu)) ?>" alt="" class="h-full w-full object-cover <?= $actu['image'] ? '' : 'mix-blend-luminosity opacity-80' ?>">
    <?php if (!$actu['image']): ?>
      <div class="absolute bottom-3 right-3 rounded bg-black/50 text-white text-[10px] px-2 py-0.5">Photo d'illustration</div>
    <?php endif; ?>
  </div>

  <div class="mt-8 text-lg text-foreground/85 leading-relaxed">
    <?= nl2br(htmlspecialchars($actu['extrait'])) ?>
  </div>
  <?php if (!empty($actu['contenu'])): ?>
    <div class="mt-6 text-muted-foreground leading-relaxed space-y-4">
      <?= nl2br(htmlspecialchars($actu['contenu'])) ?>
    </div>
  <?php endif; ?>
</article>

<?php if ($relatedItems): ?>
<section class="container-page pb-20">
  <h2 class="font-display text-2xl mb-6">À lire aussi</h2>
  <div class="grid md:grid-cols-3 gap-6">
    <?php foreach ($relatedItems as $r): ?>
      <a href="actualite.php?id=<?= $r['id'] ?>" class="group rounded-3xl border border-border overflow-hidden bg-card hover:shadow-elegant transition">
        <div class="aspect-[16/10] relative overflow-hidden">
          <img src="<?= htmlspecialchars(actualite_image_url($r)) ?>" alt="" class="h-full w-full object-cover <?= $r['image'] ? '' : 'mix-blend-luminosity opacity-80' ?> group-hover:scale-105 transition duration-500">
        </div>
        <div class="p-5">
          <div class="text-xs text-muted-foreground"><?= format_date_fr($r['date_publication']) ?></div>
          <h3 class="mt-1 font-display text-lg leading-snug group-hover:text-primary transition"><?= htmlspecialchars($r['titre']) ?></h3>
        </div>
      </a>
    <?php endforeach; ?>
  </div>
</section>
<?php endif; ?>

<?php include __DIR__ . "/includes/footer.php"; ?>
