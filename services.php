<?php
$pageTitle = "Services aux usagers — Mairie de Chérif Lo";
$pageDescription = "Effectuez vos démarches en ligne : état civil, mariage, décès, naissance, autorisation de construire.";
$activePage = "services.php";
include __DIR__ . "/includes/header.php";

$services = [
    ["icon" => "file-text", "title" => "Extrait / copie d'acte de naissance", "desc" => "Extraits, copies littérales et attestations délivrés par la mairie.", "delay" => "24 – 72h", "href" => "etat-civil.php?acte=naissance-extrait"],
    ["icon" => "scroll-text", "title" => "Acte de mariage", "desc" => "Extrait d'acte de mariage, sur présentation des références du mariage.", "delay" => "Sur RDV", "href" => "etat-civil.php?acte=mariage-extrait"],
    ["icon" => "heart-crack", "title" => "Acte de décès", "desc" => "Déclaration et délivrance d'actes officiels dans les meilleurs délais.", "delay" => "24h", "href" => "etat-civil.php?acte=deces"],
    ["icon" => "map-pin", "title" => "Certificat de résidence / de vie", "desc" => "Certificats de résidence, de vie individuelle ou collective.", "delay" => "24 – 72h", "href" => "etat-civil.php?acte=certificat-residence"],
    ["icon" => "building-2", "title" => "Autorisation de construire", "desc" => "Dépôt du dossier technique, instruction, notification.", "delay" => "30 jours", "href" => "demande.php?type=construire"],
    ["icon" => "landmark", "title" => "Service Foncier", "desc" => "Gestion domaniale, urbanisme, cession et régularisation de terrains.", "delay" => "Variable", "href" => "foncier.php"],
    ["icon" => "shield-plus", "title" => "Couverture Maladie Universelle (CMU)", "desc" => "Inscription, renouvellement et informations pratiques.", "delay" => "5 – 10 jours", "href" => "demande.php?type=cmu"],
    ["icon" => "graduation-cap", "title" => "Demande de bourse", "desc" => "Soutien financier aux élèves et étudiants méritants.", "delay" => "15 jours", "href" => "demande.php?type=bourse"],
    ["icon" => "calendar-check-2", "title" => "Rendez-vous, doléances & audience", "desc" => "Prenez rendez-vous avec le Maire ou un service de la mairie.", "delay" => "Sous 48h", "href" => "rendezvous.php"],
];
$countServices = count($services);
?>

<!-- HERO avec recherche -->
<section class="relative overflow-hidden bg-gradient-hero text-primary-foreground">
  <div class="container-page relative py-20 md:py-24 text-center">
    <div class="text-xs uppercase tracking-[0.24em] text-accent mb-3">Services aux usagers</div>
    <h1 class="font-display font-extrabold text-3xl md:text-5xl leading-tight text-balance max-w-3xl mx-auto">
      Trouvez votre démarche administrative sans vous déplacer
    </h1>
    <p class="mt-4 max-w-xl mx-auto text-primary-foreground/85">
      Recherchez, introduisez et suivez vos démarches en ligne, 100 % depuis votre domicile.
    </p>
    <div class="mt-8 max-w-xl mx-auto flex gap-2 rounded-full bg-white p-1.5 shadow-elegant">
      <div class="flex-1 flex items-center gap-2 pl-4">
        <i data-lucide="search" class="h-4 w-4 text-muted-foreground shrink-0"></i>
        <input id="demarche-search" type="text" placeholder="Rechercher une démarche (ex : acte de naissance, permis...)"
          class="w-full py-2.5 text-sm text-foreground outline-none bg-transparent placeholder:text-muted-foreground">
      </div>
      <span class="hidden sm:inline-flex items-center rounded-full bg-accent text-accent-foreground px-5 text-sm font-semibold">
        Rechercher
      </span>
    </div>
  </div>
</section>

<section class="container-page py-16">
  <div class="rounded-3xl bg-gradient-hero text-primary-foreground p-8 md:p-10 mb-10 flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
    <div>
      <div class="text-xs uppercase tracking-[0.24em] text-accent mb-2">Formulaire unique</div>
      <h2 class="font-display text-2xl md:text-3xl">Demandez plusieurs actes d'état civil en une seule fois</h2>
      <p class="mt-2 text-primary-foreground/80 max-w-xl">Naissance, mariage, décès, résidence, vie individuelle ou collective : sélectionnez tous les actes dont vous avez besoin dans un même formulaire.</p>
    </div>
    <a href="etat-civil.php" class="shrink-0 inline-flex items-center gap-2 rounded-full bg-accent text-accent-foreground px-6 py-3.5 text-sm font-semibold shadow-elegant hover:shadow-lg transition">
      Faire ma demande <i data-lucide="arrow-right" class="h-4 w-4"></i>
    </a>
  </div>

  <div class="inline-flex items-center gap-2 rounded-full border border-primary/20 bg-primary/5 px-4 py-1.5 text-xs font-semibold text-primary mb-6">
    <i data-lucide="check-circle-2" class="h-3.5 w-3.5"></i> <span id="demarche-count"><?= $countServices ?></span> démarches disponibles en ligne
  </div>

  <div id="demarche-grid" class="grid md:grid-cols-2 gap-6">
    <?php foreach ($services as $s): ?>
      <article data-demarche-title="<?= strtolower(htmlspecialchars($s['title'])) ?>" class="group relative rounded-3xl border border-border bg-card p-8 hover:shadow-elegant transition">
        <span class="absolute top-4 right-4 h-6 w-6 grid place-items-center rounded-full bg-primary text-primary-foreground" title="Démarche disponible en ligne">
          <i data-lucide="check" class="h-3.5 w-3.5"></i>
        </span>
        <div class="flex items-start gap-5">
          <div class="h-14 w-14 shrink-0 grid place-items-center rounded-2xl bg-primary/10 text-primary group-hover:bg-primary group-hover:text-primary-foreground transition">
            <i data-lucide="<?= $s['icon'] ?>" class="h-6 w-6"></i>
          </div>
          <div class="flex-1 pr-6">
            <h3 class="font-display text-2xl"><?= $s['title'] ?></h3>
            <p class="mt-2 text-muted-foreground"><?= $s['desc'] ?></p>
            <div class="mt-5 flex items-center justify-between">
              <span class="text-xs uppercase tracking-widest text-primary/70">Délai · <?= $s['delay'] ?></span>
              <a href="<?= $s['href'] ?>" class="inline-flex items-center gap-2 text-sm font-medium text-primary hover:text-primary-deep">
                Commencer <i data-lucide="arrow-right" class="h-4 w-4"></i>
              </a>
            </div>
          </div>
        </div>
      </article>
    <?php endforeach; ?>
    <p id="demarche-empty" class="hidden col-span-full text-center text-muted-foreground py-10">Aucune démarche ne correspond à votre recherche.</p>
  </div>

  <div class="mt-16 rounded-3xl bg-gradient-hero text-primary-foreground p-10 md:p-14">
    <div class="grid md:grid-cols-2 gap-8 items-center">
      <div>
        <h3 class="font-display text-3xl md:text-4xl">Besoin d'aide pour votre démarche ?</h3>
        <p class="mt-3 text-primary-foreground/80">Notre chatbot répond 24/7 aux questions fréquentes et notre guichet vous accueille du lundi au vendredi.</p>
      </div>
      <div class="flex md:justify-end gap-3 flex-wrap">
        <a href="rendezvous.php" class="rounded-full bg-accent text-accent-foreground px-6 py-3.5 text-sm font-semibold shadow-elegant">Prendre un rendez-vous</a>
        <a href="contact.php" class="rounded-full border border-white/25 px-6 py-3.5 text-sm font-medium hover:bg-white/10">Contacter la mairie</a>
      </div>
    </div>
  </div>
</section>

<script>
  (function () {
    var input = document.getElementById('demarche-search');
    var cards = Array.prototype.slice.call(document.querySelectorAll('#demarche-grid [data-demarche-title]'));
    var counter = document.getElementById('demarche-count');
    var empty = document.getElementById('demarche-empty');
    if (!input) return;
    input.addEventListener('input', function () {
      var q = input.value.trim().toLowerCase();
      var visible = 0;
      cards.forEach(function (card) {
        var match = card.getAttribute('data-demarche-title').indexOf(q) !== -1;
        card.classList.toggle('hidden', !match);
        if (match) visible++;
      });
      counter.textContent = visible;
      empty.classList.toggle('hidden', visible !== 0);
    });
  })();
</script>

<?php include __DIR__ . "/includes/footer.php"; ?>
