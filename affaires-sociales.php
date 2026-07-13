<?php
$pageTitle = "Affaires Sociales — Mairie de Chérif Lo";
$pageDescription = "Prise en charge médicale, CMU, bourses, doléances, audiences et soutien aux citoyens.";
$activePage = "affaires-sociales.php";
include __DIR__ . "/includes/header.php";

$heroEyebrow = "Affaires sociales &amp; Citoyenneté";
$heroTitle = "Une commune solidaire et à l'écoute.";
$heroDescription = "Nous plaçons le lien social et l'accompagnement des habitants au cœur de notre action.";
include __DIR__ . "/includes/page-hero.php";

$items = [
    ["icon" => "heart-pulse", "title" => "Prise en charge médicale", "text" => "Accompagnement des cas urgents et orientation vers les structures partenaires.", "href" => "rendezvous.php?objet=" . urlencode("Affaires sociales")],
    ["icon" => "shield-plus", "title" => "Couverture Maladie Universelle", "text" => "Inscription à la CMU, renouvellement et informations pratiques.", "href" => "demande.php?type=cmu"],
    ["icon" => "graduation-cap", "title" => "Demande de bourse", "text" => "Soutien financier aux élèves et étudiants méritants.", "href" => "demande.php?type=bourse"],
    ["icon" => "hand-heart", "title" => "Appui et soutien", "text" => "Aides ponctuelles aux familles vulnérables et aux personnes âgées.", "href" => "rendezvous.php?objet=" . urlencode("Affaires sociales")],
    ["icon" => "message-circle", "title" => "Audience", "text" => "Prise de rendez-vous avec le Maire ou les services.", "href" => "rendezvous.php?objet=" . urlencode("Audience avec le Maire")],
    ["icon" => "users", "title" => "Doléances", "text" => "Formulez vos demandes et requêtes, suivies par le service dédié.", "href" => "rendezvous.php?objet=" . urlencode("Doléance")],
    ["icon" => "briefcase", "title" => "Carrière", "text" => "Offres, appels à candidature et concours de recrutement.", "href" => "contact.php"],
    ["icon" => "map-pin", "title" => "Lieux à visiter", "text" => "Sites patrimoniaux, culturels et touristiques de la commune.", "href" => "mediatheque.php"],
    ["icon" => "handshake", "title" => "Partenaires", "text" => "Nos partenaires publics, privés et associatifs.", "href" => "contact.php"],
];
?>

<section class="container-page py-20 grid md:grid-cols-2 lg:grid-cols-3 gap-6">
  <?php foreach ($items as $it): ?>
    <div class="group rounded-3xl border border-border bg-card p-7 hover:border-primary/40 hover:shadow-soft transition">
      <div class="h-12 w-12 grid place-items-center rounded-2xl bg-gradient-gold text-primary-deep">
        <i data-lucide="<?= $it['icon'] ?>" class="h-6 w-6"></i>
      </div>
      <h3 class="mt-5 font-display text-xl"><?= $it['title'] ?></h3>
      <p class="mt-2 text-sm text-muted-foreground"><?= $it['text'] ?></p>
      <a href="<?= $it['href'] ?>" class="mt-4 inline-block text-sm font-medium text-primary hover:text-primary-deep">Demander →</a>
    </div>
  <?php endforeach; ?>
</section>

<?php include __DIR__ . "/includes/footer.php"; ?>
