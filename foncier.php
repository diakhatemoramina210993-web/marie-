<?php
$pageTitle = "Service Foncier — Mairie de Chérif Lo";
$pageDescription = "Gestion domaniale, urbanisme et régularisation foncière à la Mairie de Chérif Lo.";
$activePage = "services.php";
include __DIR__ . "/includes/header.php";

$heroEyebrow = "Service Foncier";
$heroTitle = "Gestion domaniale, urbanisme et régularisation foncière";
$heroDescription = "Permis de construire, cession et régularisation de terrains, branchements : toutes les démarches liées au foncier de la commune.";
include __DIR__ . "/includes/page-hero.php";

$faq = [
    ["Comment obtenir un permis de construire ?", "Déposez votre dossier via la démarche « Autorisation de construire » en ligne (plans, pièce d'identité, titre de propriété). Le service urbanisme instruit le dossier sous 30 jours."],
    ["Comment céder un terrain ?", "La cession d'un terrain communal se fait sur demande écrite auprès du service domanial, accompagnée d'un dossier de motivation. Prenez rendez-vous avec le service foncier pour être accompagné dans la procédure."],
    ["Comment régulariser un terrain ?", "Contactez le service domanial avec les documents en votre possession (bail, attestation, etc.). Un dossier de régularisation est alors ouvert et instruit selon la situation du terrain."],
    ["Où se trouve le service de l'urbanisme ?", "Le service de l'urbanisme et du foncier se trouve au sein de la Mairie de Chérif Lo, ouvert du lundi au vendredi de 8h à 17h et le samedi de 9h à 13h."],
    ["Comment obtenir un branchement eau ?", "La demande de branchement au réseau d'eau potable s'effectue auprès du service technique de la mairie, en lien avec la Commission Hydraulique et Énergie. Munissez-vous d'une pièce d'identité et d'un justificatif de propriété ou de résidence."],
];
?>

<section class="container-page py-16">
  <div class="grid lg:grid-cols-3 gap-6 mb-14">
    <div class="lg:col-span-2 rounded-3xl bg-gradient-hero text-primary-foreground p-8 md:p-10 flex flex-col justify-center">
      <div class="text-xs uppercase tracking-[0.24em] text-accent mb-2">Démarche en ligne</div>
      <h2 class="font-display text-2xl md:text-3xl">Demander une autorisation de construire</h2>
      <p class="mt-3 text-primary-foreground/80 max-w-xl">Déposez votre dossier technique directement en ligne et suivez son instruction depuis « Mon espace ».</p>
      <a href="demande.php?type=construire" class="mt-6 inline-flex w-fit items-center gap-2 rounded-full bg-accent text-accent-foreground px-6 py-3 text-sm font-semibold shadow-elegant hover:shadow-lg transition">
        Faire ma demande <i data-lucide="arrow-right" class="h-4 w-4"></i>
      </a>
    </div>
    <div class="rounded-3xl border border-border bg-card p-8 flex flex-col justify-center">
      <div class="h-12 w-12 grid place-items-center rounded-2xl bg-primary/10 text-primary mb-4">
        <i data-lucide="calendar-check-2" class="h-6 w-6"></i>
      </div>
      <h3 class="font-display text-xl">Cession ou régularisation ?</h3>
      <p class="mt-2 text-sm text-muted-foreground">Ces démarches nécessitent un accompagnement personnalisé : prenez rendez-vous avec le service foncier.</p>
      <a href="rendezvous.php?objet=<?= urlencode('Urbanisme') ?>" class="mt-4 text-sm font-medium text-primary hover:text-primary-deep inline-flex items-center gap-1">Prendre rendez-vous <i data-lucide="arrow-right" class="h-3.5 w-3.5"></i></a>
    </div>
  </div>

  <div class="rounded-3xl border border-border bg-card p-8 md:p-10 mb-14">
    <div class="flex items-start gap-5">
      <div class="h-14 w-14 shrink-0 grid place-items-center rounded-2xl bg-gradient-gold text-primary-deep">
        <i data-lucide="landmark" class="h-6 w-6"></i>
      </div>
      <div>
        <div class="text-xs uppercase tracking-[0.24em] text-primary/70">Commission municipale</div>
        <h2 class="mt-1 font-display text-2xl">Commission Domaniale</h2>
        <p class="mt-3 text-muted-foreground leading-relaxed">
          La Commission Domaniale est responsable de la gestion et de l'administration du domaine public et privé de
          la commune. Elle veille à la régularisation foncière, à l'attribution des terrains et à la préservation du
          patrimoine immobilier de Chérif Lo. En assurant une gestion transparente et efficace des biens communaux,
          elle contribue au développement urbain harmonieux et à la valorisation des espaces publics.
        </p>
      </div>
    </div>
  </div>

  <div class="max-w-3xl mx-auto">
    <h2 class="font-display text-2xl mb-6">Questions Fréquentes</h2>
    <div class="space-y-3">
      <?php foreach ($faq as [$q, $a]): ?>
        <details class="group rounded-xl border border-border bg-card px-5 py-4">
          <summary class="flex items-center justify-between cursor-pointer text-sm font-medium text-foreground/90 list-none">
            <?= htmlspecialchars($q) ?>
            <i data-lucide="chevron-down" class="h-4 w-4 text-muted-foreground group-open:rotate-180 transition-transform"></i>
          </summary>
          <p class="mt-3 text-sm text-muted-foreground leading-relaxed"><?= htmlspecialchars($a) ?></p>
        </details>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<?php include __DIR__ . "/includes/footer.php"; ?>
