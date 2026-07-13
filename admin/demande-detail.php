<?php
require __DIR__ . '/includes/auth.php';
require_admin();
require __DIR__ . '/../includes/db.php';
require __DIR__ . '/../includes/functions.php';
require __DIR__ . '/../includes/document-types.php';
require __DIR__ . '/../includes/csrf.php';

$id = (int) ($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM demandes WHERE id = :id");
$stmt->execute(['id' => $id]);
$demande = $stmt->fetch();
if (!$demande) {
    header('Location: demandes.php');
    exit;
}

$updated = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_verify()) {
    $nouveauStatut = $_POST['statut'] ?? $demande['statut'];
    $note = trim($_POST['note_admin'] ?? '');
    if (in_array($nouveauStatut, ['recue', 'en_cours', 'prete', 'rejetee'], true)) {
        $upd = $pdo->prepare("UPDATE demandes SET statut = :statut, note_admin = :note WHERE id = :id");
        $upd->execute(['statut' => $nouveauStatut, 'note' => $note ?: null, 'id' => $id]);

        if ($nouveauStatut !== $demande['statut']) {
            send_notification(
                $demande['email'],
                "Mise à jour de votre demande " . $demande['code_suivi'],
                "<p>Bonjour " . htmlspecialchars($demande['prenom'] . ' ' . $demande['nom']) . ",</p>" .
                "<p>Le statut de votre demande <strong>" . htmlspecialchars($demande['code_suivi']) . "</strong> a été mis à jour :</p>" .
                "<p style='font-size:16px;'><strong>" . statut_demande_label($nouveauStatut) . "</strong></p>" .
                ($note ? "<p>Message de la mairie : " . nl2br(htmlspecialchars($note)) . "</p>" : "") .
                "<p>Vous pouvez consulter le détail sur la page « Suivi de dossier » du site.</p>" .
                "<p>Cordialement,<br>La Mairie de Chérif Lo</p>"
            );
        }

        $stmt->execute(['id' => $id]);
        $demande = $stmt->fetch();
        $updated = true;
    }
}

$typeInfo = $DOCUMENT_TYPES[$demande['type_document']] ?? null;
$details = json_decode($demande['details_json'] ?? '[]', true) ?: [];

$pageTitle = "Demande " . $demande['code_suivi'];
$activeAdmin = "demandes";
include __DIR__ . '/includes/layout-top.php';
?>

<a href="demandes.php" class="text-sm text-muted-foreground hover:text-primary">← Retour aux demandes</a>

<div class="mt-4 flex items-center justify-between flex-wrap gap-4">
  <div>
    <div class="text-xs uppercase tracking-widest text-primary/70 font-mono"><?= htmlspecialchars($demande['code_suivi']) ?></div>
    <h1 class="mt-1 font-display text-3xl"><?= htmlspecialchars($typeInfo['label'] ?? $demande['type_document']) ?></h1>
  </div>
  <span class="rounded-full px-4 py-1.5 text-sm font-semibold <?= statut_demande_badge_class($demande['statut']) ?>"><?= statut_demande_label($demande['statut']) ?></span>
</div>

<?php if ($updated): ?>
  <div class="mt-6 rounded-xl border border-primary/30 bg-primary/10 p-4 text-sm text-primary">Statut mis à jour. Le demandeur a été notifié automatiquement par e-mail.</div>
<?php endif; ?>

<div class="mt-8 grid lg:grid-cols-3 gap-8">
  <div class="lg:col-span-2 space-y-6">
    <div class="rounded-2xl border border-border bg-card p-6">
      <h2 class="font-display text-lg mb-4">Informations du demandeur</h2>
      <dl class="grid sm:grid-cols-2 gap-4 text-sm">
        <div><dt class="text-muted-foreground">Nom complet</dt><dd class="mt-0.5 font-medium"><?= htmlspecialchars(($demande['civilite'] ?: '') . ' ' . $demande['prenom'] . ' ' . $demande['nom']) ?></dd></div>
        <div><dt class="text-muted-foreground">Date de naissance</dt><dd class="mt-0.5 font-medium"><?= $demande['date_naissance'] ? (new DateTime($demande['date_naissance']))->format('d/m/Y') : '—' ?></dd></div>
        <div><dt class="text-muted-foreground">Lieu de naissance</dt><dd class="mt-0.5 font-medium"><?= htmlspecialchars($demande['lieu_naissance'] ?: '—') ?></dd></div>
        <div><dt class="text-muted-foreground">Téléphone</dt><dd class="mt-0.5 font-medium"><?= htmlspecialchars($demande['telephone']) ?></dd></div>
        <div><dt class="text-muted-foreground">E-mail</dt><dd class="mt-0.5 font-medium"><?= htmlspecialchars($demande['email']) ?></dd></div>
        <div><dt class="text-muted-foreground">Mode de retrait</dt><dd class="mt-0.5 font-medium"><?= $demande['mode_retrait'] === 'postal' ? 'Envoi postal' : 'Retrait au guichet' ?></dd></div>
        <?php if ($demande['mode_retrait'] === 'postal'): ?>
          <div class="sm:col-span-2"><dt class="text-muted-foreground">Adresse postale</dt><dd class="mt-0.5 font-medium"><?= htmlspecialchars($demande['adresse'] ?: '—') ?></dd></div>
        <?php endif; ?>
        <div><dt class="text-muted-foreground">Nombre de copies</dt><dd class="mt-0.5 font-medium"><?= (int) $demande['nombre_copies'] ?></dd></div>
      </dl>
    </div>

    <?php if ($details): ?>
      <div class="rounded-2xl border border-border bg-card p-6">
        <h2 class="font-display text-lg mb-4">Détails spécifiques</h2>
        <dl class="grid sm:grid-cols-2 gap-4 text-sm">
          <?php foreach ($details as $key => $value): ?>
            <div><dt class="text-muted-foreground"><?= htmlspecialchars($FIELD_META[$key]['label'] ?? $key) ?></dt><dd class="mt-0.5 font-medium"><?= nl2br(htmlspecialchars($value)) ?></dd></div>
          <?php endforeach; ?>
        </dl>
      </div>
    <?php endif; ?>

    <?php if ($demande['piece_jointe']): ?>
      <div class="rounded-2xl border border-border bg-card p-6">
        <h2 class="font-display text-lg mb-3">Pièce jointe</h2>
        <a href="download.php?file=<?= urlencode($demande['piece_jointe']) ?>" class="inline-flex items-center gap-2 text-sm text-primary hover:text-primary-deep font-medium">
          <i data-lucide="paperclip" class="h-4 w-4"></i> Télécharger le fichier
        </a>
      </div>
    <?php endif; ?>
  </div>

  <div>
    <form method="post" class="rounded-2xl border border-border bg-card p-6 sticky top-6">
      <?= csrf_field() ?>
      <h2 class="font-display text-lg mb-4">Traiter le dossier</h2>
      <label class="text-sm block">
        <span class="text-foreground/85">Statut</span>
        <select name="statut" class="mt-2 w-full rounded-xl border border-input bg-background px-4 py-2.5">
          <?php foreach (['recue' => 'Reçue', 'en_cours' => 'En cours', 'prete' => 'Prête', 'rejetee' => 'Rejetée'] as $k => $l): ?>
            <option value="<?= $k ?>" <?= $demande['statut'] === $k ? 'selected' : '' ?>><?= $l ?></option>
          <?php endforeach; ?>
        </select>
      </label>
      <label class="mt-4 text-sm block">
        <span class="text-foreground/85">Message au demandeur (optionnel)</span>
        <textarea name="note_admin" rows="4" class="mt-2 w-full rounded-xl border border-input bg-background px-4 py-2.5 resize-none"><?= htmlspecialchars($demande['note_admin'] ?? '') ?></textarea>
      </label>
      <button type="submit" class="mt-5 w-full rounded-full bg-primary text-primary-foreground px-6 py-3 text-sm font-semibold hover:bg-primary-deep transition">
        Enregistrer et notifier
      </button>
      <p class="mt-3 text-xs text-muted-foreground">Un e-mail automatique est envoyé au demandeur si le statut change.</p>
    </form>
  </div>
</div>

<?php include __DIR__ . '/includes/layout-bottom.php'; ?>
