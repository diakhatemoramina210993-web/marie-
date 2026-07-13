<?php
require __DIR__ . '/includes/auth.php';
require_admin();
require __DIR__ . '/../includes/db.php';
require __DIR__ . '/../includes/functions.php';
require __DIR__ . '/../includes/csrf.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_verify()) {
    $id = (int) ($_POST['id'] ?? 0);
    $statut = $_POST['statut'] ?? '';
    if (in_array($statut, ['non_lu', 'lu', 'traite'], true)) {
        $pdo->prepare("UPDATE messages_contact SET statut = :s WHERE id = :id")->execute(['s' => $statut, 'id' => $id]);
    }
}

$statutFilter = $_GET['statut'] ?? '';
$sql = "SELECT * FROM messages_contact WHERE 1=1";
$params = [];
if ($statutFilter !== '' && in_array($statutFilter, ['non_lu', 'lu', 'traite'], true)) {
    $sql .= " AND statut = :statut";
    $params['statut'] = $statutFilter;
}
$sql .= " ORDER BY created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$messages = $stmt->fetchAll();

$labels = ['non_lu' => 'Non lu', 'lu' => 'Lu', 'traite' => 'Traité'];
$badges = ['non_lu' => 'bg-accent/20 text-primary-deep', 'lu' => 'bg-secondary text-secondary-foreground', 'traite' => 'bg-primary/15 text-primary'];

$pageTitle = "Messages de contact";
$activeAdmin = "messages";
include __DIR__ . '/includes/layout-top.php';
?>

<div class="flex items-center justify-between flex-wrap gap-4 mb-6">
  <h1 class="font-display text-3xl">Messages de contact</h1>
</div>

<form method="get" class="flex flex-wrap gap-3 mb-6">
  <select name="statut" onchange="this.form.submit()" class="rounded-xl border border-input bg-card px-4 py-2.5 text-sm">
    <option value="">Tous les statuts</option>
    <?php foreach ($labels as $k => $l): ?>
      <option value="<?= $k ?>" <?= $statutFilter === $k ? 'selected' : '' ?>><?= $l ?></option>
    <?php endforeach; ?>
  </select>
  <?php if ($statutFilter): ?>
    <a href="messages.php" class="rounded-xl px-4 py-2.5 text-sm text-muted-foreground hover:text-primary">Réinitialiser</a>
  <?php endif; ?>
</form>

<div class="space-y-4">
  <?php if (!$messages): ?>
    <div class="rounded-2xl border border-border bg-card p-6 text-center text-muted-foreground text-sm">Aucun message.</div>
  <?php endif; ?>
  <?php foreach ($messages as $m): ?>
    <div class="rounded-2xl border border-border bg-card p-6">
      <div class="flex items-center justify-between flex-wrap gap-3">
        <div>
          <div class="font-medium"><?= htmlspecialchars($m['sujet']) ?></div>
          <div class="text-xs text-muted-foreground mt-1">
            <?= htmlspecialchars($m['nom']) ?> · <?= htmlspecialchars($m['email']) ?><?= !empty($m['telephone']) ? ' · ' . htmlspecialchars($m['telephone']) : '' ?> · <?= (new DateTime($m['created_at']))->format('d/m/Y H:i') ?>
          </div>
        </div>
        <span class="rounded-full px-3 py-1 text-xs font-semibold <?= $badges[$m['statut']] ?>"><?= $labels[$m['statut']] ?></span>
      </div>
      <p class="mt-4 text-sm text-foreground/85"><?= nl2br(htmlspecialchars($m['message'])) ?></p>
      <form method="post" class="mt-4 flex items-center gap-2">
        <?= csrf_field() ?>
        <input type="hidden" name="id" value="<?= $m['id'] ?>">
        <select name="statut" class="rounded-xl border border-input bg-background px-3 py-2 text-xs">
          <?php foreach ($labels as $k => $l): ?>
            <option value="<?= $k ?>" <?= $m['statut'] === $k ? 'selected' : '' ?>><?= $l ?></option>
          <?php endforeach; ?>
        </select>
        <button type="submit" class="rounded-xl bg-primary text-primary-foreground px-4 py-2 text-xs font-semibold hover:bg-primary-deep transition">Mettre à jour</button>
        <a href="mailto:<?= htmlspecialchars($m['email']) ?>?subject=<?= urlencode('Re: ' . $m['sujet']) ?>" class="rounded-xl border border-input px-4 py-2 text-xs font-medium hover:bg-secondary transition">Répondre par e-mail</a>
        <?php if (!empty($m['telephone'])): ?>
          <a href="<?= htmlspecialchars(whatsapp_link($m['telephone'], "Bonjour " . $m['nom'] . ", suite à votre message \"" . $m['sujet'] . "\" sur le site de la Mairie de Chérif Lo : ")) ?>"
             target="_blank" rel="noopener noreferrer"
             class="inline-flex items-center gap-1.5 rounded-xl bg-[#25D366] text-white px-4 py-2 text-xs font-semibold hover:opacity-90 transition">
            <i data-lucide="message-circle" class="h-3.5 w-3.5"></i> Répondre par WhatsApp
          </a>
        <?php endif; ?>
      </form>
    </div>
  <?php endforeach; ?>
</div>

<?php include __DIR__ . '/includes/layout-bottom.php'; ?>
