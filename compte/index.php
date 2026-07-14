<?php
require __DIR__ . '/../includes/citizen-auth.php';
require __DIR__ . '/../includes/db.php';
require __DIR__ . '/../includes/functions.php';
require __DIR__ . '/../includes/document-types.php';

require_citizen();
$citoyen = current_citoyen($pdo);

$stmtD = $pdo->prepare("SELECT * FROM demandes WHERE citoyen_id = :id ORDER BY created_at DESC");
$stmtD->execute(['id' => $citoyen['id']]);
$mesDemandes = $stmtD->fetchAll();

$stmtR = $pdo->prepare("SELECT * FROM rendezvous WHERE citoyen_id = :id ORDER BY date_rdv DESC, heure_rdv DESC");
$stmtR->execute(['id' => $citoyen['id']]);
$mesRdv = $stmtR->fetchAll();

$pageTitle = "Mon espace — Mairie de Chérif Lo";
$pageDescription = "Retrouvez l'historique et le statut de toutes vos démarches auprès de la Mairie de Chérif Lo.";
$activePage = "";
include __DIR__ . "/../includes/header.php";
?>

<section class="bg-gradient-hero text-primary-foreground">
  <div class="container-page py-16 flex items-center justify-between flex-wrap gap-6">
    <div>
      <div class="text-xs uppercase tracking-[0.28em] text-accent mb-2">Mon espace</div>
      <h1 class="font-display text-3xl md:text-4xl">Bonjour <?= htmlspecialchars($citoyen['prenom']) ?> 👋</h1>
      <p class="mt-2 text-primary-foreground/80"><?= htmlspecialchars($citoyen['telephone']) ?><?= !empty($citoyen['email']) ? ' · ' . htmlspecialchars($citoyen['email']) : '' ?></p>
    </div>
    <a href="/mairie/compte/deconnexion.php" class="rounded-full border border-white/25 px-5 py-2.5 text-sm font-medium hover:bg-white/10 transition">Déconnexion</a>
  </div>
</section>

<section class="container-page py-16">
  <div class="flex flex-wrap gap-3 mb-10">
    <a href="/mairie/demande.php" class="rounded-full bg-primary text-primary-foreground px-5 py-2.5 text-sm font-semibold hover:bg-primary-deep transition">+ Nouvelle demande de document</a>
    <a href="/mairie/rendezvous.php" class="rounded-full border border-input px-5 py-2.5 text-sm font-medium hover:bg-secondary transition">+ Prendre rendez-vous</a>
  </div>

  <div class="grid lg:grid-cols-2 gap-10">
    <div>
      <h2 class="font-display text-2xl mb-4">Mes demandes de documents</h2>
      <div class="rounded-2xl border border-border bg-card divide-y divide-border">
        <?php if (!$mesDemandes): ?>
          <p class="p-6 text-sm text-muted-foreground">Vous n'avez encore déposé aucune demande.</p>
        <?php endif; ?>
        <?php foreach ($mesDemandes as $d): ?>
          <div class="p-5 flex items-start justify-between gap-4">
            <div class="min-w-0">
              <div class="text-sm font-medium"><?= htmlspecialchars($DOCUMENT_TYPES[$d['type_document']]['label'] ?? $d['type_document']) ?></div>
              <div class="text-xs text-muted-foreground mt-1 font-mono"><?= htmlspecialchars($d['code_suivi']) ?></div>
              <div class="text-xs text-muted-foreground mt-1"><?= (new DateTime($d['created_at']))->format('d/m/Y') ?></div>
              <?php if (!empty($d['note_admin'])): ?>
                <div class="mt-2 text-xs rounded-lg bg-secondary p-2"><?= nl2br(htmlspecialchars($d['note_admin'])) ?></div>
              <?php endif; ?>
            </div>
            <span class="shrink-0 rounded-full px-3 py-1 text-xs font-semibold <?= statut_demande_badge_class($d['statut']) ?>"><?= statut_demande_label($d['statut']) ?></span>
          </div>
        <?php endforeach; ?>
      </div>
    </div>

    <div>
      <h2 class="font-display text-2xl mb-4">Mes rendez-vous</h2>
      <div class="rounded-2xl border border-border bg-card divide-y divide-border">
        <?php if (!$mesRdv): ?>
          <p class="p-6 text-sm text-muted-foreground">Vous n'avez encore pris aucun rendez-vous.</p>
        <?php endif; ?>
        <?php foreach ($mesRdv as $r): ?>
          <div class="p-5 flex items-start justify-between gap-4">
            <div class="min-w-0">
              <div class="text-sm font-medium"><?= htmlspecialchars($r['objet']) ?></div>
              <div class="text-xs text-muted-foreground mt-1 font-mono"><?= htmlspecialchars($r['code_suivi']) ?></div>
              <div class="text-xs text-muted-foreground mt-1"><?= (new DateTime($r['date_rdv']))->format('d/m/Y') ?> à <?= substr($r['heure_rdv'], 0, 5) ?></div>
              <?php if (!empty($r['note_admin'])): ?>
                <div class="mt-2 text-xs rounded-lg bg-secondary p-2"><?= nl2br(htmlspecialchars($r['note_admin'])) ?></div>
              <?php endif; ?>
            </div>
            <span class="shrink-0 rounded-full px-3 py-1 text-xs font-semibold <?= statut_rdv_badge_class($r['statut']) ?>"><?= statut_rdv_label($r['statut']) ?></span>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</section>

<?php include __DIR__ . "/../includes/footer.php"; ?>
