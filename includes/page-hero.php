<?php
/**
 * Reusable page hero banner.
 * Expects $heroEyebrow, $heroTitle, $heroDescription set before include.
 */
?>
<section class="bg-gradient-hero text-primary-foreground">
  <div class="container-page py-20 md:py-28">
    <?php if (!empty($heroEyebrow)): ?>
      <div class="text-xs uppercase tracking-[0.28em] text-accent mb-4"><?= $heroEyebrow ?></div>
    <?php endif; ?>
    <h1 class="font-display text-4xl md:text-6xl max-w-3xl text-balance"><?= $heroTitle ?></h1>
    <?php if (!empty($heroDescription)): ?>
      <p class="mt-5 max-w-2xl text-primary-foreground/80 text-lg"><?= $heroDescription ?></p>
    <?php endif; ?>
  </div>
</section>
