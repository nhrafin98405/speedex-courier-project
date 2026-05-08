<?php $pageTitle = 'Home'; require_once 'includes/header.php'; require_once 'includes/navbar.php'; ?>

<!-- Hero Section -->
<section class="hero-section">
  <div class="container">
    <div class="row align-items-center">
      <div class="col-lg-6">
        <div class="reveal">
          <h1 class="hero-title">
            Fast. Safe. Reliable<br>Delivery All Over<br><span>Bangladesh</span>
          </h1>
          <p class="hero-subtitle">
            Send your parcel easily and we will deliver with care. Track your shipment in real-time across all major cities.
          </p>
          <div class="d-flex gap-3 flex-wrap">
            <a href="<?= !empty($_SESSION['user_id']) ? BASE_URL.'/send-parcel.php' : BASE_URL.'/auth/login.php?redirect='.urlencode(BASE_URL.'/send-parcel.php') ?>" class="btn-speedex"><i class="fas fa-paper-plane"></i> Send Parcel Now</a>
            <a href="track.php" class="btn-speedex-outline"><i class="fas fa-search"></i> Track Parcel</a>
          </div>
        </div>
      </div>
      <div class="col-lg-6 text-center mt-5 mt-lg-0">
        <div class="hero-image reveal">
          <div style="width: 400px; height: 400px; margin: 0 auto; background: radial-gradient(circle, rgba(34,197,94,0.15) 0%, transparent 70%); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
            <i class="fas fa-truck-fast" style="font-size: 8rem; color: var(--primary); filter: drop-shadow(0 0 40px rgba(34,197,94,0.4));"></i>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Features Section -->
<section class="section-padding" style="background: var(--bg-secondary);">
  <div class="container">
    <div class="row g-4">
      <?php
      $features = [
        ['icon' => 'fa-calendar-check', 'title' => 'Easy Booking', 'desc' => 'Book your parcel in just a few steps.'],
        ['icon' => 'fa-location-dot', 'title' => 'Real-Time Tracking', 'desc' => 'Track your parcel in real-time.'],
        ['icon' => 'fa-shield-halved', 'title' => 'Secure Delivery', 'desc' => 'We ensure safe and secure delivery.'],
        ['icon' => 'fa-globe', 'title' => 'Wide Network', 'desc' => 'Delivery across all major cities.'],
      ];
      foreach ($features as $f): ?>
      <div class="col-lg-3 col-md-6">
        <div class="feature-card reveal">
          <div class="feature-icon"><i class="fas <?= $f['icon'] ?>"></i></div>
          <h5><?= $f['title'] ?></h5>
          <p><?= $f['desc'] ?></p>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- How It Works -->
<section class="section-padding">
  <div class="container">
    <div class="text-center mb-5 reveal">
      <h2 class="section-title">How It <span class="gradient-text">Works</span></h2>
      <p class="section-subtitle">Simple steps to send your parcel</p>
    </div>
    <div class="row justify-content-center">
      <div class="col-lg-8">
        <?php
        $steps = [
          ['num' => 1, 'title' => 'Book Your Parcel', 'desc' => 'Fill in sender & receiver details, choose delivery type and payment method.'],
          ['num' => 2, 'title' => 'We Pick It Up', 'desc' => 'Our rider will pick up the parcel from your location or drop it at the nearest hub.'],
          ['num' => 3, 'title' => 'In Transit', 'desc' => 'Your parcel travels through our hub network with real-time tracking.'],
          ['num' => 4, 'title' => 'Delivered!', 'desc' => 'Parcel is delivered safely to the receiver. Get instant SMS confirmation.'],
        ];
        foreach ($steps as $s): ?>
        <div class="timeline-item reveal">
          <div class="timeline-number"><?= $s['num'] ?></div>
          <div>
            <h5 style="font-weight: 600; margin-bottom: 4px;"><?= $s['title'] ?></h5>
            <p style="color: var(--text-secondary); font-size: 0.875rem;"><?= $s['desc'] ?></p>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</section>

<!-- Pricing Section -->
<section class="section-padding" style="background: var(--bg-secondary);">
  <div class="container">
    <div class="text-center mb-5 reveal">
      <h2 class="section-title">Delivery <span class="gradient-text">Pricing</span></h2>
      <p class="section-subtitle">Affordable rates for all your shipping needs</p>
    </div>
    <div class="row g-4 justify-content-center">
      <?php
      $plans = [
        ['name' => 'Standard', 'price' => '60', 'time' => '48-72 Hours', 'features' => ['Up to 5 KG', 'All Districts', 'SMS Updates', 'Insurance'], 'featured' => false],
        ['name' => 'Express', 'price' => '100', 'time' => '24-48 Hours', 'features' => ['Up to 10 KG', 'Priority Handling', 'Live Tracking', 'Insurance', 'COD Available'], 'featured' => true],
        ['name' => 'Same Day', 'price' => '200', 'time' => '6-12 Hours', 'features' => ['Up to 15 KG', 'Dedicated Rider', 'Real-time Tracking', 'Full Insurance', 'COD + Payment'], 'featured' => false],
      ];
      foreach ($plans as $p): ?>
      <div class="col-lg-4 col-md-6">
        <div class="pricing-card reveal <?= $p['featured'] ? 'featured' : '' ?>">
          <h5 style="font-weight: 600; margin-bottom: 8px;"><?= $p['name'] ?></h5>
          <p style="color: var(--text-muted); font-size: 0.8rem; margin-bottom: 16px;"><?= $p['time'] ?></p>
          <div class="pricing-amount mb-4">৳<?= $p['price'] ?> <small>/parcel</small></div>
          <ul style="list-style: none; padding: 0; margin-bottom: 24px;">
            <?php foreach ($p['features'] as $feat): ?>
            <li style="padding: 8px 0; font-size: 0.875rem; color: var(--text-secondary);"><i class="fas fa-check-circle me-2" style="color: var(--primary);"></i> <?= $feat ?></li>
            <?php endforeach; ?>
          </ul>
          <a href="auth/login.php" class="btn-speedex w-100 justify-content-center">Get Started</a>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- Testimonials -->
<section class="section-padding">
  <div class="container">
    <div class="text-center mb-5 reveal">
      <h2 class="section-title">What Our <span class="gradient-text">Customers Say</span></h2>
      <p class="section-subtitle">Trusted by thousands across Bangladesh</p>
    </div>
    <div class="row g-4">
      <?php
      $testimonials = [
        ['name' => 'Rafiq Ahmed', 'role' => 'Business Owner', 'text' => 'nhr has been amazing for my e-commerce business. Fast delivery and great tracking system!', 'init' => 'RA'],
        ['name' => 'Sadia Akter', 'role' => 'Regular Customer', 'text' => 'Very reliable service. My parcels always arrive on time and in perfect condition.', 'init' => 'SA'],
        ['name' => 'Hasan Mahmud', 'role' => 'Corporate Client', 'text' => 'The corporate delivery plan is excellent. Professional service and competitive pricing.', 'init' => 'HM'],
      ];
      foreach ($testimonials as $t): ?>
      <div class="col-lg-4 col-md-6">
        <div class="testimonial-card reveal">
          <div class="stars"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i></div>
          <p style="color: var(--text-secondary); font-size: 0.875rem; line-height: 1.8; margin-bottom: 16px;">"<?= $t['text'] ?>"</p>
          <div class="d-flex align-items-center gap-3">
            <div class="testimonial-avatar"><?= $t['init'] ?></div>
            <div>
              <h6 style="font-weight: 600; margin-bottom: 2px; font-size: 0.9rem;"><?= $t['name'] ?></h6>
              <small style="color: var(--text-muted);"><?= $t['role'] ?></small>
            </div>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- FAQ Section -->
<section class="section-padding" style="background: var(--bg-secondary);">
  <div class="container">
    <div class="text-center mb-5 reveal">
      <h2 class="section-title">Frequently Asked <span class="gradient-text">Questions</span></h2>
      <p class="section-subtitle">Find answers to common questions</p>
    </div>
    <div class="row justify-content-center">
      <div class="col-lg-8">
        <?php
        $faqs = [
          ['q' => 'How do I send a parcel?', 'a' => 'Simply log into your account, fill in sender and receiver details, choose delivery type, and book. Our rider will pick it up or you can drop it at the nearest hub.'],
          ['q' => 'How can I track my parcel?', 'a' => 'Use your tracking ID on our Track Parcel page. You\'ll see real-time status updates including pickup, transit, hub arrival, and delivery.'],
          ['q' => 'What are the delivery charges?', 'a' => 'Charges depend on weight and delivery type. Standard starts at ৳60, Express at ৳100, and Same Day at ৳200. Additional per-KG charges apply.'],
          ['q' => 'Do you offer Cash on Delivery (COD)?', 'a' => 'Yes! COD is available for Express and Same Day delivery. The COD amount is collected from the receiver and transferred to you.'],
          ['q' => 'How long does delivery take?', 'a' => 'Standard: 48-72 hours, Express: 24-48 hours, Same Day: 6-12 hours (within city). Delivery time may vary by location.'],
        ];
        foreach ($faqs as $i => $faq): ?>
        <div class="faq-item reveal <?= $i === 0 ? 'active' : '' ?>">
          <div class="faq-question"><?= $faq['q'] ?> <i class="fas fa-chevron-down"></i></div>
          <div class="faq-answer"><?= $faq['a'] ?></div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</section>

<!-- CTA Section -->
<section class="section-padding" style="background: var(--gradient-primary); text-align: center;">
  <div class="container reveal">
    <h2 style="font-size: 2rem; font-weight: 700; color: #fff; margin-bottom: 12px;">Ready to Ship Your Parcel?</h2>
    <p style="color: rgba(255,255,255,0.8); margin-bottom: 24px;">Join thousands of satisfied customers. Fast, safe, reliable delivery.</p>
    <a href="auth/register.php" class="btn-speedex-outline" style="border-color: #fff; color: #fff;">
      <i class="fas fa-rocket"></i> Get Started Now
    </a>
  </div>
</section>

<?php require_once 'includes/footer.php'; ?>
