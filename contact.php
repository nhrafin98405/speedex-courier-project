<?php $pageTitle = 'Contact'; require_once 'includes/header.php'; require_once 'includes/navbar.php'; ?>

<section class="section-padding" style="padding-top: 120px;">
  <div class="container">
    <div class="text-center mb-5 reveal">
      <h1 class="section-title">Contact <span class="gradient-text">Us</span></h1>
      <p class="section-subtitle">Get in touch with our support team</p>
    </div>

    <div class="row g-4">
      <div class="col-lg-6">
        <div class="contact-card reveal">
          <h4 style="font-weight: 600; margin-bottom: 24px;">Send us a message</h4>
          <form data-validate action="api/contact.php" method="POST">
            <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
            <div class="mb-3">
              <label class="form-label-speedex">Full Name</label>
              <input type="text" name="name" class="form-control-speedex" placeholder="Your full name" required>
            </div>
            <div class="mb-3">
              <label class="form-label-speedex">Email Address</label>
              <input type="email" name="email" class="form-control-speedex" placeholder="your@email.com" required>
            </div>
            <div class="mb-3">
              <label class="form-label-speedex">Phone Number</label>
              <input type="tel" name="phone" class="form-control-speedex" placeholder="01XXX-XXXXXX">
            </div>
            <div class="mb-4">
              <label class="form-label-speedex">Message</label>
              <textarea name="message" class="form-control-speedex" rows="5" placeholder="Your message..." required></textarea>
            </div>
            <button type="submit" class="btn-speedex w-100 justify-content-center">
              <i class="fas fa-paper-plane"></i> Send Message
            </button>
          </form>
        </div>
      </div>

      <div class="col-lg-6">
        <div class="glass-card reveal mb-4">
          <h5 style="font-weight: 600; margin-bottom: 20px;">Contact Information</h5>
          <div class="d-flex gap-3 mb-4 align-items-center">
            <div class="feature-icon flex-shrink-0" style="width: 48px; height: 48px; min-width: 48px; font-size: 1rem; display: flex; align-items: center; justify-content: center; margin: 0;"><i class="fas fa-map-marker-alt"></i></div>
            <div>
              <h6 style="font-weight: 600; margin-bottom: 4px;">Head Office</h6>
              <p style="color: var(--text-secondary); font-size: 0.85rem; margin: 0;">House 12, Road 5, Dhanmondi, Dhaka-1205</p>
            </div>
          </div>
          <div class="d-flex gap-3 mb-4 align-items-center">
            <div class="feature-icon flex-shrink-0" style="width: 48px; height: 48px; min-width: 48px; font-size: 1rem; display: flex; align-items: center; justify-content: center; margin: 0;"><i class="fas fa-phone"></i></div>
            <div>
              <h6 style="font-weight: 600; margin-bottom: 4px;">Phone</h6>
              <p style="color: var(--text-secondary); font-size: 0.85rem; margin: 0;">+880 1700-000000 (24/7)</p>
            </div>
          </div>
          <div class="d-flex gap-3 mb-4 align-items-center">
            <div class="feature-icon flex-shrink-0" style="width: 48px; height: 48px; min-width: 48px; font-size: 1rem; display: flex; align-items: center; justify-content: center; margin: 0;"><i class="fas fa-envelope"></i></div>
            <div>
              <h6 style="font-weight: 600; margin-bottom: 4px;">Email</h6>
              <p style="color: var(--text-secondary); font-size: 0.85rem; margin: 0;">info@speedex.com</p>
            </div>
          </div>
        </div>

        <div class="glass-card reveal" style="padding: 0; overflow: hidden; height: 280px;">
          <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3652.2!2d90.38!3d23.74!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zMjPCsDQ0JzI0LjAiTiA5MMKwMjInNDguMCJF!5e0!3m2!1sen!2sbd!4v1" width="100%" height="100%" style="border:0; filter: grayscale(0.3);" allowfullscreen="" loading="lazy"></iframe>
        </div>
      </div>
    </div>
  </div>
</section>

<?php require_once 'includes/footer.php'; ?>
