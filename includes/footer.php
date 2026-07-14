</main>

<footer class="mt-24 bg-primary-deep text-primary-foreground">
  <div class="container-page py-16 grid gap-12 md:grid-cols-2 lg:grid-cols-4">
    <div>
      <div class="flex items-center gap-3">
        <img src="/mairie/assets/img/logo.jpg" alt="Logo Mairie de Chérif Lo" class="h-14 w-14 rounded-full bg-white p-1">
        <div>
          <div class="font-display text-xl">Mairie de Chérif Lo</div>
          <div class="text-xs text-primary-foreground/70 uppercase tracking-wider">République du Sénégal</div>
        </div>
      </div>
      <p class="mt-5 text-sm text-primary-foreground/75 leading-relaxed">
        Une administration proche, transparente et au service de chaque citoyen.
      </p>
    </div>

    <div>
      <h4 class="text-sm uppercase tracking-widest text-accent">Navigation</h4>
      <ul class="mt-4 space-y-2 text-sm text-primary-foreground/85">
        <li><a href="/mairie/mairie.php" class="hover:text-accent">La Mairie</a></li>
        <li><a href="/mairie/services.php" class="hover:text-accent">Services aux usagers</a></li>
        <li><a href="/mairie/actualites.php" class="hover:text-accent">Actualités</a></li>
        <li><a href="/mairie/affaires-sociales.php" class="hover:text-accent">Affaires sociales</a></li>
        <li><a href="/mairie/mediatheque.php" class="hover:text-accent">Médiathèque</a></li>
        <li><a href="/mairie/rendezvous.php" class="hover:text-accent">Prendre rendez-vous</a></li>
      </ul>
    </div>

    <div>
      <h4 class="text-sm uppercase tracking-widest text-accent">Contact</h4>
      <ul class="mt-4 space-y-3 text-sm text-primary-foreground/85">
        <li class="flex gap-2"><i data-lucide="map-pin" class="h-4 w-4 mt-0.5 text-accent"></i> <?= htmlspecialchars($SETTINGS['contact_adresse'] ?? '') ?></li>
        <li class="flex gap-2"><i data-lucide="phone" class="h-4 w-4 mt-0.5 text-accent"></i> <?= htmlspecialchars($SETTINGS['contact_telephone'] ?? '') ?></li>
        <li class="flex gap-2"><i data-lucide="mail" class="h-4 w-4 mt-0.5 text-accent"></i> <?= htmlspecialchars($SETTINGS['contact_email'] ?? '') ?></li>
        <li><a href="/mairie/contact.php" class="inline-flex items-center gap-1.5 mt-1 font-medium text-accent hover:underline">Formulaire de contact <i data-lucide="arrow-right" class="h-3.5 w-3.5"></i></a></li>
      </ul>
    </div>

    <div>
      <h4 class="text-sm uppercase tracking-widest text-accent">Horaires</h4>
      <ul class="mt-4 space-y-2 text-sm text-primary-foreground/85">
        <li><?= htmlspecialchars($SETTINGS['horaires_semaine'] ?? '') ?></li>
        <li><?= htmlspecialchars($SETTINGS['horaires_samedi'] ?? '') ?></li>
        <li><?= htmlspecialchars($SETTINGS['horaires_dimanche'] ?? '') ?></li>
      </ul>
      <div class="mt-5 flex gap-3">
        <a href="<?= !empty($SETTINGS['social_facebook']) ? htmlspecialchars($SETTINGS['social_facebook']) : '#' ?>" aria-label="Facebook" class="h-9 w-9 grid place-items-center rounded-full bg-white/10 hover:bg-accent hover:text-primary-deep transition"><i data-lucide="facebook" class="h-4 w-4"></i></a>
        <a href="<?= !empty($SETTINGS['social_youtube']) ? htmlspecialchars($SETTINGS['social_youtube']) : '#' ?>" aria-label="YouTube" class="h-9 w-9 grid place-items-center rounded-full bg-white/10 hover:bg-accent hover:text-primary-deep transition"><i data-lucide="youtube" class="h-4 w-4"></i></a>
      </div>
    </div>
  </div>
  <div class="border-t border-white/10">
    <div class="container-page py-5 text-xs flex flex-col md:flex-row items-center justify-between gap-2 text-primary-foreground/60">
      <span>© <?= date("Y") ?> Mairie de Chérif Lo — Tous droits réservés</span>
      <span class="flex items-center gap-4">
        Un Sénégal digital, une commune connectée.
        <a href="/mairie/admin/login.php" class="underline decoration-white/30 hover:text-accent">Espace Mairie</a>
      </span>
    </div>
  </div>
</footer>

<a
  href="<?= htmlspecialchars(whatsapp_link(ADMIN_PHONE_INTL, "Bonjour, je souhaite contacter la Mairie de Chérif Lo depuis le site.")) ?>"
  target="_blank"
  rel="noopener noreferrer"
  aria-label="Contacter la mairie sur WhatsApp"
  class="fixed bottom-5 right-5 z-50 h-14 w-14 grid place-items-center rounded-full bg-[#25D366] text-white shadow-elegant hover:scale-105 transition"
>
  <svg viewBox="0 0 32 32" class="h-7 w-7" fill="currentColor" aria-hidden="true">
    <path d="M16.004 3C9.377 3 4 8.373 4 15c0 2.31.646 4.47 1.77 6.31L4 29l7.86-1.73A11.9 11.9 0 0 0 16.004 27C22.63 27 28 21.627 28 15S22.63 3 16.004 3Zm0 21.75a9.7 9.7 0 0 1-4.95-1.36l-.355-.21-4.66 1.03 1.02-4.54-.232-.37A9.72 9.72 0 0 1 5.25 15c0-5.93 4.82-10.75 10.754-10.75S26.75 9.07 26.75 15 21.94 24.75 16.004 24.75Zm5.6-7.34c-.307-.154-1.816-.897-2.098-1-.281-.103-.487-.154-.692.154-.205.307-.794 1-0.974 1.205-.179.205-.358.23-.665.077-.307-.154-1.297-.478-2.47-1.523-.913-.814-1.53-1.82-1.71-2.128-.179-.307-.02-.473.135-.626.138-.138.307-.358.46-.538.154-.179.205-.307.307-.512.103-.205.051-.384-.026-.538-.077-.154-.692-1.667-.948-2.283-.25-.6-.503-.52-.692-.53l-.59-.01c-.205 0-.538.077-.82.384-.281.307-1.075 1.05-1.075 2.564s1.1 2.974 1.253 3.18c.154.205 2.166 3.31 5.248 4.64.733.316 1.305.505 1.751.646.735.234 1.404.2 1.933.122.59-.088 1.816-.742 2.072-1.46.256-.717.256-1.332.18-1.46-.077-.128-.282-.205-.59-.359Z"/>
  </svg>
</a>

<script>lucide.createIcons();</script>
</body>
</html>
