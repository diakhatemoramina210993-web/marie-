<?php
$pageTitle = "La Mairie — Chérif Lo";
$pageDescription = "Présentation de la Mairie de Chérif Lo : le Maire, le cabinet, le conseil municipal, les commissions et l'administration.";
$activePage = "mairie.php";
include __DIR__ . "/includes/header.php";

$heroEyebrow = "Présentation";
$heroTitle = "La Mairie de Chérif Lo";
$heroDescription = "Une institution au service des citoyens, guidée par la proximité, la transparence et l'engagement.";
include __DIR__ . "/includes/page-hero.php";

$sections = [
    ["title" => "Le Maire", "text" => "Élu de proximité, il représente la commune et met en œuvre les décisions du Conseil municipal."],
    ["title" => "Cabinet du Maire", "text" => "L'équipe rapprochée qui accompagne le Maire dans la conduite des projets."],
    ["title" => "Bureau Municipal", "text" => "L'organe exécutif qui coordonne l'action municipale au quotidien."],
    ["title" => "Commissions techniques", "text" => "Éducation, santé, urbanisme, environnement, jeunesse, culture."],
    ["title" => "Conseil municipal", "text" => "Instance délibérative qui vote les budgets et oriente les politiques publiques."],
    ["title" => "Administration municipale", "text" => "Services techniques et administratifs au service des citoyens."],
];

$meta = [
    ["k" => "Zone d'intervention", "v" => "Commune de Chérif Lo — Arrondissement de Pambal, Département de Tivaouane."],
    ["k" => "Domaines de compétences", "v" => "État civil, urbanisme, éducation, santé, action sociale, environnement, culture, jeunesse et sport."],
    ["k" => "Histoire de la mairie", "v" => "Créée par décret, la commune s'inscrit dans la tradition de gouvernance locale sénégalaise, au service de ses habitants."],
];

$commissions = [
    ["title" => "Santé", "text" => "La Commission Santé se consacre à l'amélioration du bien-être et de la santé des habitants. Elle œuvre pour renforcer les infrastructures sanitaires, faciliter l'accès aux soins et promouvoir des programmes de prévention."],
    ["title" => "Action sociale", "text" => "La Commission Sociale intervient pour soutenir les populations vulnérables et renforcer la cohésion sociale. Elle met en place des programmes d'aide et d'assistance, favorise l'insertion sociale et professionnelle."],
    ["title" => "Affaires domaniales", "text" => "La Commission Domaniale est responsable de la gestion et de l'administration du domaine public et privé de la commune. Elle veille à la régularisation foncière et à l'attribution des terrains."],
    ["title" => "Éducation et entrepreneuriat", "text" => "La Commission Éducation et Entrepreneuriat œuvre pour améliorer l'accès à une éducation de qualité et stimuler l'esprit entrepreneurial, notamment chez les jeunes."],
    ["title" => "Transport", "text" => "La Commission Transport se consacre à l'amélioration des infrastructures de transport et de mobilité à Chérif Lo. Elle développe des plans pour faciliter les déplacements des habitants."],
    ["title" => "Urbanisme et habitat", "text" => "La Commission Urbanisme et Habitat est chargée de la planification et de la gestion de l'espace urbain. Elle élabore les plans d'urbanisme et veille au respect des normes de construction."],
    ["title" => "Finances", "text" => "La Commission Finance joue un rôle crucial de coordination et de supervision financière. Elle s'assure que chaque commission municipale élabore un plan annuel d'activités détaillé."],
    ["title" => "Planification et coopération décentralisée", "text" => "Cette commission assure la planification stratégique du développement local et coordonne les partenariats avec d'autres collectivités et organisations internationales."],
    ["title" => "Fiscalité locale", "text" => "La Commission Fiscalité Locale est chargée de la mise en œuvre des politiques fiscales au niveau communal. Elle veille à l'équité et à l'efficacité du système fiscal local."],
    ["title" => "Promotion féminine", "text" => "La Commission Femme est dédiée à la promotion des droits des femmes et à leur participation active dans la vie de la commune. Elle encourage l'entrepreneuriat féminin."],
    ["title" => "Culture", "text" => "La Commission Culture œuvre pour la promotion et la préservation du riche patrimoine culturel de Chérif Lo. Elle soutient les initiatives artistiques et organise des événements culturels."],
    ["title" => "Jeunesse, sport et loisirs", "text" => "La Commission Jeunesse, Sport et Loisir s'engage pour l'épanouissement des jeunes de Chérif Lo. Elle organise des activités sportives, culturelles et récréatives."],
    ["title" => "Hydraulique et énergie", "text" => "La Commission Hydraulique et Énergie gère les ressources en eau et en énergie de Chérif Lo. Elle veille à l'approvisionnement en eau potable et à la promotion des énergies renouvelables."],
    ["title" => "Affaires religieuses", "text" => "La Commission Religieuse s'attache à favoriser le dialogue inter-religieux et à soutenir les différentes communautés spirituelles de Chérif Lo."],
    ["title" => "Environnement", "text" => "La Commission Environnement est dédiée à la protection et à la valorisation du patrimoine naturel de Chérif Lo. Elle met en place des projets pour préserver les espaces verts."],
    ["title" => "Artisanat", "text" => "La Commission Artisanat soutient le développement des activités artisanales locales. Elle valorise le savoir-faire des artisans de Chérif Lo et facilite l'accès aux marchés."],
    ["title" => "Numérique et innovations", "text" => "La Commission Numérique est dédiée à la promotion des technologies de l'information et de la communication. Elle travaille à la modernisation des services publics grâce au numérique."],
];
?>

<section class="container-page py-20 grid lg:grid-cols-12 gap-12">
  <div class="lg:col-span-5">
    <img src="/cahier-lumineux-php/assets/img/maire-portrait.jpg" alt="Le Maire de Chérif Lo" width="900" height="1100" loading="lazy" class="rounded-3xl object-cover w-full aspect-[4/5] shadow-elegant mt-6">
  </div>
  <div class="lg:col-span-7">
    <div class="text-xs uppercase tracking-[0.28em] text-primary/70">Mot du Maire</div>
    <h2 class="mt-3 font-display text-3xl md:text-4xl text-balance">
      « Ensemble, faisons de Chérif Lo une commune forte, unie et tournée vers l'avenir. »
    </h2>
    <div class="mt-6 space-y-4 text-muted-foreground leading-relaxed">
      <p>Chères concitoyennes, chers concitoyens, chers visiteurs,</p>
      <p>C'est avec un profond sentiment de fierté et de responsabilité que je vous accueille sur le portail officiel de notre commune de Chérif Lo.</p>
      <p>Notre territoire, ancré dans la région de Thiès et le terroir du Cayor, porte en lui l'héritage de générations qui ont façonné cette terre par le travail agricole et l'esprit de solidarité. C'est cet héritage que nous avons la responsabilité de préserver, tout en construisant l'avenir de notre commune.</p>
      <p>En tant que maire, mon engagement est simple : servir chaque habitant avec transparence, écoute et détermination. Parmi nos priorités, la jeunesse occupe une place centrale, c'est pourquoi nous portons le projet du stade municipal, un espace dédié au sport et à l'épanouissement de nos jeunes, mais aussi un lieu de rassemblement pour toute la commune. Ce projet illustre notre volonté de doter Chérif Lo d'infrastructures modernes, au service du vivre-ensemble et du développement local.</p>
      <p>Nous travaillons chaque jour à améliorer l'accès aux services essentiels, à soutenir nos producteurs et nos artisans, et à créer les conditions d'un développement durable pour tous les villages qui composent notre commune.</p>
      <p>Ce site se veut un espace de proximité entre l'administration communale et vous : habitants, membres de la diaspora, partenaires et visiteurs. Vous y trouverez les informations sur nos services, nos projets, dont celui du stade municipal, et la vie de notre territoire.</p>
      <p>Je vous invite à vous approprier cet outil, à nous faire part de vos préoccupations, et à participer activement à la construction de notre commune.</p>
      <p>Ensemble, faisons de Chérif Lo une commune forte, unie et tournée vers l'avenir.</p>
    </div>
    <div class="mt-8">
      <div class="font-display text-lg text-foreground">Ousmane Sarr</div>
      <div class="text-sm text-muted-foreground">Maire de la Commune de Chérif Lo</div>
    </div>
  </div>
</section>

<section class="bg-secondary/60">
  <div class="container-page py-20">
    <h2 class="font-display text-3xl md:text-4xl">Organisation municipale</h2>
    <p class="mt-3 text-muted-foreground max-w-2xl">Une équipe engagée et structurée pour piloter le développement de la commune.</p>
    <div class="mt-10 grid md:grid-cols-2 lg:grid-cols-3 gap-6">
      <?php foreach ($sections as $s):
        $isCommissions = $s['title'] === 'Commissions techniques'; ?>
        <<?= $isCommissions ? 'a href="#commissions"' : 'div' ?> class="block bg-card border border-border rounded-3xl p-6 hover:shadow-soft transition<?= $isCommissions ? ' hover:border-primary/40' : '' ?>">
          <div class="h-1 w-10 bg-gradient-gold rounded-full mb-4"></div>
          <h3 class="font-display text-xl"><?= $s['title'] ?></h3>
          <p class="mt-2 text-sm text-muted-foreground"><?= $s['text'] ?></p>
          <?php if ($isCommissions): ?><span class="mt-3 inline-flex items-center gap-1 text-sm font-medium text-primary">Voir les 17 commissions <i data-lucide="arrow-right" class="h-3.5 w-3.5"></i></span><?php endif; ?>
        </<?= $isCommissions ? 'a' : 'div' ?>>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="container-page py-20 grid md:grid-cols-3 gap-8">
  <?php foreach ($meta as $m): ?>
    <div>
      <div class="text-xs uppercase tracking-[0.28em] text-primary/70"><?= $m['k'] ?></div>
      <p class="mt-3 text-foreground/85 leading-relaxed"><?= $m['v'] ?></p>
    </div>
  <?php endforeach; ?>
</section>

<section id="commissions" class="bg-secondary/60 scroll-mt-24">
  <div class="container-page py-20">
    <div class="text-xs uppercase tracking-[0.28em] text-primary/70">Organisation municipale</div>
    <h2 class="mt-3 font-display text-3xl md:text-4xl">Rôle des Commissions</h2>
    <p class="mt-5 max-w-3xl text-muted-foreground leading-relaxed">
      Pour assurer une gestion publique efficace, spécialisée et proche des réalités locales, la commune de Chérif Lo
      s'appuie sur <strong>17 commissions municipales</strong>. Ces instances thématiques, composées d'élus et parfois
      de représentants de la société civile, ont pour mission d'étudier les dossiers relevant de leur domaine, de
      formuler des avis et de proposer des actions concrètes au conseil municipal. Elles constituent ainsi des
      espaces stratégiques d'analyse, de planification et de suivi des politiques locales.
    </p>

    <div class="mt-10 grid md:grid-cols-2 lg:grid-cols-3 gap-6">
      <?php foreach ($commissions as $c):
        $isDomaniale = $c['title'] === 'Affaires domaniales'; ?>
        <<?= $isDomaniale ? 'a href="foncier.php"' : 'div' ?> class="block bg-card border border-border rounded-3xl p-6 hover:shadow-soft transition<?= $isDomaniale ? ' hover:border-primary/40' : '' ?>">
          <div class="h-1 w-10 bg-gradient-gold rounded-full mb-4"></div>
          <h3 class="font-display text-lg"><?= htmlspecialchars($c['title']) ?></h3>
          <p class="mt-2 text-sm text-muted-foreground leading-relaxed"><?= htmlspecialchars($c['text']) ?></p>
          <?php if ($isDomaniale): ?><span class="mt-3 inline-flex items-center gap-1 text-sm font-medium text-primary">Service Foncier <i data-lucide="arrow-right" class="h-3.5 w-3.5"></i></span><?php endif; ?>
        </<?= $isDomaniale ? 'a' : 'div' ?>>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<?php include __DIR__ . "/includes/footer.php"; ?>
