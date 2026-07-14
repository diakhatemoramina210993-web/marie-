<?php
$pageTitle = "Mairie de Chérif Lo — Accueil";
$pageDescription = "Bienvenue sur le portail officiel de la commune de Chérif Lo : démarches, actualités, projets et services aux citoyens.";
$activePage = "index.php";
include __DIR__ . "/includes/header.php";

$quickServices = [
    ["icon" => "file-text", "label" => "Acte de naissance", "to" => "etat-civil.php?acte=naissance-extrait"],
    ["icon" => "scroll-text", "label" => "Acte de mariage", "to" => "etat-civil.php?acte=mariage-extrait"],
    ["icon" => "building-2", "label" => "Autorisation de construire", "to" => "demande.php?type=construire"],
    ["icon" => "heart-pulse", "label" => "Couverture Maladie (CMU)", "to" => "demande.php?type=cmu"],
    ["icon" => "graduation-cap", "label" => "Demande de bourse", "to" => "demande.php?type=bourse"],
    ["icon" => "hand-coins", "label" => "Doléances & Audience", "to" => "rendezvous.php"],
];

$news = $pdo->query("SELECT * FROM actualites WHERE statut = 'publie' ORDER BY date_publication DESC, created_at DESC LIMIT 3")->fetchAll();

$stats = [
    ["icon" => "trees", "k" => $SETTINGS['stats_villages'] ?? '18', "v" => "Villages"],
    ["icon" => "users", "k" => $SETTINGS['stats_habitants'] ?? '24 500', "v" => "Population"],
    ["icon" => "folder-check", "k" => $SETTINGS['stats_projets'] ?? '12', "v" => "Projets 2026"],
    ["icon" => "monitor-check", "k" => $SETTINGS['stats_demarches_pct'] ?? '100%', "v" => "Démarches en ligne"],
];
?>

<!-- HERO -->
<section class="relative overflow-hidden bg-gradient-hero text-primary-foreground">
  <div class="absolute inset-0 opacity-40">
    <img src="/mairie/assets/img/hero-cherif-lo.jpg" alt="" class="h-full w-full object-cover" width="1920" height="1200">
  </div>
  <div class="absolute inset-0 bg-primary-deep/70"></div>
  <div class="container-page relative py-24 md:py-32 text-center">
    <h1 class="font-display font-extrabold text-4xl md:text-6xl leading-[1.1] text-balance">
      Une Commune au Service<br class="hidden md:block"> <span class="text-accent">de ses Citoyens</span>
    </h1>
    <div class="mt-5 mx-auto h-1 w-20 rounded-full bg-accent"></div>
    <p class="mt-6 max-w-2xl mx-auto text-lg text-primary-foreground/85">
      Chérif Lo s'engage pour un développement durable et inclusif, au service de tous ses habitants,
      Département de Tivaouane · Arrondissement de Pambal.
    </p>
    <div class="mt-9 flex flex-wrap justify-center gap-3">
      <a href="mairie.php" class="inline-flex items-center gap-2 rounded-full bg-white text-primary-deep px-6 py-3.5 text-sm font-semibold shadow-elegant hover:shadow-lg transition">
        Découvrir la mairie
      </a>
      <a href="services.php" class="group inline-flex items-center gap-2 rounded-full bg-accent text-accent-foreground px-6 py-3.5 text-sm font-semibold shadow-elegant hover:shadow-lg transition">
        Faire une démarche
        <i data-lucide="arrow-right" class="h-4 w-4 transition-transform group-hover:translate-x-1"></i>
      </a>
    </div>

    <dl class="mt-14 grid grid-cols-2 md:grid-cols-4 gap-4 max-w-3xl mx-auto">
      <?php foreach ($stats as $s): ?>
        <div class="rounded-2xl border border-white/15 bg-white/10 backdrop-blur-sm px-4 py-6">
          <i data-lucide="<?= $s['icon'] ?>" class="h-6 w-6 mx-auto text-accent"></i>
          <dt class="mt-3 font-display text-2xl md:text-3xl font-bold"><?= $s['k'] ?></dt>
          <dd class="text-xs uppercase tracking-wider text-primary-foreground/70 mt-1"><?= $s['v'] ?></dd>
        </div>
      <?php endforeach; ?>
    </dl>
  </div>
</section>

<!-- QUICK SERVICES -->
<section class="container-page -mt-16 relative z-10">
  <div class="bg-card rounded-3xl shadow-elegant border border-border p-6 md:p-10">
    <div class="flex items-end justify-between flex-wrap gap-4 mb-8">
      <div>
        <div class="text-xs uppercase tracking-[0.24em] text-primary/70">Accès rapide</div>
        <h2 class="mt-2 font-display text-3xl">Vos démarches essentielles</h2>
      </div>
      <a href="services.php" class="text-sm font-medium text-primary hover:text-primary-deep inline-flex items-center gap-1">
        Voir tout <i data-lucide="arrow-right" class="h-4 w-4"></i>
      </a>
    </div>
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3">
      <?php foreach ($quickServices as $q): ?>
        <a href="<?= $q['to'] ?>" class="group flex flex-col items-start gap-3 rounded-2xl border border-border p-5 hover:border-primary/40 hover:bg-secondary transition">
          <div class="h-11 w-11 grid place-items-center rounded-xl bg-primary/10 text-primary group-hover:bg-primary group-hover:text-primary-foreground transition">
            <i data-lucide="<?= $q['icon'] ?>" class="h-5 w-5"></i>
          </div>
          <span class="text-sm font-medium leading-tight"><?= $q['label'] ?></span>
        </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- MOT DU MAIRE -->
<section class="container-page py-24 grid lg:grid-cols-12 gap-12 items-center">
  <div class="lg:col-span-5">
    <div class="relative mt-6">
      <img src="/mairie/assets/img/maire-portrait.jpg" alt="Portrait du Maire de Chérif Lo" width="900" height="1100" loading="lazy"
        class="rounded-3xl shadow-elegant object-cover w-full aspect-[4/5]">
      <div class="absolute -bottom-6 -right-6 bg-gradient-gold text-primary-deep px-6 py-4 rounded-2xl shadow-soft">
        <div class="text-xs uppercase tracking-widest">Le Maire</div>
        <div class="font-display text-xl">Chérif Lo</div>
      </div>
    </div>
  </div>
  <div class="lg:col-span-7">
    <div class="text-xs uppercase tracking-[0.28em] text-primary/70">Mot du Maire</div>
    <h2 class="mt-3 font-display text-4xl md:text-5xl text-balance">
      « Ensemble, faisons de Chérif Lo une commune forte, unie et tournée vers l'avenir. »
    </h2>
    <p class="mt-6 text-muted-foreground leading-relaxed">
      C'est avec un profond sentiment de fierté et de responsabilité que je vous accueille
      sur le portail officiel de notre commune, ancrée dans la région de Thiès et le terroir du Cayor.
    </p>
    <p class="mt-4 text-muted-foreground leading-relaxed">
      Parmi nos priorités, la jeunesse occupe une place centrale, c'est pourquoi nous portons
      le projet du stade municipal, un espace dédié au sport et au rassemblement de toute la commune.
    </p>
    <a href="mairie.php" class="mt-8 inline-flex items-center gap-2 text-primary font-medium hover:text-primary-deep">
      Lire l'intégralité du message <i data-lucide="arrow-right" class="h-4 w-4"></i>
    </a>
  </div>
</section>

<!-- PILIERS -->
<section class="bg-secondary/60">
  <div class="container-page py-24">
    <div class="max-w-2xl">
      <div class="text-xs uppercase tracking-[0.28em] text-primary/70">Nos priorités</div>
      <h2 class="mt-3 font-display text-4xl md:text-5xl">Une action municipale au service du citoyen</h2>
    </div>
    <div class="mt-12 grid md:grid-cols-3 gap-6">
      <?php
      $piliers = [
          ["icon" => "users", "title" => "Proximité", "text" => "Une administration accessible, à l'écoute de chaque habitant."],
          ["icon" => "shield-check", "title" => "Transparence", "text" => "Budgets, projets et réalisations publiés en toute clarté."],
          ["icon" => "sparkles", "title" => "Innovation", "text" => "Le numérique au service de démarches simples et rapides."],
      ];
      foreach ($piliers as $p): ?>
        <article class="bg-card rounded-3xl p-8 border border-border hover:shadow-elegant transition">
          <div class="h-12 w-12 grid place-items-center rounded-2xl bg-gradient-gold text-primary-deep">
            <i data-lucide="<?= $p['icon'] ?>" class="h-6 w-6"></i>
          </div>
          <h3 class="mt-5 font-display text-2xl"><?= $p['title'] ?></h3>
          <p class="mt-2 text-muted-foreground"><?= $p['text'] ?></p>
        </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- COMMISSIONS -->
<section class="container-page py-24">
  <div class="max-w-2xl mx-auto text-center">
    <div class="text-xs uppercase tracking-[0.28em] text-primary/70">Organisation municipale</div>
    <h2 class="mt-3 font-display text-4xl md:text-5xl">Commissions Municipales</h2>
    <p class="mt-4 text-muted-foreground">17 commissions thématiques pour une gestion efficace et proche des réalités locales.</p>
  </div>
  <div class="mt-12 grid grid-cols-2 md:grid-cols-4 gap-6 max-w-3xl mx-auto">
    <?php
    $commissionsApercu = [
        ["icon" => "heart-pulse", "title" => "Santé", "bg" => "bg-red-100", "color" => "text-red-600"],
        ["icon" => "leaf", "title" => "Environnement", "bg" => "bg-green-100", "color" => "text-green-600"],
        ["icon" => "graduation-cap", "title" => "Éducation", "bg" => "bg-blue-100", "color" => "text-blue-600"],
        ["icon" => "droplet", "title" => "Hydraulique", "bg" => "bg-cyan-100", "color" => "text-cyan-600"],
    ];
    foreach ($commissionsApercu as $c): ?>
      <a href="mairie.php#commissions" class="flex flex-col items-center text-center rounded-3xl border border-border bg-card p-6 hover:shadow-soft hover:border-primary/30 transition">
        <div class="h-14 w-14 grid place-items-center rounded-full <?= $c['bg'] ?> <?= $c['color'] ?>">
          <i data-lucide="<?= $c['icon'] ?>" class="h-6 w-6"></i>
        </div>
        <span class="mt-3 text-sm font-medium"><?= $c['title'] ?></span>
      </a>
    <?php endforeach; ?>
  </div>
  <div class="mt-10 text-center">
    <a href="mairie.php#commissions" class="inline-flex items-center gap-2 rounded-full bg-primary text-primary-foreground px-6 py-3 text-sm font-semibold hover:bg-primary-deep transition">
      Voir toutes les commissions <i data-lucide="arrow-right" class="h-4 w-4"></i>
    </a>
  </div>
</section>

<!-- ACTUALITÉS -->
<section class="container-page py-24">
  <div class="flex items-end justify-between flex-wrap gap-4 mb-10">
    <div>
      <div class="text-xs uppercase tracking-[0.28em] text-primary/70">Actualités</div>
      <h2 class="mt-3 font-display text-4xl md:text-5xl">L'actualité de la commune</h2>
    </div>
    <a href="actualites.php" class="inline-flex items-center gap-2 text-primary font-medium hover:text-primary-deep">
      Toutes les actualités <i data-lucide="arrow-right" class="h-4 w-4"></i>
    </a>
  </div>
  <?php if (!$news): ?>
    <div class="rounded-3xl border border-border bg-card p-10 text-center text-muted-foreground">
      Aucune actualité publiée pour le moment.
    </div>
  <?php endif; ?>
  <div class="grid md:grid-cols-3 gap-6">
    <?php foreach ($news as $n): ?>
      <article class="group rounded-3xl border border-border overflow-hidden bg-card hover:shadow-elegant transition">
        <a href="actualite.php?id=<?= $n['id'] ?>" class="aspect-[16/10] bg-gradient-hero relative overflow-hidden block">
          <img src="<?= htmlspecialchars(actualite_image_url($n)) ?>" alt="" class="absolute inset-0 h-full w-full object-cover <?= $n['image'] ? '' : 'mix-blend-luminosity opacity-80' ?> group-hover:scale-105 transition duration-500" loading="lazy">
          <div class="absolute inset-0 bg-gradient-to-t from-primary-deep/70 via-primary-deep/10 to-transparent"></div>
          <div class="absolute top-4 left-4 rounded-full bg-white/95 text-primary-deep text-xs font-semibold px-3 py-1"><?= htmlspecialchars($n['tag']) ?></div>
          <?php if (!$n['image']): ?>
            <div class="absolute bottom-3 right-3 rounded bg-black/50 text-white text-[10px] px-2 py-0.5">Photo d'illustration</div>
          <?php endif; ?>
        </a>
        <div class="p-6">
          <div class="text-xs text-muted-foreground flex items-center gap-1.5"><i data-lucide="calendar" class="h-3.5 w-3.5"></i><?= format_date_fr($n['date_publication']) ?></div>
          <h3 class="mt-3 font-display text-xl leading-snug group-hover:text-primary transition"><a href="actualite.php?id=<?= $n['id'] ?>"><?= htmlspecialchars($n['titre']) ?></a></h3>
          <p class="mt-2 text-sm text-muted-foreground"><?= htmlspecialchars($n['extrait']) ?></p>
        </div>
      </article>
    <?php endforeach; ?>
  </div>
</section>

<!-- CTA -->
<section class="container-page pb-24">
  <div class="rounded-3xl bg-gradient-hero text-primary-foreground p-10 md:p-16 relative overflow-hidden">
    <div class="absolute -right-24 -top-24 h-80 w-80 rounded-full bg-accent/25 blur-3xl"></div>
    <div class="relative grid md:grid-cols-2 gap-10 items-center">
      <div>
        <div class="inline-flex items-center gap-2 text-xs uppercase tracking-[0.28em] text-accent">
          <i data-lucide="message-circle" class="h-3.5 w-3.5"></i> Une question ?
        </div>
        <h2 class="mt-4 font-display text-4xl md:text-5xl text-balance">Contactez votre mairie en quelques clics.</h2>
        <p class="mt-4 text-primary-foreground/80 max-w-lg">
          Notre équipe vous répond dans les meilleurs délais pour toute demande, doléance ou audience.
        </p>
      </div>
      <div class="flex md:justify-end gap-3 flex-wrap">
        <a href="contact.php" class="rounded-full bg-accent text-accent-foreground px-6 py-3.5 text-sm font-semibold shadow-elegant hover:shadow-lg">
          Nous contacter
        </a>
        <a href="rendezvous.php?objet=<?= urlencode('Doléance') ?>" class="rounded-full border border-white/25 px-6 py-3.5 text-sm font-medium hover:bg-white/10">
          Déposer une doléance
        </a>
      </div>
    </div>
  </div>
</section>

<?php include __DIR__ . "/includes/footer.php"; ?>
