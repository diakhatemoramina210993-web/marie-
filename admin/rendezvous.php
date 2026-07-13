<?php
require __DIR__ . '/includes/auth.php';
require_admin();
require __DIR__ . '/../includes/db.php';
require __DIR__ . '/../includes/functions.php';
require __DIR__ . '/../includes/csrf.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'accept' && csrf_verify()) {
    $id = (int) ($_POST['id'] ?? 0);
    $stmt = $pdo->prepare("SELECT * FROM rendezvous WHERE id = :id");
    $stmt->execute(['id' => $id]);
    $r = $stmt->fetch();
    if ($r && $r['statut'] === 'en_attente') {
        $pdo->prepare("UPDATE rendezvous SET statut = 'confirme' WHERE id = :id")->execute(['id' => $id]);
        $dateFr = (new DateTime($r['date_rdv']))->format('d/m/Y');
        send_notification(
            $r['email'],
            "Votre rendez-vous " . $r['code_suivi'] . " est confirmé",
            "<p>Bonjour " . htmlspecialchars($r['prenom'] . ' ' . $r['nom']) . ",</p>" .
            "<p>Votre rendez-vous <strong>" . htmlspecialchars($r['code_suivi']) . "</strong> (" . htmlspecialchars($r['objet']) . ") du $dateFr à " . substr($r['heure_rdv'], 0, 5) . " est confirmé.</p>" .
            "<p>Cordialement,<br>La Mairie de Chérif Lo</p>"
        );
    }
    header('Location: rendezvous.php' . (!empty($_POST['back_qs']) ? '?' . $_POST['back_qs'] : ''));
    exit;
}

$statutFilter = $_GET['statut'] ?? '';

$sql = "SELECT * FROM rendezvous WHERE 1=1";
$params = [];
if ($statutFilter !== '' && in_array($statutFilter, ['en_attente', 'confirme', 'annule'], true)) {
    $sql .= " AND statut = :statut";
    $params['statut'] = $statutFilter;
}
$sql .= " ORDER BY date_rdv ASC, heure_rdv ASC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rdvs = $stmt->fetchAll();

$pageTitle = "Rendez-vous";
$activeAdmin = "rendezvous";
include __DIR__ . '/includes/layout-top.php';
?>

<div class="flex items-center justify-between flex-wrap gap-4 mb-6">
  <h1 class="font-display text-3xl">Rendez-vous</h1>
</div>

<form method="get" class="flex flex-wrap gap-3 mb-6">
  <select name="statut" onchange="this.form.submit()" class="rounded-xl border border-input bg-card px-4 py-2.5 text-sm">
    <option value="">Tous les statuts</option>
    <?php foreach (['en_attente' => 'En attente', 'confirme' => 'Confirmé', 'annule' => 'Annulé'] as $k => $l): ?>
      <option value="<?= $k ?>" <?= $statutFilter === $k ? 'selected' : '' ?>><?= $l ?></option>
    <?php endforeach; ?>
  </select>
  <?php if ($statutFilter): ?>
    <a href="rendezvous.php" class="rounded-xl px-4 py-2.5 text-sm text-muted-foreground hover:text-primary">Réinitialiser</a>
  <?php endif; ?>
</form>

<div class="rounded-2xl border border-border bg-card overflow-x-auto">
  <table class="w-full text-sm">
    <thead class="bg-secondary/60 text-left">
      <tr>
        <th class="p-4 font-medium">Code</th>
        <th class="p-4 font-medium">Objet</th>
        <th class="p-4 font-medium">Demandeur</th>
        <th class="p-4 font-medium">Créneau</th>
        <th class="p-4 font-medium">Statut</th>
        <th class="p-4"></th>
      </tr>
    </thead>
    <tbody class="divide-y divide-border">
      <?php if (!$rdvs): ?>
        <tr><td colspan="6" class="p-6 text-center text-muted-foreground">Aucun rendez-vous trouvé.</td></tr>
      <?php endif; ?>
      <?php foreach ($rdvs as $r): ?>
        <tr class="hover:bg-secondary/40">
          <td class="p-4 font-mono text-xs"><?= htmlspecialchars($r['code_suivi']) ?></td>
          <td class="p-4"><?= htmlspecialchars($r['objet']) ?></td>
          <td class="p-4"><?= htmlspecialchars($r['prenom'] . ' ' . $r['nom']) ?></td>
          <td class="p-4 text-muted-foreground"><?= (new DateTime($r['date_rdv']))->format('d/m/Y') ?> à <?= substr($r['heure_rdv'], 0, 5) ?></td>
          <td class="p-4"><span class="rounded-full px-3 py-1 text-xs font-semibold <?= statut_rdv_badge_class($r['statut']) ?>"><?= statut_rdv_label($r['statut']) ?></span></td>
          <td class="p-4">
            <div class="flex items-center gap-3">
              <?php if ($r['statut'] === 'en_attente'): ?>
                <form method="post" onsubmit="return confirm('Confirmer ce rendez-vous et prévenir le citoyen par e-mail ?');">
                  <?= csrf_field() ?>
                  <input type="hidden" name="action" value="accept">
                  <input type="hidden" name="id" value="<?= $r['id'] ?>">
                  <input type="hidden" name="back_qs" value="<?= htmlspecialchars(http_build_query(['statut' => $statutFilter])) ?>">
                  <button type="submit" class="inline-flex items-center gap-1 text-primary hover:text-primary-deep font-medium">
                    <i data-lucide="check" class="h-3.5 w-3.5"></i> Accepter
                  </button>
                </form>
              <?php endif; ?>
              <a href="rdv-detail.php?id=<?= $r['id'] ?>" class="text-primary hover:text-primary-deep font-medium">Voir →</a>
            </div>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<?php include __DIR__ . '/includes/layout-bottom.php'; ?>
