<?php $pageTitle = 'Services'; require_once 'includes/header.php'; require_once 'includes/navbar.php'; ?>

<section class="section-padding" style="padding-top: 120px;">
  <div class="container">
    <div class="text-center mb-5 reveal">
      <h1 class="section-title">Our <span class="gradient-text">Services</span></h1>
      <p class="section-subtitle">Comprehensive delivery solutions for every need</p>
    </div>

    <div class="row g-4">
      <?php
      $services = [
        ['icon' => 'fa-bolt', 'title' => 'Same Day Delivery', 'desc' => 'Get your parcel delivered within the same day. Available for intra-city deliveries with dedicated riders and priority processing.', 'badge' => 'Premium'],
        ['icon' => 'fa-rocket', 'title' => 'Express Delivery', 'desc' => 'Fast 24-48 hour delivery across all districts. Priority handling with real-time tracking and SMS notifications.', 'badge' => 'Popular'],
        ['icon' => 'fa-building', 'title' => 'Corporate Delivery', 'desc' => 'Customized solutions for businesses. Bulk shipping rates, dedicated account manager, and monthly billing.', 'badge' => 'Business'],
        ['icon' => 'fa-money-bill-wave', 'title' => 'Cash On Delivery', 'desc' => 'Collect payment from receivers. Perfect for e-commerce businesses. Fast fund transfer to your account.', 'badge' => 'E-Commerce'],
        ['icon' => 'fa-globe-americas', 'title' => 'International Delivery', 'desc' => 'Ship worldwide with our partner network. Customs handling, door-to-door delivery, and competitive rates.', 'badge' => 'Global'],
        ['icon' => 'fa-truck-loading', 'title' => 'Standard Delivery', 'desc' => 'Affordable 48-72 hour delivery across Bangladesh. Reliable service with tracking and insurance included.', 'badge' => 'Value'],
      ];
      foreach ($services as $s): ?>
      <div class="col-lg-4 col-md-6">
        <div class="service-card reveal">
          <span class="badge-status badge-delivered" style="position: absolute; top: 16px; right: 16px;"><?= $s['badge'] ?></span>
          <div class="service-icon"><i class="fas <?= $s['icon'] ?>"></i></div>
          <h5 style="font-weight: 600; margin-bottom: 12px;"><?= $s['title'] ?></h5>
          <p style="color: var(--text-secondary); font-size: 0.875rem; line-height: 1.8;"><?= $s['desc'] ?></p>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<?php require_once 'includes/footer.php'; ?>
