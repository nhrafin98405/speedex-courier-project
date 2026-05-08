<?php $pageTitle='Live Tracking'; require_once __DIR__.'/includes/header.php'; require_once __DIR__.'/includes/navbar.php'; ?>
<div class="bg-orbs"><span></span><span></span></div>
<section class="section-padding" style="padding-top:120px;position:relative;z-index:1;">
  <div class="container">
    <div class="text-center mb-5">
      <span class="step-pill"><span class="live-dot"></span> Live Tracking</span>
      <h1 class="section-title mt-3">Track Your <span class="gradient-text">Parcel</span></h1>
      <p class="section-subtitle">Realtime updates — auto-refreshing every 5 seconds</p>
    </div>
    <div class="row justify-content-center">
      <div class="col-lg-8">
        <form id="liveTrackForm">
          <div class="tracking-input-wrapper">
            <i class="fas fa-search" style="color:var(--text-muted);padding-left:16px;"></i>
            <input type="text" id="trackingInput" placeholder="Enter Tracking ID e.g. SPX12345678901" required>
            <button type="submit" class="btn-speedex"><i class="fas fa-search"></i> Track</button>
          </div>
        </form>
        <div id="trackingResults" class="mt-5" style="display:none;"></div>
      </div>
    </div>
  </div>
</section>
<?php require_once __DIR__.'/includes/footer.php'; ?>
