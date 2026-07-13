<?php
require __DIR__ . '/includes/auth.php';
require_admin();
require __DIR__ . '/../includes/db.php';
require __DIR__ . '/../includes/csrf.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete' && csrf_verify()) {
    $id = (int) ($_POST['id'] ?? 0);
    $stmt = $pdo->prepare("SELECT image FROM actualites WHERE id = :id");
    $stmt->execute(['id' => $id]);
    $row = $stmt->fetch();
    if ($row) {
        $pdo->prepare("DELETE FROM actualites WHERE id = :id")->execute(['id' => $id]);
        if ($row['image']) {
            @unlink(__DIR__ . '/../assets/img/actualites/' . $row['image']);
        }
    }
    header('Location: actualites.php');
    exit;
}

$statutFilter = $_GET['statut'] ?? '';
$sql = "SELECT * FROM actualites WHERE 1=1";
$params = [];
if (in_array($statutFilter, ['publie', 'brouillon'], true)) {
    $sql .= " AND statut = :statut";
    $params['statut'] = $statutFilter;
}
$sql .= " ORDER BY date_publication DESC, created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$actus = $stmt->fetchAll();

$pageTitle = "Actualités";
$activeAdmin = "actualites";
include __DIR__ . '/includes/layout-top.php';
?>

<div class="flex items-center justify-between flex-wrap gap-4 mb-6">
  <h1 class="font-display text-3xl">Actualités</h1>
  <a href="actualite-form.php" class="inline-flex items-center gap-2 rounded-full bg-primary text-primary-foreground px-5 py-2.5 text-sm font-semibold hover:bg-primary-deep transition">
    <i data-lucide="plus" class="h-4 w-4"></i> Nouvelle actualité
  </a>
</div>

<form method="get" class="flex flex-wrap gap-3 mb-6">
  <select name="statut" onchange="this.form.submit()" class="rounded-xl border border-input bg-card px-4 py-2.5 text-sm">
    <option value="">Tous les statuts</option>
    <option value="publie" <?= $statutFilter === 'publie' ? 'selected' : '' ?>>Publiée</option>
    <option value="brouillon" <?= $statutFilter === 'brouillon' ? 'selected' : '' ?>>Brouillon</option>
  </select>
  <?php if ($statutFilter): ?>
    <a href="actualites.php" class="rounded-xl px-4 py-2.5 text-sm text-muted-foreground hover:text-primary">Réinitialiser</a>
  <?php endif; ?>
</form>

<div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5">
  <?php if (!$actus): ?>
    <div class="col-span-full rounded-2xl border border-border bg-card p-8 text-center text-muted-foreground text-sm">
      Aucune actualité pour le moment. Cliquez sur « Nouvelle actualité » pour publier la première.
    </div>
  <?php endif; ?>
  <?php foreach ($actus as $a): ?>
    <div class="rounded-2xl border border-border bg-card overflow-hidden">
      <div class="aspect-[16/10] bg-secondary relative">
        <?php if ($a['image']): ?>
          <img src="/cahier-lumineux-php/assets/img/actualites/<?= htmlspecialchars($a['image']) ?>" alt="" class="h-full w-full object-cover">
        <?php else: ?>
          <div class="h-full w-full bg-gradient-hero"></div>
        <?php endif; ?>
        <span class="absolute top-3 left-3 rounded-full bg-white/95 text-primary-deep text-xs font-semibold px-3 py-1"><?= htmlspecialchars($a['tag']) ?></span>
        <span class="absolute top-3 right-3 rounded-full px-3 py-1 text-xs font-semibold <?= $a['statut'] === 'publie' ? 'bg-primary text-primary-foreground' : 'bg-accent/90 text-primary-deep' ?>">
          <?= $a['statut'] === 'publie' ? 'Publiée' : 'Brouillon' ?>
        </span>
      </div>
      <div class="p-5">
        <div class="text-xs text-muted-foreground"><?= (new DateTime($a['date_publication']))->format('d/m/Y') ?></div>
        <h3 class="mt-1 font-display text-lg leading-snug"><?= htmlspecialchars($a['titre']) ?></h3>
        <p class="mt-2 text-sm text-muted-foreground line-clamp-2"><?= htmlspecialchars($a['extrait']) ?></p>
        <div class="mt-4 flex items-center gap-3">
          <a href="actualite-form.php?id=<?= $a['id'] ?>" class="text-sm font-medium text-primary hover:text-primary-deep">Modifier</a>
          <form method="post" onsubmit="return confirm('Supprimer définitivement cette actualité ?');">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="id" value="<?= $a['id'] ?>">
            <button type="submit" class="text-sm font-medium text-destructive hover:underline">Supprimer</button>
          </form>
        </div>
      </div>
    </div>
  <?php endforeach; ?>
</div>

<?php include __DIR__ . '/includes/layout-bottom.php'; ?>
