<?php
// Footer for both public + dashboard pages.
?>
<?php if (!isset($hideFooter) || !$hideFooter): ?>
<footer class="footer">
  <div class="container">
    <div class="row g-4">
      <div class="col-lg-4 col-md-6">
        <div class="d-flex align-items-center gap-2 mb-3">
          <span class="brand-logo-mark"><i class="fas fa-shipping-fast"></i></span>
          <div class="brand-logo-text"><span class="brand-name" style="font-size:1.15rem;">NHR</span><span class="brand-tag">Courier Service</span></div>
        </div>
        <p style="color:var(--text-secondary);font-size:.875rem;line-height:1.8;">Fast, Safe and Reliable delivery across Bangladesh, with realtime tracking and email updates.</p>
      </div>
      <div class="col-lg-2 col-md-6"><h5>Links</h5><ul class="footer-links">
        <li><a href="<?= BASE_URL ?>/">Home</a></li>
        <li><a href="<?= BASE_URL ?>/tracking.php">Track</a></li>
        <li><a href="<?= BASE_URL ?>/send-parcel.php">Send</a></li>
        <li><a href="<?= BASE_URL ?>/services.php">Services</a></li>
      </ul></div>
      <div class="col-lg-3 col-md-6"><h5>Services</h5><ul class="footer-links">
        <li><a href="#">Same Day Delivery</a></li><li><a href="#">Express Delivery</a></li>
        <li><a href="#">Cash On Delivery</a></li><li><a href="#">Corporate</a></li>
      </ul></div>
      <div class="col-lg-3 col-md-6"><h5>Contact</h5><ul class="footer-links">
        <li><i class="fas fa-map-marker-alt me-2" style="color:var(--primary);"></i> Dhaka, Bangladesh</li>
        <li><i class="fas fa-phone me-2" style="color:var(--primary);"></i> +880 1700-000000</li>
        <li><i class="fas fa-envelope me-2" style="color:var(--primary);"></i> info@speedex.com</li>
      </ul></div>
    </div>
    <div class="footer-bottom"><p>&copy; <?= date('Y') ?> nhr Courier Service. All rights reserved.</p></div>
  </div>
</footer>
<?php endif; ?>
<div id="toastContainer" class="toast-container"></div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
<script>window.SPEEDEX_BASE = "<?= BASE_URL ?>";</script>
<script src="<?= BASE_URL ?>/assets/js/main.js"></script>
<script src="<?= BASE_URL ?>/assets/js/realtime.js"></script>
</body>
</html>
