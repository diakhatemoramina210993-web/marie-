<?php
$pageTitle = "Médiathèque — Mairie de Chérif Lo";
$pageDescription = "Photos et vidéos des événements, projets et cérémonies de la commune de Chérif Lo.";
$activePage = "mediatheque.php";
include __DIR__ . "/includes/header.php";

$heroEyebrow = "Médiathèque";
$heroTitle = "La commune en images et en vidéos.";
$heroDescription = "Retrouvez les temps forts, cérémonies et projets de Chérif Lo.";
include __DIR__ . "/includes/page-hero.php";

$photos = $pdo->query("SELECT * FROM medias WHERE type = 'photo' ORDER BY ordre ASC, created_at DESC")->fetchAll();
$videos = $pdo->query("SELECT * FROM medias WHERE type = 'video' ORDER BY ordre ASC, created_at DESC")->fetchAll();
?>

<section class="container-page py-20">
  <div class="flex items-center gap-3 mb-8">
    <i data-lucide="image" class="h-5 w-5 text-primary"></i>
    <h2 class="font-display text-3xl">Photos</h2>
  </div>
  <?php if (!$photos): ?>
    <p class="text-muted-foreground text-sm">Aucune photo publiée pour le moment.</p>
  <?php else: ?>
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
      <?php foreach ($photos as $p): ?>
        <a href="/cahier-lumineux-php/assets/img/medias/<?= htmlspecialchars($p['fichier']) ?>" target="_blank" rel="noopener"
           class="group aspect-square rounded-2xl overflow-hidden relative block">
          <img src="/cahier-lumineux-php/assets/img/medias/<?= htmlspecialchars($p['fichier']) ?>" alt="<?= htmlspecialchars($p['titre']) ?>" loading="lazy" class="h-full w-full object-cover group-hover:scale-105 transition duration-300">
          <div class="absolute inset-0 bg-primary-deep/40 group-hover:bg-primary-deep/10 transition"></div>
          <div class="absolute inset-x-0 bottom-0 p-4 text-primary-foreground text-sm font-medium"><?= htmlspecialchars($p['titre']) ?></div>
        </a>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

  <div class="mt-20 flex items-center gap-3 mb-8">
    <i data-lucide="play" class="h-5 w-5 text-primary"></i>
    <h2 class="font-display text-3xl">Vidéos</h2>
  </div>
  <?php if (!$videos): ?>
    <p class="text-muted-foreground text-sm">Aucune vidéo publiée pour le moment.</p>
  <?php else: ?>
    <div class="grid md:grid-cols-3 gap-6">
      <?php foreach ($videos as $v): ?>
        <button type="button"
          class="video-trigger text-left rounded-3xl overflow-hidden border border-border bg-card group cursor-pointer shadow-soft hover:shadow-elegant hover:-translate-y-1 transition-all duration-300"
          data-video-src="<?= $v['fichier'] ? htmlspecialchars('/cahier-lumineux-php/assets/img/medias/' . $v['fichier']) : '' ?>"
          data-video-url="<?= htmlspecialchars($v['video_url'] ?? '') ?>"
          data-video-title="<?= htmlspecialchars($v['titre']) ?>">
          <span class="aspect-video relative flex items-center justify-center overflow-hidden <?= $v['fichier'] ? 'bg-black' : 'bg-gradient-hero' ?>">
            <?php if ($v['fichier']): ?>
              <video src="/cahier-lumineux-php/assets/img/medias/<?= htmlspecialchars($v['fichier']) ?>" class="video-thumb-source absolute inset-0 h-full w-full object-cover scale-105 group-hover:scale-110 opacity-0 transition-opacity transition-transform duration-500" preload="auto" muted playsinline aria-hidden="true"></video>
            <?php endif; ?>
            <span class="absolute inset-0 bg-gradient-to-t from-black/90 via-black/40 to-black/60"></span>
            <span class="absolute top-3 left-3 rounded-full bg-white/95 text-primary-deep text-[11px] font-semibold uppercase tracking-wider px-3 py-1">Vidéo</span>
            <span class="relative h-16 w-16 rounded-full bg-white shadow-xl ring-4 ring-white/30 grid place-items-center text-primary-deep group-hover:scale-110 transition-transform duration-300">
              <i data-lucide="play" class="h-6 w-6 ml-1" fill="currentColor"></i>
            </span>
            <?php if ($v['duree']): ?>
              <span class="absolute bottom-3 right-3 bg-black/70 text-white text-xs font-medium px-2.5 py-1 rounded-md tabular-nums"><?= htmlspecialchars($v['duree']) ?></span>
            <?php endif; ?>
          </span>
          <span class="block p-5">
            <span class="font-display text-lg leading-snug block group-hover:text-primary transition-colors"><?= htmlspecialchars($v['titre']) ?></span>
          </span>
        </button>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</section>

<div id="video-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/80 p-4" role="dialog" aria-modal="true" aria-label="Lecteur vidéo">
  <div class="relative w-full max-w-3xl">
    <button type="button" id="video-modal-close" aria-label="Fermer" class="absolute -top-10 right-0 text-white/90 hover:text-white">
      <i data-lucide="x" class="h-7 w-7"></i>
    </button>
    <div id="video-modal-body" class="aspect-video bg-black rounded-xl overflow-hidden"></div>
  </div>
</div>

<script>
  (function () {
    // Positionne chaque miniature vidéo sur une image réelle du fichier
    // (au lieu de la première image, souvent noire ou peu représentative).
    document.querySelectorAll('.video-thumb-source').forEach(function (v) {
      function seekToThumb() {
        var target = (v.duration || 12) * 0.25;
        v.addEventListener('seeked', function () {
          v.classList.remove('opacity-0');
        }, { once: true });
        try { v.currentTime = target; } catch (e) { v.classList.remove('opacity-0'); }
      }
      if (v.readyState >= 1) {
        seekToThumb();
      } else {
        v.addEventListener('loadedmetadata', seekToThumb, { once: true });
      }
      v.addEventListener('error', function () {
        v.classList.remove('opacity-0');
      });
    });
  })();

  (function () {
    var modal = document.getElementById('video-modal');
    var body = document.getElementById('video-modal-body');
    var closeBtn = document.getElementById('video-modal-close');
    var lastFocused = null;

    function openModal(src, url, title) {
      body.innerHTML = '';
      if (src) {
        var video = document.createElement('video');
        video.src = src;
        video.controls = true;
        video.autoplay = true;
        video.className = 'h-full w-full';
        video.setAttribute('aria-label', title);
        body.appendChild(video);
      } else if (url) {
        window.open(url, '_blank', 'noopener');
        return;
      } else {
        return;
      }
      lastFocused = document.activeElement;
      modal.classList.remove('hidden');
      modal.classList.add('flex');
      closeBtn.focus();
      document.addEventListener('keydown', onKeydown);
    }

    function closeModal() {
      body.innerHTML = '';
      modal.classList.add('hidden');
      modal.classList.remove('flex');
      document.removeEventListener('keydown', onKeydown);
      if (lastFocused) lastFocused.focus();
    }

    function onKeydown(e) {
      if (e.key === 'Escape') closeModal();
    }

    document.querySelectorAll('.video-trigger').forEach(function (btn) {
      btn.addEventListener('click', function () {
        openModal(btn.dataset.videoSrc, btn.dataset.videoUrl, btn.dataset.videoTitle);
      });
    });
    closeBtn.addEventListener('click', closeModal);
    modal.addEventListener('click', function (e) {
      if (e.target === modal) closeModal();
    });
  })();
</script>

<?php include __DIR__ . "/includes/footer.php"; ?>
