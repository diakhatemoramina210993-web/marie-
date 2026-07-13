<?php
require __DIR__ . '/includes/auth.php';
require_admin();
require __DIR__ . '/../includes/db.php';
require __DIR__ . '/../includes/functions.php';
require __DIR__ . '/../includes/csrf.php';

$id = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);
$actu = null;
if ($id) {
    $stmt = $pdo->prepare("SELECT * FROM actualites WHERE id = :id");
    $stmt->execute(['id' => $id]);
    $actu = $stmt->fetch();
    if (!$actu) {
        header('Location: actualites.php');
        exit;
    }
}

$errors = [];
$old = $actu ?: [
    'titre' => '', 'tag' => 'Communiqué', 'extrait' => '', 'contenu' => '',
    'date_publication' => date('Y-m-d'), 'statut' => 'publie', 'image' => null,
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) {
        $errors[] = "Session expirée, merci de renvoyer le formulaire.";
    }
    $titre = trim($_POST['titre'] ?? '');
    $tag = trim($_POST['tag'] ?? 'Communiqué');
    $extrait = trim($_POST['extrait'] ?? '');
    $contenu = trim($_POST['contenu'] ?? '');
    $datePublication = trim($_POST['date_publication'] ?? '');
    $statut = ($_POST['statut'] ?? 'publie') === 'brouillon' ? 'brouillon' : 'publie';
    $removeImage = !empty($_POST['remove_image']);

    $old = [
        'titre' => $titre, 'tag' => $tag, 'extrait' => $extrait, 'contenu' => $contenu,
        'date_publication' => $datePublication, 'statut' => $statut, 'image' => $actu['image'] ?? null,
    ];

    if ($titre === '') $errors[] = "Le titre est requis.";
    if ($extrait === '') $errors[] = "L'extrait (résumé court) est requis.";
    if (!DateTime::createFromFormat('Y-m-d', $datePublication)) $errors[] = "La date de publication est invalide.";

    $newImage = null;
    if (empty($errors)) {
        $newImage = handle_actualite_image('photo', $errors);
    }

    if (empty($errors)) {
        $finalImage = $actu['image'] ?? null;
        if ($newImage) {
            if ($finalImage) {
                @unlink(__DIR__ . '/../assets/img/actualites/' . $finalImage);
            }
            $finalImage = $newImage;
        } elseif ($removeImage && $finalImage) {
            @unlink(__DIR__ . '/../assets/img/actualites/' . $finalImage);
            $finalImage = null;
        }

        if ($actu) {
            $stmt = $pdo->prepare(
                "UPDATE actualites SET titre=:titre, tag=:tag, extrait=:extrait, contenu=:contenu, image=:image, date_publication=:date_pub, statut=:statut WHERE id=:id"
            );
            $stmt->execute([
                'titre' => $titre, 'tag' => $tag, 'extrait' => $extrait, 'contenu' => $contenu ?: null,
                'image' => $finalImage, 'date_pub' => $datePublication, 'statut' => $statut, 'id' => $id,
            ]);
        } else {
            $stmt = $pdo->prepare(
                "INSERT INTO actualites (titre, tag, extrait, contenu, image, date_publication, statut) VALUES (:titre, :tag, :extrait, :contenu, :image, :date_pub, :statut)"
            );
            $stmt->execute([
                'titre' => $titre, 'tag' => $tag, 'extrait' => $extrait, 'contenu' => $contenu ?: null,
                'image' => $finalImage, 'date_pub' => $datePublication, 'statut' => $statut,
            ]);
        }

        header('Location: actualites.php');
        exit;
    }
}

$pageTitle = $actu ? "Modifier l'actualité" : "Nouvelle actualité";
$activeAdmin = "actualites";
include __DIR__ . '/includes/layout-top.php';
?>

<a href="actualites.php" class="text-sm text-muted-foreground hover:text-primary">← Retour aux actualités</a>

<h1 class="mt-4 font-display text-3xl"><?= $actu ? "Modifier l'actualité" : "Publier une nouvelle actualité" ?></h1>

<?php if (!empty($errors)): ?>
  <div class="mt-6 rounded-xl border border-destructive/30 bg-destructive/10 p-4 text-sm text-destructive max-w-2xl">
    <ul class="list-disc list-inside space-y-1">
      <?php foreach ($errors as $err): ?><li><?= htmlspecialchars($err) ?></li><?php endforeach; ?>
    </ul>
  </div>
<?php endif; ?>

<form method="post" enctype="multipart/form-data" class="mt-6 max-w-2xl rounded-2xl border border-border bg-card p-6 md:p-8 space-y-5">
  <?= csrf_field() ?>
  <?php if ($actu): ?><input type="hidden" name="id" value="<?= $actu['id'] ?>"><?php endif; ?>

  <label class="text-sm block">
    <span class="text-foreground/85">Titre</span>
    <input name="titre" value="<?= htmlspecialchars($old['titre']) ?>" required class="mt-2 w-full rounded-xl border border-input bg-background px-4 py-2.5">
  </label>

  <div class="grid sm:grid-cols-2 gap-5">
    <label class="text-sm block">
      <span class="text-foreground/85">Catégorie</span>
      <select name="tag" class="mt-2 w-full rounded-xl border border-input bg-background px-4 py-2.5">
        <?php foreach (['Événement', 'Communiqué', 'Projet'] as $t): ?>
          <option value="<?= $t ?>" <?= $old['tag'] === $t ? 'selected' : '' ?>><?= $t ?></option>
        <?php endforeach; ?>
      </select>
    </label>
    <label class="text-sm block">
      <span class="text-foreground/85">Date de publication</span>
      <input type="date" name="date_publication" value="<?= htmlspecialchars($old['date_publication']) ?>" required class="mt-2 w-full rounded-xl border border-input bg-background px-4 py-2.5">
    </label>
  </div>

  <label class="text-sm block">
    <span class="text-foreground/85">Extrait (résumé affiché sur les cartes)</span>
    <textarea name="extrait" required rows="2" class="mt-2 w-full rounded-xl border border-input bg-background px-4 py-2.5 resize-none"><?= htmlspecialchars($old['extrait']) ?></textarea>
  </label>

  <label class="text-sm block">
    <span class="text-foreground/85">Contenu complet (optionnel)</span>
    <textarea name="contenu" rows="6" class="mt-2 w-full rounded-xl border border-input bg-background px-4 py-2.5 resize-none"><?= htmlspecialchars($old['contenu'] ?? '') ?></textarea>
  </label>

  <div class="text-sm">
    <span class="text-foreground/85">Photo</span>
    <?php if (!empty($old['image'])): ?>
      <div class="mt-2 flex items-center gap-4">
        <img src="/cahier-lumineux-php/assets/img/actualites/<?= htmlspecialchars($old['image']) ?>" class="h-20 w-32 object-cover rounded-lg border border-border">
        <label class="flex items-center gap-2 text-xs text-muted-foreground">
          <input type="checkbox" name="remove_image" value="1"> Supprimer la photo actuelle
        </label>
      </div>
    <?php endif; ?>
    <input type="file" name="photo" accept=".jpg,.jpeg,.png,.webp" class="mt-2 w-full rounded-xl border border-input bg-background px-4 py-2.5">
    <p class="mt-1 text-xs text-muted-foreground">jpg, png ou webp — 6 Mo max. Si aucune photo n'est ajoutée, un visuel provisoire est affiché sur le site.</p>
  </div>

  <label class="text-sm block">
    <span class="text-foreground/85">Statut</span>
    <select name="statut" class="mt-2 w-full rounded-xl border border-input bg-background px-4 py-2.5">
      <option value="publie" <?= $old['statut'] === 'publie' ? 'selected' : '' ?>>Publiée (visible sur le site)</option>
      <option value="brouillon" <?= $old['statut'] === 'brouillon' ? 'selected' : '' ?>>Brouillon (non visible)</option>
    </select>
  </label>

  <button type="submit" class="rounded-full bg-primary text-primary-foreground px-6 py-3 text-sm font-semibold hover:bg-primary-deep transition">
    <?= $actu ? 'Enregistrer les modifications' : "Publier l'actualité" ?>
  </button>
</form>

<?php include __DIR__ . '/includes/layout-bottom.php'; ?>
