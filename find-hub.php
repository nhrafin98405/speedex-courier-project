<?php $pageTitle = 'Find Hub'; require_once 'includes/header.php'; require_once 'includes/navbar.php'; ?>

<section class="section-padding" style="padding-top: 120px;">
  <div class="container">
    <div class="text-center mb-5 reveal">
      <h1 class="section-title">Find a <span class="gradient-text">Hub</span></h1>
      <p class="section-subtitle">Locate nhr hubs across Bangladesh</p>
    </div>

    <div class="row mb-4 justify-content-center">
      <div class="col-lg-6">
        <div class="tracking-input-wrapper reveal">
          <i class="fas fa-search" style="color: var(--text-muted); padding-left: 16px;"></i>
          <input type="text" id="hubSearch" placeholder="Search by district or area..." onkeyup="filterHubs()">
        </div>
      </div>
    </div>

    <div class="row g-4" id="hubGrid">
      <?php
      $hubs = [
        ['name' => 'Dhaka Hub', 'district' => 'Dhaka', 'area' => 'Dhanmondi', 'phone' => '01711111111', 'address' => 'House 12, Road 5, Dhanmondi, Dhaka-1205'],
        ['name' => 'Mymensingh Hub', 'district' => 'Mymensingh', 'area' => 'Sadar', 'phone' => '01722222222', 'address' => 'Road 3, Mymensingh Sadar, Mymensingh-2200'],
        ['name' => 'Chittagong Hub', 'district' => 'Chittagong', 'area' => 'Agrabad', 'phone' => '01733333333', 'address' => 'CDA Avenue, Agrabad, Chittagong-4100'],
        ['name' => 'Sylhet Hub', 'district' => 'Sylhet', 'area' => 'Zindabazar', 'phone' => '01744444444', 'address' => 'Zindabazar, Sylhet-3100'],
        ['name' => 'Khulna Hub', 'district' => 'Khulna', 'area' => 'Sadar', 'phone' => '01755555555', 'address' => 'Khan Jahan Ali Road, Khulna-9100'],
        ['name' => 'Barisal Hub', 'district' => 'Barisal', 'area' => 'Sadar', 'phone' => '01766666666', 'address' => 'Sadar Road, Barisal-8200'],
        ['name' => 'Rajshahi Hub', 'district' => 'Rajshahi', 'area' => 'Shaheb Bazaar', 'phone' => '01777777777', 'address' => 'Shaheb Bazaar, Rajshahi-6100'],
        ['name' => 'Rangpur Hub', 'district' => 'Rangpur', 'area' => 'Sadar', 'phone' => '01788888888', 'address' => 'Station Road, Rangpur-5400'],
      ];
      foreach ($hubs as $hub): ?>
      <div class="col-lg-4 col-md-6 hub-item" data-district="<?= strtolower($hub['district']) ?>" data-area="<?= strtolower($hub['area']) ?>">
        <div class="hub-card reveal">
          <div style="height: 160px; background: var(--gradient-primary); display: flex; align-items: center; justify-content: center;">
            <i class="fas fa-warehouse" style="font-size: 3rem; color: rgba(255,255,255,0.8);"></i>
          </div>
          <div class="hub-card-body">
            <h5 style="font-weight: 600; margin-bottom: 8px;"><?= $hub['name'] ?></h5>
            <p style="color: var(--text-secondary); font-size: 0.85rem;"><i class="fas fa-map-marker-alt me-2 text-primary-green"></i><?= $hub['district'] ?>, <?= $hub['area'] ?></p>
            <p style="color: var(--text-secondary); font-size: 0.85rem;"><i class="fas fa-location-dot me-2 text-primary-green"></i><?= $hub['address'] ?></p>
            <p style="color: var(--text-secondary); font-size: 0.85rem;"><i class="fas fa-phone me-2 text-primary-green"></i><?= $hub['phone'] ?></p>
            <a href="https://maps.google.com/?q=<?= urlencode($hub['address']) ?>" target="_blank" class="btn-speedex-outline w-100 justify-content-center mt-3" style="padding: 8px 16px; font-size: 0.8rem;">
              <i class="fas fa-map"></i> View on Map
            </a>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<script>
function filterHubs() {
  const query = document.getElementById('hubSearch').value.toLowerCase();
  document.querySelectorAll('.hub-item').forEach(item => {
    const d = item.dataset.district + ' ' + item.dataset.area;
    item.style.display = d.includes(query) ? '' : 'none';
  });
}
</script>

<?php require_once 'includes/footer.php'; ?>
