<?php
require __DIR__ . '/includes/auth.php';
require_admin();
require __DIR__ . '/../includes/db.php';
require __DIR__ . '/../includes/functions.php';
require __DIR__ . '/../includes/csrf.php';

$id = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);
$media = null;
if ($id) {
    $stmt = $pdo->prepare("SELECT * FROM medias WHERE id = :id");
    $stmt->execute(['id' => $id]);
    $media = $stmt->fetch();
    if (!$media) {
        header('Location: medias.php');
        exit;
    }
}

$errors = [];
$old = $media ?: [
    'type' => 'photo', 'titre' => '', 'fichier' => null, 'video_url' => '', 'duree' => '', 'ordre' => 0,
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) {
        $errors[] = "Session expirée, merci de renvoyer le formulaire.";
    }
    $type = ($_POST['type'] ?? 'photo') === 'video' ? 'video' : 'photo';
    $titre = trim($_POST['titre'] ?? '');
    $videoUrl = trim($_POST['video_url'] ?? '');
    $duree = trim($_POST['duree'] ?? '');
    $ordre = (int) ($_POST['ordre'] ?? 0);
    $removeFile = !empty($_POST['remove_file']);

    $old = [
        'type' => $type, 'titre' => $titre, 'fichier' => $media['fichier'] ?? null,
        'video_url' => $videoUrl, 'duree' => $duree, 'ordre' => $ordre,
    ];

    if ($titre === '') $errors[] = "Le titre est requis.";

    // Un fichier existant n'est réutilisable que si le type du média n'a pas changé
    // (un fichier image n'a pas de sens pour un média vidéo, et inversement).
    $typeChanged = $media && $media['type'] !== $type;
    $keepableFile = (!$typeChanged && !$removeFile) ? ($media['fichier'] ?? null) : null;

    $newFile = null;
    if (empty($errors)) {
        if ($type === 'photo') {
            $newFile = handle_media_photo('fichier', $errors);
            if (!$newFile && !$keepableFile) {
                $errors[] = "Une photo est requise pour un média de type Photo.";
            }
        } else {
            $newFile = handle_media_video('fichier', $errors);
            if (!$newFile && !$keepableFile && $videoUrl === '') {
                $errors[] = "Ajoutez un fichier vidéo ou un lien vidéo externe.";
            }
        }
    }

    if (empty($errors)) {
        $oldFile = $media['fichier'] ?? null;
        $finalFile = $keepableFile;
        if ($newFile) {
            $finalFile = $newFile;
        }
        if ($oldFile && $oldFile !== $finalFile) {
            @unlink(__DIR__ . '/../assets/img/medias/' . $oldFile);
        }

        if ($media) {
            $stmt = $pdo->prepare(
                "UPDATE medias SET type=:type, titre=:titre, fichier=:fichier, video_url=:video_url, duree=:duree, ordre=:ordre WHERE id=:id"
            );
            $stmt->execute([
                'type' => $type, 'titre' => $titre, 'fichier' => $finalFile,
                'video_url' => $type === 'video' && $videoUrl !== '' ? $videoUrl : null,
                'duree' => $duree ?: null, 'ordre' => $ordre, 'id' => $id,
            ]);
        } else {
            $stmt = $pdo->prepare(
                "INSERT INTO medias (type, titre, fichier, video_url, duree, ordre) VALUES (:type, :titre, :fichier, :video_url, :duree, :ordre)"
            );
            $stmt->execute([
                'type' => $type, 'titre' => $titre, 'fichier' => $finalFile,
                'video_url' => $type === 'video' && $videoUrl !== '' ? $videoUrl : null,
                'duree' => $duree ?: null, 'ordre' => $ordre,
            ]);
        }

        header('Location: medias.php');
        exit;
    }
}

$pageTitle = $media ? "Modifier le média" : "Nouveau média";
$activeAdmin = "medias";
include __DIR__ . '/includes/layout-top.php';
?>

<a href="medias.php" class="text-sm text-muted-foreground hover:text-primary">← Retour à la médiathèque</a>

<h1 class="mt-4 font-display text-3xl"><?= $media ? "Modifier le média" : "Publier un nouveau média" ?></h1>

<?php if (!empty($errors)): ?>
  <div class="mt-6 rounded-xl border border-destructive/30 bg-destructive/10 p-4 text-sm text-destructive max-w-2xl">
    <ul class="list-disc list-inside space-y-1">
      <?php foreach ($errors as $err): ?><li><?= htmlspecialchars($err) ?></li><?php endforeach; ?>
    </ul>
  </div>
<?php endif; ?>

<form method="post" enctype="multipart/form-data" class="mt-6 max-w-2xl rounded-2xl border border-border bg-card p-6 md:p-8 space-y-5">
  <?= csrf_field() ?>
  <?php if ($media): ?><input type="hidden" name="id" value="<?= $media['id'] ?>"><?php endif; ?>

  <label class="text-sm block">
    <span class="text-foreground/85">Type de média</span>
    <select name="type" id="media-type" class="mt-2 w-full rounded-xl border border-input bg-background px-4 py-2.5">
      <option value="photo" <?= $old['type'] === 'photo' ? 'selected' : '' ?>>Photo</option>
      <option value="video" <?= $old['type'] === 'video' ? 'selected' : '' ?>>Vidéo</option>
    </select>
  </label>

  <label class="text-sm block">
    <span class="text-foreground/85">Titre</span>
    <input name="titre" value="<?= htmlspecialchars($old['titre']) ?>" required class="mt-2 w-full rounded-xl border border-input bg-background px-4 py-2.5">
  </label>

  <div id="field-photo" class="text-sm">
    <span class="text-foreground/85">Photo</span>
    <?php if ($old['type'] === 'photo' && !empty($old['fichier'])): ?>
      <div class="mt-2 flex items-center gap-4">
        <img src="/cahier-lumineux-php/assets/img/medias/<?= htmlspecialchars($old['fichier']) ?>" class="h-20 w-32 object-cover rounded-lg border border-border">
        <label class="flex items-center gap-2 text-xs text-muted-foreground">
          <input type="checkbox" name="remove_file" value="1"> Supprimer la photo actuelle
        </label>
      </div>
    <?php endif; ?>
    <input type="file" name="fichier" id="input-photo-file" accept=".jpg,.jpeg,.png,.webp" class="mt-2 w-full rounded-xl border border-input bg-background px-4 py-2.5">
    <p class="mt-1 text-xs text-muted-foreground">jpg, png ou webp — 6 Mo max.</p>
  </div>

  <div id="field-video" class="text-sm space-y-4">
    <div>
      <span class="text-foreground/85">Fichier vidéo</span>
      <?php if ($old['type'] === 'video' && !empty($old['fichier'])): ?>
        <div class="mt-2 flex items-center gap-4">
          <video src="/cahier-lumineux-php/assets/img/medias/<?= htmlspecialchars($old['fichier']) ?>" class="h-20 w-32 rounded-lg border border-border object-cover" muted></video>
          <label class="flex items-center gap-2 text-xs text-muted-foreground">
            <input type="checkbox" name="remove_file" value="1"> Supprimer le fichier actuel
          </label>
        </div>
      <?php endif; ?>
      <input type="file" name="fichier" id="input-video-file" accept=".mp4,.webm,.mov" class="mt-2 w-full rounded-xl border border-input bg-background px-4 py-2.5">
      <p class="mt-1 text-xs text-muted-foreground">mp4, webm ou mov — 35 Mo max.</p>
    </div>
    <div>
      <span class="text-foreground/85">— ou lien vidéo externe (YouTube, Vimeo…)</span>
      <input name="video_url" value="<?= htmlspecialchars($old['video_url'] ?? '') ?>" placeholder="https://youtube.com/embed/…" class="mt-2 w-full rounded-xl border border-input bg-background px-4 py-2.5">
      <p class="mt-1 text-xs text-muted-foreground">Utile pour les vidéos trop lourdes pour être envoyées directement.</p>
    </div>
    <label class="text-sm block">
      <span class="text-foreground/85">Durée affichée (ex : 3:24)</span>
      <input name="duree" value="<?= htmlspecialchars($old['duree'] ?? '') ?>" class="mt-2 w-full rounded-xl border border-input bg-background px-4 py-2.5">
    </label>
  </div>

  <label class="text-sm block">
    <span class="text-foreground/85">Ordre d'affichage</span>
    <input type="number" name="ordre" value="<?= (int) $old['ordre'] ?>" class="mt-2 w-32 rounded-xl border border-input bg-background px-4 py-2.5">
    <p class="mt-1 text-xs text-muted-foreground">Les valeurs les plus basses s'affichent en premier.</p>
  </label>

  <button type="submit" class="rounded-full bg-primary text-primary-foreground px-6 py-3 text-sm font-semibold hover:bg-primary-deep transition">
    <?= $media ? 'Enregistrer les modifications' : 'Publier le média' ?>
  </button>
</form>

<script>
  (function () {
    var typeSelect = document.getElementById('media-type');
    var photoField = document.getElementById('field-photo');
    var videoField = document.getElementById('field-video');
    var photoFileInput = document.getElementById('input-photo-file');
    var videoFileInput = document.getElementById('input-video-file');
    function sync() {
      var isVideo = typeSelect.value === 'video';
      photoField.style.display = isVideo ? 'none' : '';
      videoField.style.display = isVideo ? '' : 'none';
      // Un seul des deux champs "fichier" ne doit être soumis à la fois.
      photoFileInput.disabled = isVideo;
      videoFileInput.disabled = !isVideo;
    }
    typeSelect.addEventListener('change', sync);
    sync();
  })();
</script>

<?php include __DIR__ . '/includes/layout-bottom.php'; ?>
