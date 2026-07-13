<?php
require __DIR__ . '/includes/auth.php';
require_admin();
require __DIR__ . '/../includes/db.php';
require __DIR__ . '/../includes/functions.php';
require __DIR__ . '/../includes/document-types.php';

$counts = [
    'demandes_nouvelles' => (int) $pdo->query("SELECT COUNT(*) FROM demandes WHERE statut = 'recue'")->fetchColumn(),
    'demandes_total' => (int) $pdo->query("SELECT COUNT(*) FROM demandes")->fetchColumn(),
    'rdv_attente' => (int) $pdo->query("SELECT COUNT(*) FROM rendezvous WHERE statut = 'en_attente'")->fetchColumn(),
    'rdv_total' => (int) $pdo->query("SELECT COUNT(*) FROM rendezvous")->fetchColumn(),
    'messages_non_lus' => (int) $pdo->query("SELECT COUNT(*) FROM messages_contact WHERE statut = 'non_lu'")->fetchColumn(),
];

$dernieresDemandes = $pdo->query("SELECT * FROM demandes ORDER BY created_at DESC LIMIT 5")->fetchAll();
$prochainRdv = $pdo->query("SELECT * FROM rendezvous WHERE date_rdv >= CURDATE() AND statut != 'annule' ORDER BY date_rdv ASC, heure_rdv ASC LIMIT 5")->fetchAll();

$pageTitle = "Tableau de bord";
$activeAdmin = "dashboard";
include __DIR__ . '/includes/layout-top.php';
?>

<div class="flex items-center justify-between flex-wrap gap-4 mb-8">
  <div>
    <div class="text-xs uppercase tracking-widest text-primary/70">Bienvenue</div>
    <h1 class="mt-1 font-display text-3xl"><?= htmlspecialchars($_SESSION['admin_name'] ?? 'Administrateur') ?></h1>
  </div>
</div>

<div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5">
  <a href="demandes.php?statut=recue" class="rounded-3xl border border-border bg-card p-6 hover:shadow-soft transition">
    <div class="flex items-center justify-between">
      <div class="h-11 w-11 grid place-items-center rounded-xl bg-primary/10 text-primary"><i data-lucide="file-text" class="h-5 w-5"></i></div>
      <span class="font-display text-3xl"><?= $counts['demandes_nouvelles'] ?></span>
    </div>
    <p class="mt-4 text-sm font-medium">Nouvelles demandes à traiter</p>
    <p class="text-xs text-muted-foreground mt-1"><?= $counts['demandes_total'] ?> demandes au total</p>
  </a>

  <a href="rendezvous.php?statut=en_attente" class="rounded-3xl border border-border bg-card p-6 hover:shadow-soft transition">
    <div class="flex items-center justify-between">
      <div class="h-11 w-11 grid place-items-center rounded-xl bg-accent/20 text-primary-deep"><i data-lucide="calendar-check-2" class="h-5 w-5"></i></div>
      <span class="font-display text-3xl"><?= $counts['rdv_attente'] ?></span>
    </div>
    <p class="mt-4 text-sm font-medium">Rendez-vous à confirmer</p>
    <p class="text-xs text-muted-foreground mt-1"><?= $counts['rdv_total'] ?> rendez-vous au total</p>
  </a>

  <a href="messages.php?statut=non_lu" class="rounded-3xl border border-border bg-card p-6 hover:shadow-soft transition">
    <div class="flex items-center justify-between">
      <div class="h-11 w-11 grid place-items-center rounded-xl bg-secondary text-secondary-foreground"><i data-lucide="mail" class="h-5 w-5"></i></div>
      <span class="font-display text-3xl"><?= $counts['messages_non_lus'] ?></span>
    </div>
    <p class="mt-4 text-sm font-medium">Messages non lus</p>
    <p class="text-xs text-muted-foreground mt-1">Formulaire de contact</p>
  </a>
</div>

<div class="mt-10 grid lg:grid-cols-2 gap-8">
  <div>
    <div class="flex items-center justify-between mb-4">
      <h2 class="font-display text-xl">Dernières demandes</h2>
      <a href="demandes.php" class="text-sm text-primary hover:text-primary-deep">Tout voir</a>
    </div>
    <div class="rounded-2xl border border-border bg-card divide-y divide-border">
      <?php if (!$dernieresDemandes): ?>
        <p class="p-5 text-sm text-muted-foreground">Aucune demande pour le moment.</p>
      <?php endif; ?>
      <?php foreach ($dernieresDemandes as $d): ?>
        <a href="demande-detail.php?id=<?= $d['id'] ?>" class="flex items-center justify-between gap-3 p-4 hover:bg-secondary/50 transition">
          <div class="min-w-0">
            <div class="text-sm font-medium truncate"><?= htmlspecialchars($DOCUMENT_TYPES[$d['type_document']]['label'] ?? $d['type_document']) ?></div>
            <div class="text-xs text-muted-foreground"><?= htmlspecialchars($d['prenom'] . ' ' . $d['nom']) ?> · <?= $d['code_suivi'] ?></div>
          </div>
          <span class="shrink-0 rounded-full px-3 py-1 text-xs font-semibold <?= statut_demande_badge_class($d['statut']) ?>"><?= statut_demande_label($d['statut']) ?></span>
        </a>
      <?php endforeach; ?>
    </div>
  </div>

  <div>
    <div class="flex items-center justify-between mb-4">
      <h2 class="font-display text-xl">Prochains rendez-vous</h2>
      <a href="rendezvous.php" class="text-sm text-primary hover:text-primary-deep">Tout voir</a>
    </div>
    <div class="rounded-2xl border border-border bg-card divide-y divide-border">
      <?php if (!$prochainRdv): ?>
        <p class="p-5 text-sm text-muted-foreground">Aucun rendez-vous à venir.</p>
      <?php endif; ?>
      <?php foreach ($prochainRdv as $r): ?>
        <a href="rdv-detail.php?id=<?= $r['id'] ?>" class="flex items-center justify-between gap-3 p-4 hover:bg-secondary/50 transition">
          <div class="min-w-0">
            <div class="text-sm font-medium truncate"><?= htmlspecialchars($r['objet']) ?></div>
            <div class="text-xs text-muted-foreground"><?= htmlspecialchars($r['prenom'] . ' ' . $r['nom']) ?> · <?= (new DateTime($r['date_rdv']))->format('d/m/Y') ?> à <?= substr($r['heure_rdv'], 0, 5) ?></div>
          </div>
          <span class="shrink-0 rounded-full px-3 py-1 text-xs font-semibold <?= statut_rdv_badge_class($r['statut']) ?>"><?= statut_rdv_label($r['statut']) ?></span>
        </a>
      <?php endforeach; ?>
    </div>
  </div>
</div>

<?php include __DIR__ . '/includes/layout-bottom.php'; ?>
