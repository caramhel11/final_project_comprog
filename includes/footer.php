<?php // includes/footer.php — Shared site footer ?>

<footer class="footer mt-20">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-10 pb-10 border-b border-white/10">

      <div>
        <div class="footer-logo mb-3">Isla <span style="color:#D97706">Finds</span></div>
        <p class="text-sm leading-relaxed" style="color:rgba(255,255,255,.55)">
          Bringing the heart of Marinduque to your doorstep — authentic souvenirs, handicrafts,
          and delicacies crafted with island pride.
        </p>
      </div>

      <div>
        <h4 class="text-white font-semibold mb-4 text-sm uppercase tracking-wider">Quick Links</h4>
        <ul class="space-y-2">
          <li><a href="<?= $rootPath ?? '' ?>index.php" class="text-sm hover:text-white transition" style="color:rgba(255,255,255,.55)">Home</a></li>
          <li><a href="<?= $rootPath ?? '' ?>shop.php" class="text-sm hover:text-white transition" style="color:rgba(255,255,255,.55)">Shop</a></li>
          <li><a href="<?= $rootPath ?? '' ?>cart.php" class="text-sm hover:text-white transition" style="color:rgba(255,255,255,.55)">Cart</a></li>
          <li><a href="<?= $rootPath ?? '' ?>login.php" class="text-sm hover:text-white transition" style="color:rgba(255,255,255,.55)">Sign In</a></li>
        </ul>
      </div>

      <div>
        <h4 class="text-white font-semibold mb-4 text-sm uppercase tracking-wider">Categories</h4>
        <ul class="space-y-2">
          <?php
          $cats = getAllCategories();
          foreach ($cats as $cat):
          ?>
          <li>
            <a href="<?= $rootPath ?? '' ?>shop.php?category=<?= $cat['CategoryID'] ?>"
               class="text-sm hover:text-white transition" style="color:rgba(255,255,255,.55)">
              <?= sanitize($cat['CategoryName']) ?>
            </a>
          </li>
          <?php endforeach; ?>
        </ul>
      </div>
    </div>

    <div class="py-6 flex flex-col sm:flex-row items-center justify-between gap-2">
      <p class="text-xs" style="color:rgba(255,255,255,.4)">
        &copy; <?= date('Y') ?> Isla Finds. All rights reserved. Made with ♥ from Marinduque.
      </p>
      <p class="text-xs" style="color:rgba(255,255,255,.35)">
        🌺 Marinduque, Philippines
      </p>
    </div>
  </div>
</footer>

<!-- Scroll-reveal -->
<script>
  const observer = new IntersectionObserver(entries => {
    entries.forEach(e => { if (e.isIntersecting) e.target.classList.add('visible'); });
  }, { threshold: 0.12 });
  document.querySelectorAll('.fade-up').forEach(el => observer.observe(el));
</script>
</body>
</html>