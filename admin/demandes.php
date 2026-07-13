<?php
require __DIR__ . '/includes/auth.php';
require_admin();
require __DIR__ . '/../includes/db.php';
require __DIR__ . '/../includes/functions.php';
require __DIR__ . '/../includes/document-types.php';
require __DIR__ . '/../includes/csrf.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'accept' && csrf_verify()) {
    $id = (int) ($_POST['id'] ?? 0);
    $stmt = $pdo->prepare("SELECT * FROM demandes WHERE id = :id");
    $stmt->execute(['id' => $id]);
    $d = $stmt->fetch();
    if ($d && $d['statut'] === 'recue') {
        $pdo->prepare("UPDATE demandes SET statut = 'en_cours' WHERE id = :id")->execute(['id' => $id]);
        send_notification(
            $d['email'],
            "Mise à jour de votre demande " . $d['code_suivi'],
            "<p>Bonjour " . htmlspecialchars($d['prenom'] . ' ' . $d['nom']) . ",</p>" .
            "<p>Votre demande <strong>" . htmlspecialchars($d['code_suivi']) . "</strong> a été acceptée et est maintenant en cours de traitement.</p>" .
            "<p>Cordialement,<br>La Mairie de Chérif Lo</p>"
        );
    }
    header('Location: demandes.php' . (!empty($_POST['back_qs']) ? '?' . $_POST['back_qs'] : ''));
    exit;
}

$statutFilter = $_GET['statut'] ?? '';
$typeFilter = $_GET['type'] ?? '';

$sql = "SELECT * FROM demandes WHERE 1=1";
$params = [];
if ($statutFilter !== '' && in_array($statutFilter, ['recue', 'en_cours', 'prete', 'rejetee'], true)) {
    $sql .= " AND statut = :statut";
    $params['statut'] = $statutFilter;
}
if ($typeFilter !== '' && isset($DOCUMENT_TYPES[$typeFilter])) {
    $sql .= " AND type_document = :type";
    $params['type'] = $typeFilter;
}
$sql .= " ORDER BY created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$demandes = $stmt->fetchAll();

$pageTitle = "Demandes de documents";
$activeAdmin = "demandes";
include __DIR__ . '/includes/layout-top.php';
?>

<div class="flex items-center justify-between flex-wrap gap-4 mb-6">
  <h1 class="font-display text-3xl">Demandes de documents</h1>
</div>

<form method="get" class="flex flex-wrap gap-3 mb-6">
  <select name="statut" onchange="this.form.submit()" class="rounded-xl border border-input bg-card px-4 py-2.5 text-sm">
    <option value="">Tous les statuts</option>
    <?php foreach (['recue' => 'Reçue', 'en_cours' => 'En cours', 'prete' => 'Prête', 'rejetee' => 'Rejetée'] as $k => $l): ?>
      <option value="<?= $k ?>" <?= $statutFilter === $k ? 'selected' : '' ?>><?= $l ?></option>
    <?php endforeach; ?>
  </select>
  <select name="type" onchange="this.form.submit()" class="rounded-xl border border-input bg-card px-4 py-2.5 text-sm">
    <option value="">Tous les types</option>
    <?php foreach ($DOCUMENT_TYPES as $slug => $info): ?>
      <option value="<?= $slug ?>" <?= $typeFilter === $slug ? 'selected' : '' ?>><?= htmlspecialchars($info['label']) ?></option>
    <?php endforeach; ?>
  </select>
  <?php if ($statutFilter || $typeFilter): ?>
    <a href="demandes.php" class="rounded-xl px-4 py-2.5 text-sm text-muted-foreground hover:text-primary">Réinitialiser</a>
  <?php endif; ?>
</form>

<div class="rounded-2xl border border-border bg-card overflow-x-auto">
  <table class="w-full text-sm">
    <thead class="bg-secondary/60 text-left">
      <tr>
        <th class="p-4 font-medium">Code</th>
        <th class="p-4 font-medium">Type</th>
        <th class="p-4 font-medium">Demandeur</th>
        <th class="p-4 font-medium">Date</th>
        <th class="p-4 font-medium">Statut</th>
        <th class="p-4"></th>
      </tr>
    </thead>
    <tbody class="divide-y divide-border">
      <?php if (!$demandes): ?>
        <tr><td colspan="6" class="p-6 text-center text-muted-foreground">Aucune demande trouvée.</td></tr>
      <?php endif; ?>
      <?php foreach ($demandes as $d): ?>
        <tr class="hover:bg-secondary/40">
          <td class="p-4 font-mono text-xs"><?= htmlspecialchars($d['code_suivi']) ?></td>
          <td class="p-4"><?= htmlspecialchars($DOCUMENT_TYPES[$d['type_document']]['label'] ?? $d['type_document']) ?></td>
          <td class="p-4"><?= htmlspecialchars($d['prenom'] . ' ' . $d['nom']) ?></td>
          <td class="p-4 text-muted-foreground"><?= (new DateTime($d['created_at']))->format('d/m/Y H:i') ?></td>
          <td class="p-4"><span class="rounded-full px-3 py-1 text-xs font-semibold <?= statut_demande_badge_class($d['statut']) ?>"><?= statut_demande_label($d['statut']) ?></span></td>
          <td class="p-4">
            <div class="flex items-center gap-3">
              <?php if ($d['statut'] === 'recue'): ?>
                <form method="post" onsubmit="return confirm('Accepter cette demande et prévenir le citoyen par e-mail ?');">
                  <?= csrf_field() ?>
                  <input type="hidden" name="action" value="accept">
                  <input type="hidden" name="id" value="<?= $d['id'] ?>">
                  <input type="hidden" name="back_qs" value="<?= htmlspecialchars(http_build_query(['statut' => $statutFilter, 'type' => $typeFilter])) ?>">
                  <button type="submit" class="inline-flex items-center gap-1 text-primary hover:text-primary-deep font-medium">
                    <i data-lucide="check" class="h-3.5 w-3.5"></i> Accepter
                  </button>
                </form>
              <?php endif; ?>
              <a href="demande-detail.php?id=<?= $d['id'] ?>" class="text-primary hover:text-primary-deep font-medium">Voir →</a>
            </div>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<?php include __DIR__ . '/includes/layout-bottom.php'; ?>
