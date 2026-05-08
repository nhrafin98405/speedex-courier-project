<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/config/database.php';
if (empty($_SESSION['user_id'])) {
  $redirect = urlencode($_SERVER['REQUEST_URI'] ?? '/speedex-courier/send-parcel.php');
  header('Location: ' . BASE_URL . '/auth/login.php?redirect=' . $redirect);
  exit;
}
$pageTitle = 'Send Parcel';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/config/mail.php';

$success = $error = null;
try { $db = Database::getConnection(); $hubs = $db->query("SELECT id,name,district FROM hubs WHERE status='active' ORDER BY name")->fetchAll(); } catch(Exception $e){ $hubs=[]; }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (!validateCSRFToken($_POST['csrf_token'] ?? '')) { $error='Invalid security token.'; }
  else {
    try {
      $tid = generateTrackingId();
      $data = [
        $tid,
        sanitize($_POST['sender_name']), sanitize($_POST['sender_phone']), sanitize($_POST['sender_email'] ?? ''), sanitize($_POST['sender_address']), (int)$_POST['sender_hub_id'],
        sanitize($_POST['receiver_name']), sanitize($_POST['receiver_phone']), sanitize($_POST['receiver_email'] ?? ''), sanitize($_POST['receiver_address']), (int)$_POST['receiver_hub_id'],
        sanitize($_POST['parcel_type']), (float)$_POST['weight'], sanitize($_POST['description'] ?? ''),
        sanitize($_POST['delivery_type']), sanitize($_POST['payment_method']),
        (float)$_POST['delivery_charge'], (float)$_POST['total_amount'],
        $_SESSION['user_id'] ?? null,
      ];
      $sql = "INSERT INTO parcels (tracking_id, sender_name, sender_phone, sender_email, sender_address, sender_hub_id,
              receiver_name, receiver_phone, receiver_email, receiver_address, receiver_hub_id,
              parcel_type, weight, description, delivery_type, payment_method, delivery_charge, total_amount, booked_by)
              VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
      $db->prepare($sql)->execute($data);
      $pid = (int)$db->lastInsertId();
      $db->prepare("INSERT INTO parcel_tracking (parcel_id, status, location, hub_id, remarks) VALUES (?,?,?,?,?)")
         ->execute([$pid, 'Parcel Booked', $hubs[array_search((int)$_POST['sender_hub_id'], array_column($hubs,'id'))]['name'] ?? '', (int)$_POST['sender_hub_id'], 'Booking created']);
      // Payment record
      $payId = 'PAY' . strtoupper(substr(md5(uniqid('',true)),0,10));
      $payMethod = $_POST['payment_method']==='cod' ? 'cash' : 'cash';
      $payStatus = $_POST['payment_method']==='sender_pay' ? 'completed' : 'pending';
      $db->prepare("INSERT INTO payments (payment_id, parcel_id, amount, method, status) VALUES (?,?,?,?,?)")
         ->execute([$payId, $pid, (float)$_POST['total_amount'], $payMethod, $payStatus]);
      $parcel = $db->prepare("SELECT * FROM parcels WHERE id=?"); $parcel->execute([$pid]); $parcel = $parcel->fetch();
      Mailer::parcelBooked($parcel);
      pushNotification($_SESSION['user_id'] ?? null, 'parcel', 'New Parcel Booked', "Tracking ID: {$tid}", BASE_URL . '/tracking.php?id=' . $tid);
      logActivity($_SESSION['user_id'] ?? null, 'parcel_booked', "Parcel {$tid} created");
      $success = $tid;
    } catch (Exception $e) { $error = 'Failed to book parcel: ' . $e->getMessage(); }
  }
}
?>
<div class="bg-orbs"><span></span><span></span></div>
<?php require_once __DIR__ . '/includes/navbar.php'; ?>
<section class="section-padding" style="padding-top:120px;position:relative;z-index:1;">
  <div class="container">
    <div class="text-center mb-4">
      <span class="step-pill"><i class="fas fa-paper-plane"></i> Send Parcel</span>
      <h1 class="section-title mt-3">Book a <span class="gradient-text">Delivery</span></h1>
      <p class="section-subtitle">Auto-generated tracking ID and instant email confirmation</p>
    </div>

    <?php if ($success): ?>
      <div class="glass-form text-center" style="max-width:640px;margin:0 auto;">
        <i class="fas fa-circle-check" style="font-size:3.5rem;color:var(--primary);"></i>
        <h3 class="mt-3">Parcel booked successfully!</h3>
        <p style="color:var(--text-secondary);">Your tracking ID is</p>
        <h2 class="text-primary-green" style="letter-spacing:1px;"><?= sanitize($success) ?></h2>
        <a href="tracking.php?id=<?= urlencode($success) ?>" class="btn-speedex mt-3"><i class="fas fa-search"></i> Track Parcel</a>
        <a href="send-parcel.php" class="btn-speedex-outline mt-3">Book Another</a>
      </div>
    <?php else: ?>
    <?php if ($error): ?><div class="glass-card mb-3" style="color:#ef4444;"><i class="fas fa-circle-exclamation me-2"></i><?= $error ?></div><?php endif; ?>
    <form method="POST" class="glass-form" style="max-width:980px;margin:0 auto;">
      <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
      <input type="hidden" id="totalAmountHidden" name="total_amount" value="80">
      <input type="hidden" id="chargeHidden" name="delivery_charge" value="80">

      <h5 class="mb-3" style="color:var(--primary);"><i class="fas fa-user me-2"></i>From (Sender)</h5>
      <div class="row g-3 mb-4">
        <div class="col-md-6"><label class="form-label-speedex">Name</label><input class="form-control-speedex" name="sender_name" required></div>
        <div class="col-md-6"><label class="form-label-speedex">Phone</label><input class="form-control-speedex" name="sender_phone" required></div>
        <div class="col-md-6"><label class="form-label-speedex">Email</label><input class="form-control-speedex" type="email" name="sender_email" placeholder="for booking confirmation"></div>
        <div class="col-md-6"><label class="form-label-speedex">Origin Hub</label>
          <select class="form-control-speedex" name="sender_hub_id" required>
            <option value="">Select hub</option>
            <?php foreach ($hubs as $h): ?><option value="<?= $h['id'] ?>"><?= sanitize($h['name']) ?> (<?= sanitize($h['district']) ?>)</option><?php endforeach; ?>
          </select>
        </div>
        <div class="col-12"><label class="form-label-speedex">Address</label><textarea class="form-control-speedex" rows="2" name="sender_address" required></textarea></div>
      </div>

      <h5 class="mb-3" style="color:var(--primary);"><i class="fas fa-user-check me-2"></i>To (Receiver)</h5>
      <div class="row g-3 mb-4">
        <div class="col-md-6"><label class="form-label-speedex">Name</label><input class="form-control-speedex" name="receiver_name" required></div>
        <div class="col-md-6"><label class="form-label-speedex">Phone</label><input class="form-control-speedex" name="receiver_phone" required></div>
        <div class="col-md-6"><label class="form-label-speedex">Email</label><input class="form-control-speedex" type="email" name="receiver_email" placeholder="for delivery updates"></div>
        <div class="col-md-6"><label class="form-label-speedex">Destination Hub</label>
          <select class="form-control-speedex" name="receiver_hub_id" required>
            <option value="">Select hub</option>
            <?php foreach ($hubs as $h): ?><option value="<?= $h['id'] ?>"><?= sanitize($h['name']) ?> (<?= sanitize($h['district']) ?>)</option><?php endforeach; ?>
          </select>
        </div>
        <div class="col-12"><label class="form-label-speedex">Address</label><textarea class="form-control-speedex" rows="2" name="receiver_address" required></textarea></div>
      </div>

      <h5 class="mb-3" style="color:var(--primary);"><i class="fas fa-box me-2"></i>Parcel Information</h5>
      <div class="row g-3 mb-4">
        <div class="col-md-4"><label class="form-label-speedex">Type</label>
          <select class="form-control-speedex" name="parcel_type">
            <option value="document">Document</option><option value="small_parcel">Small Parcel</option>
            <option value="medium_parcel">Medium Parcel</option><option value="large_parcel">Large Parcel</option>
            <option value="fragile">Fragile</option>
          </select>
        </div>
        <div class="col-md-4"><label class="form-label-speedex">Weight (kg)</label><input id="weightInput" class="form-control-speedex" type="number" step="0.1" min="0" name="weight" value="1"></div>
        <div class="col-md-4"><label class="form-label-speedex">Delivery</label>
          <select id="deliveryType" class="form-control-speedex" name="delivery_type">
            <option value="standard">Standard (24-48h)</option>
            <option value="express">Express (12-24h)</option>
            <option value="same_day">Same Day</option>
          </select>
        </div>
        <div class="col-md-6"><label class="form-label-speedex">Payment Method</label>
          <select class="form-control-speedex" name="payment_method">
            <option value="sender_pay">Sender Pay</option><option value="receiver_pay">Receiver Pay</option><option value="cod">Cash on Delivery</option>
          </select>
        </div>
        <div class="col-md-6"><label class="form-label-speedex">Description</label><input class="form-control-speedex" name="description" placeholder="Optional"></div>
      </div>

      <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div class="cost-box"><div style="font-size:.75rem;color:var(--text-muted);">Estimated Cost</div><div class="v" id="estCost">৳ 80.00</div></div>
        <button type="submit" class="btn-speedex"><i class="fas fa-paper-plane"></i> Book Parcel & Send Email</button>
      </div>
    </form>
    <?php endif; ?>
  </div>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
