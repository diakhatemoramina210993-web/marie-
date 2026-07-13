<?php
require __DIR__ . '/includes/auth.php';
require_admin();
require __DIR__ . '/../includes/db.php';
require __DIR__ . '/../includes/functions.php';
require __DIR__ . '/../includes/csrf.php';

$id = (int) ($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM rendezvous WHERE id = :id");
$stmt->execute(['id' => $id]);
$rdv = $stmt->fetch();
if (!$rdv) {
    header('Location: rendezvous.php');
    exit;
}

$updated = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_verify()) {
    $nouveauStatut = $_POST['statut'] ?? $rdv['statut'];
    $note = trim($_POST['note_admin'] ?? '');
    if (in_array($nouveauStatut, ['en_attente', 'confirme', 'annule'], true)) {
        $upd = $pdo->prepare("UPDATE rendezvous SET statut = :statut, note_admin = :note WHERE id = :id");
        $upd->execute(['statut' => $nouveauStatut, 'note' => $note ?: null, 'id' => $id]);

        if ($nouveauStatut !== $rdv['statut']) {
            $dateFr = (new DateTime($rdv['date_rdv']))->format('d/m/Y');
            send_notification(
                $rdv['email'],
                "Mise à jour de votre rendez-vous " . $rdv['code_suivi'],
                "<p>Bonjour " . htmlspecialchars($rdv['prenom'] . ' ' . $rdv['nom']) . ",</p>" .
                "<p>Votre rendez-vous <strong>" . htmlspecialchars($rdv['code_suivi']) . "</strong> (" . htmlspecialchars($rdv['objet']) . ", le $dateFr à " . substr($rdv['heure_rdv'], 0, 5) . ") est désormais :</p>" .
                "<p style='font-size:16px;'><strong>" . statut_rdv_label($nouveauStatut) . "</strong></p>" .
                ($note ? "<p>Message de la mairie : " . nl2br(htmlspecialchars($note)) . "</p>" : "") .
                "<p>Cordialement,<br>La Mairie de Chérif Lo</p>"
            );
        }

        $stmt->execute(['id' => $id]);
        $rdv = $stmt->fetch();
        $updated = true;
    }
}

$pageTitle = "Rendez-vous " . $rdv['code_suivi'];
$activeAdmin = "rendezvous";
include __DIR__ . '/includes/layout-top.php';
?>

<a href="rendezvous.php" class="text-sm text-muted-foreground hover:text-primary">← Retour aux rendez-vous</a>

<div class="mt-4 flex items-center justify-between flex-wrap gap-4">
  <div>
    <div class="text-xs uppercase tracking-widest text-primary/70 font-mono"><?= htmlspecialchars($rdv['code_suivi']) ?></div>
    <h1 class="mt-1 font-display text-3xl"><?= htmlspecialchars($rdv['objet']) ?></h1>
  </div>
  <span class="rounded-full px-4 py-1.5 text-sm font-semibold <?= statut_rdv_badge_class($rdv['statut']) ?>"><?= statut_rdv_label($rdv['statut']) ?></span>
</div>

<?php if ($updated): ?>
  <div class="mt-6 rounded-xl border border-primary/30 bg-primary/10 p-4 text-sm text-primary">Statut mis à jour. Le demandeur a été notifié automatiquement par e-mail.</div>
<?php endif; ?>

<div class="mt-8 grid lg:grid-cols-3 gap-8">
  <div class="lg:col-span-2 space-y-6">
    <div class="rounded-2xl border border-border bg-card p-6">
      <h2 class="font-display text-lg mb-4">Informations</h2>
      <dl class="grid sm:grid-cols-2 gap-4 text-sm">
        <div><dt class="text-muted-foreground">Nom complet</dt><dd class="mt-0.5 font-medium"><?= htmlspecialchars($rdv['prenom'] . ' ' . $rdv['nom']) ?></dd></div>
        <div><dt class="text-muted-foreground">Créneau</dt><dd class="mt-0.5 font-medium"><?= (new DateTime($rdv['date_rdv']))->format('d/m/Y') ?> à <?= substr($rdv['heure_rdv'], 0, 5) ?></dd></div>
        <div><dt class="text-muted-foreground">Téléphone</dt><dd class="mt-0.5 font-medium"><?= htmlspecialchars($rdv['telephone']) ?></dd></div>
        <div><dt class="text-muted-foreground">E-mail</dt><dd class="mt-0.5 font-medium"><?= htmlspecialchars($rdv['email']) ?></dd></div>
        <?php if ($rdv['motif']): ?>
          <div class="sm:col-span-2"><dt class="text-muted-foreground">Motif</dt><dd class="mt-0.5 font-medium"><?= nl2br(htmlspecialchars($rdv['motif'])) ?></dd></div>
        <?php endif; ?>
      </dl>
    </div>
  </div>

  <div>
    <form method="post" class="rounded-2xl border border-border bg-card p-6 sticky top-6">
      <?= csrf_field() ?>
      <h2 class="font-display text-lg mb-4">Gérer le rendez-vous</h2>
      <label class="text-sm block">
        <span class="text-foreground/85">Statut</span>
        <select name="statut" class="mt-2 w-full rounded-xl border border-input bg-background px-4 py-2.5">
          <?php foreach (['en_attente' => 'En attente', 'confirme' => 'Confirmé', 'annule' => 'Annulé'] as $k => $l): ?>
            <option value="<?= $k ?>" <?= $rdv['statut'] === $k ? 'selected' : '' ?>><?= $l ?></option>
          <?php endforeach; ?>
        </select>
      </label>
      <label class="mt-4 text-sm block">
        <span class="text-foreground/85">Message au demandeur (optionnel)</span>
        <textarea name="note_admin" rows="4" class="mt-2 w-full rounded-xl border border-input bg-background px-4 py-2.5 resize-none"><?= htmlspecialchars($rdv['note_admin'] ?? '') ?></textarea>
      </label>
      <button type="submit" class="mt-5 w-full rounded-full bg-primary text-primary-foreground px-6 py-3 text-sm font-semibold hover:bg-primary-deep transition">
        Enregistrer et notifier
      </button>
      <p class="mt-3 text-xs text-muted-foreground">Un e-mail automatique est envoyé au demandeur si le statut change.</p>
    </form>
  </div>
</div>

<?php include __DIR__ . '/includes/layout-bottom.php'; ?>
