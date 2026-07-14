<?php
require __DIR__ . '/includes/auth.php';
require_admin();
require __DIR__ . '/../includes/db.php';
require __DIR__ . '/../includes/csrf.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete' && csrf_verify()) {
    $id = (int) ($_POST['id'] ?? 0);
    $stmt = $pdo->prepare("SELECT fichier FROM medias WHERE id = :id");
    $stmt->execute(['id' => $id]);
    $row = $stmt->fetch();
    if ($row) {
        $pdo->prepare("DELETE FROM medias WHERE id = :id")->execute(['id' => $id]);
        if ($row['fichier']) {
            @unlink(__DIR__ . '/../assets/img/medias/' . $row['fichier']);
        }
    }
    header('Location: medias.php');
    exit;
}

$typeFilter = $_GET['type'] ?? '';
$sql = "SELECT * FROM medias WHERE 1=1";
$params = [];
if (in_array($typeFilter, ['photo', 'video'], true)) {
    $sql .= " AND type = :type";
    $params['type'] = $typeFilter;
}
$sql .= " ORDER BY ordre ASC, created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$medias = $stmt->fetchAll();

$pageTitle = "Médiathèque";
$activeAdmin = "medias";
include __DIR__ . '/includes/layout-top.php';
?>

<div class="flex items-center justify-between flex-wrap gap-4 mb-6">
  <h1 class="font-display text-3xl">Médiathèque</h1>
  <a href="media-form.php" class="inline-flex items-center gap-2 rounded-full bg-primary text-primary-foreground px-5 py-2.5 text-sm font-semibold hover:bg-primary-deep transition">
    <i data-lucide="plus" class="h-4 w-4"></i> Publier un média
  </a>
</div>

<form method="get" class="flex flex-wrap gap-3 mb-6">
  <select name="type" onchange="this.form.submit()" class="rounded-xl border border-input bg-card px-4 py-2.5 text-sm">
    <option value="">Tous les types</option>
    <option value="photo" <?= $typeFilter === 'photo' ? 'selected' : '' ?>>Photos</option>
    <option value="video" <?= $typeFilter === 'video' ? 'selected' : '' ?>>Vidéos</option>
  </select>
  <?php if ($typeFilter): ?>
    <a href="medias.php" class="rounded-xl px-4 py-2.5 text-sm text-muted-foreground hover:text-primary">Réinitialiser</a>
  <?php endif; ?>
</form>

<div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5">
  <?php if (!$medias): ?>
    <div class="col-span-full rounded-2xl border border-border bg-card p-8 text-center text-muted-foreground text-sm">
      Aucun média pour le moment. Cliquez sur « Publier un média » pour ajouter la première photo ou vidéo.
    </div>
  <?php endif; ?>
  <?php foreach ($medias as $m): ?>
    <div class="rounded-2xl border border-border bg-card overflow-hidden">
      <div class="aspect-[16/10] bg-secondary relative">
        <?php if ($m['type'] === 'photo' && $m['fichier']): ?>
          <img src="/mairie/assets/img/medias/<?= htmlspecialchars($m['fichier']) ?>" alt="" class="h-full w-full object-cover">
        <?php elseif ($m['type'] === 'video'): ?>
          <div class="h-full w-full <?= $m['fichier'] ? 'bg-black' : 'bg-gradient-hero' ?> flex items-center justify-center relative overflow-hidden">
            <?php if ($m['fichier']): ?>
              <video src="/mairie/assets/img/medias/<?= htmlspecialchars($m['fichier']) ?>" class="video-thumb-source absolute inset-0 h-full w-full object-cover opacity-0 transition-opacity duration-500" preload="auto" muted playsinline aria-hidden="true"></video>
              <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/30 to-black/50"></div>
            <?php endif; ?>
            <div class="relative h-14 w-14 rounded-full bg-white/95 grid place-items-center text-primary-deep">
              <i data-lucide="play" class="h-6 w-6 ml-1" fill="currentColor"></i>
            </div>
          </div>
          <?php if ($m['duree']): ?>
            <span class="absolute bottom-3 right-3 bg-black/60 text-white text-xs px-2 py-1 rounded"><?= htmlspecialchars($m['duree']) ?></span>
          <?php endif; ?>
        <?php else: ?>
          <div class="h-full w-full bg-gradient-hero"></div>
        <?php endif; ?>
        <span class="absolute top-3 left-3 rounded-full bg-white/95 text-primary-deep text-xs font-semibold px-3 py-1"><?= $m['type'] === 'photo' ? 'Photo' : 'Vidéo' ?></span>
      </div>
      <div class="p-5">
        <h3 class="font-display text-lg leading-snug"><?= htmlspecialchars($m['titre']) ?></h3>
        <?php if ($m['type'] === 'video'): ?>
          <p class="mt-1 text-xs text-muted-foreground"><?= $m['fichier'] ? 'Fichier vidéo' : ($m['video_url'] ? 'Lien externe' : 'Aucune source') ?></p>
        <?php endif; ?>
        <div class="mt-4 flex items-center gap-3">
          <a href="media-form.php?id=<?= $m['id'] ?>" class="text-sm font-medium text-primary hover:text-primary-deep">Modifier</a>
          <form method="post" onsubmit="return confirm('Supprimer définitivement ce média ?');">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="id" value="<?= $m['id'] ?>">
            <button type="submit" class="text-sm font-medium text-destructive hover:underline">Supprimer</button>
          </form>
        </div>
      </div>
    </div>
  <?php endforeach; ?>
</div>

<script>
  document.querySelectorAll('.video-thumb-source').forEach(function (v) {
    function seekToThumb() {
      var target = (v.duration || 12) * 0.25;
      v.addEventListener('seeked', function () { v.classList.remove('opacity-0'); }, { once: true });
      try { v.currentTime = target; } catch (e) { v.classList.remove('opacity-0'); }
    }
    if (v.readyState >= 1) { seekToThumb(); } else { v.addEventListener('loadedmetadata', seekToThumb, { once: true }); }
    v.addEventListener('error', function () { v.classList.remove('opacity-0'); });
  });
</script>

<?php include __DIR__ . '/includes/layout-bottom.php'; ?>
