<?php
// about.php
require_once 'includes/functions.php';
$pageTitle = 'About — Isla Finds';
include 'includes/header.php';
?>

<section class="hero py-24 px-4 text-center relative">
  <div class="relative z-10 max-w-2xl mx-auto">
    <div class="hero-badge">🌺 &nbsp;Our Story</div>
    <h1 class="hero-title mb-4">About Isla Finds</h1>
    <p class="hero-subtitle">Celebrating the culture and craft of Marinduque — one souvenir at a time.</p>
  </div>
  <svg class="hero-waves" viewBox="0 0 1440 80" fill="none" xmlns="http://www.w3.org/2000/svg">
    <path d="M0 40C240 80 480 0 720 40C960 80 1200 0 1440 40V80H0V40Z" fill="#FAFAFA"/>
  </svg>
</section>

<section class="max-w-4xl mx-auto px-4 py-20">
  <div class="grid md:grid-cols-2 gap-12 items-center fade-up">
    <div>
      <span class="section-tag">Who We Are</span>
      <h2 class="section-title mt-2 mb-5">Rooted in Marinduque</h2>
      <p class="text-gray-600 leading-relaxed mb-4">
        Isla Finds is a proudly Marinduqueño enterprise dedicated to bringing the island's most authentic
        products to customers everywhere. From the crispy arrowroot cookies baked by local families, to the
        intricately woven buntal bags crafted by master artisans — every item carries the island's spirit.
      </p>
      <p class="text-gray-600 leading-relaxed">
        Our mission is simple: preserve local craft traditions, support island livelihoods, and share
        the warmth of Marinduque with the world.
      </p>
    </div>
    <div class="grid grid-cols-2 gap-4">
      <?php
      $facts = [
        ['🌺','Island Province','Heart of the Philippines'],
        ['🧺','Handicrafts','Woven by skilled artisans'],
        ['🍪','Delicacies','Authentic local recipes'],
        ['🎁','17+ Products','Growing collection'],
      ];
      foreach ($facts as $f):
      ?>
      <div class="bg-white border border-gray-100 rounded-2xl p-5 shadow-sm text-center fade-up">
        <div class="text-3xl mb-2"><?= $f[0] ?></div>
        <div class="font-semibold text-sm text-gray-800"><?= $f[1] ?></div>
        <div class="text-xs text-gray-400 mt-1"><?= $f[2] ?></div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<?php include 'includes/footer.php'; ?>