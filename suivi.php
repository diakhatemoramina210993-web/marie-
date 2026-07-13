<?php
require __DIR__ . '/includes/db.php';
require __DIR__ . '/includes/functions.php';
require __DIR__ . '/includes/document-types.php';

$code = trim($_GET['code'] ?? $_POST['code'] ?? '');
$result = null;
$resultType = null;
$notFound = false;

if ($code !== '') {
    if (stripos($code, 'DOC-') === 0) {
        $stmt = $pdo->prepare("SELECT * FROM demandes WHERE code_suivi = :code");
        $stmt->execute(['code' => $code]);
        $result = $stmt->fetch();
        $resultType = 'demande';
    } elseif (stripos($code, 'RDV-') === 0) {
        $stmt = $pdo->prepare("SELECT * FROM rendezvous WHERE code_suivi = :code");
        $stmt->execute(['code' => $code]);
        $result = $stmt->fetch();
        $resultType = 'rdv';
    }
    if (!$result) {
        $notFound = true;
    }
}

$pageTitle = "Suivi de dossier — Mairie de Chérif Lo";
$pageDescription = "Suivez l'état d'avancement de votre demande de document ou de votre rendez-vous.";
$activePage = "";
include __DIR__ . "/includes/header.php";

$heroEyebrow = "Suivi de dossier";
$heroTitle = "Où en est ma démarche ?";
$heroDescription = "Saisissez le code de suivi reçu par e-mail lors de votre demande.";
include __DIR__ . "/includes/page-hero.php";
?>

<section class="container-page py-20">
  <form method="get" action="suivi.php" class="max-w-xl mx-auto flex gap-3">
    <input name="code" value="<?= htmlspecialchars($code) ?>" placeholder="Ex : DOC-2607-A1B2C3" required
      class="flex-1 rounded-xl border border-input bg-background px-4 py-3 outline-none focus:border-primary uppercase">
    <button type="submit" class="rounded-xl bg-primary text-primary-foreground px-6 py-3 text-sm font-semibold hover:bg-primary-deep transition">
      Rechercher
    </button>
  </form>

  <?php if ($notFound): ?>
    <div class="max-w-xl mx-auto mt-8 rounded-2xl border border-destructive/30 bg-destructive/10 p-5 text-sm text-destructive text-center">
      Aucun dossier ne correspond à ce code. Vérifiez la saisie (format DOC-XXXXXX ou RDV-XXXXXX).
    </div>
  <?php endif; ?>

  <?php if ($result && $resultType === 'demande'):
    $details = json_decode($result['details_json'] ?? '[]', true) ?: [];
    $typeInfo = $DOCUMENT_TYPES[$result['type_document']] ?? null;
  ?>
    <div class="max-w-2xl mx-auto mt-10 rounded-3xl border border-border bg-card p-8 md:p-10 shadow-soft">
      <div class="flex items-center justify-between flex-wrap gap-3">
        <div>
          <div class="text-xs uppercase tracking-widest text-primary/70">Demande de document</div>
          <h2 class="mt-1 font-display text-2xl"><?= htmlspecialchars($typeInfo['label'] ?? $result['type_document']) ?></h2>
        </div>
        <span class="rounded-full px-4 py-1.5 text-xs font-semibold <?= statut_demande_badge_class($result['statut']) ?>">
          <?= statut_demande_label($result['statut']) ?>
        </span>
      </div>

      <dl class="mt-8 grid sm:grid-cols-2 gap-5 text-sm">
        <div><dt class="text-muted-foreground">Code de suivi</dt><dd class="mt-1 font-medium"><?= htmlspecialchars($result['code_suivi']) ?></dd></div>
        <div><dt class="text-muted-foreground">Déposée le</dt><dd class="mt-1 font-medium"><?= (new DateTime($result['created_at']))->format('d/m/Y à H:i') ?></dd></div>
        <div><dt class="text-muted-foreground">Demandeur</dt><dd class="mt-1 font-medium"><?= htmlspecialchars($result['civilite'] . ' ' . $result['prenom'] . ' ' . $result['nom']) ?></dd></div>
        <div><dt class="text-muted-foreground">Mode de retrait</dt><dd class="mt-1 font-medium"><?= $result['mode_retrait'] === 'postal' ? 'Envoi postal' : 'Retrait au guichet' ?></dd></div>
      </dl>

      <?php if (!empty($result['note_admin'])): ?>
        <div class="mt-6 rounded-xl bg-secondary p-4 text-sm">
          <div class="text-xs uppercase tracking-widest text-primary/70 mb-1">Message de la mairie</div>
          <?= nl2br(htmlspecialchars($result['note_admin'])) ?>
        </div>
      <?php endif; ?>
    </div>

  <?php elseif ($result && $resultType === 'rdv'): ?>
    <div class="max-w-2xl mx-auto mt-10 rounded-3xl border border-border bg-card p-8 md:p-10 shadow-soft">
      <div class="flex items-center justify-between flex-wrap gap-3">
        <div>
          <div class="text-xs uppercase tracking-widest text-primary/70">Rendez-vous</div>
          <h2 class="mt-1 font-display text-2xl"><?= htmlspecialchars($result['objet']) ?></h2>
        </div>
        <span class="rounded-full px-4 py-1.5 text-xs font-semibold <?= statut_rdv_badge_class($result['statut']) ?>">
          <?= statut_rdv_label($result['statut']) ?>
        </span>
      </div>

      <dl class="mt-8 grid sm:grid-cols-2 gap-5 text-sm">
        <div><dt class="text-muted-foreground">Code de suivi</dt><dd class="mt-1 font-medium"><?= htmlspecialchars($result['code_suivi']) ?></dd></div>
        <div><dt class="text-muted-foreground">Créneau demandé</dt><dd class="mt-1 font-medium"><?= (new DateTime($result['date_rdv']))->format('d/m/Y') ?> à <?= substr($result['heure_rdv'], 0, 5) ?></dd></div>
        <div><dt class="text-muted-foreground">Demandeur</dt><dd class="mt-1 font-medium"><?= htmlspecialchars($result['prenom'] . ' ' . $result['nom']) ?></dd></div>
        <div><dt class="text-muted-foreground">Contact</dt><dd class="mt-1 font-medium"><?= htmlspecialchars($result['telephone']) ?></dd></div>
      </dl>

      <?php if (!empty($result['note_admin'])): ?>
        <div class="mt-6 rounded-xl bg-secondary p-4 text-sm">
          <div class="text-xs uppercase tracking-widest text-primary/70 mb-1">Message de la mairie</div>
          <?= nl2br(htmlspecialchars($result['note_admin'])) ?>
        </div>
      <?php endif; ?>
    </div>
  <?php endif; ?>
</section>

<?php include __DIR__ . "/includes/footer.php"; ?>
